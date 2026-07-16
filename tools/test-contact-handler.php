<?php
/**
 * Izolované testy kontaktního handleru bez načtení WordPressu a bez odesílání e-mailů.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );

final class WP_REST_Server {
	public const CREATABLE = 'POST';
}

final class WP_REST_Request {
	public function __construct( private array $params ) {}

	public function get_param( string $key ) {
		return $this->params[ $key ] ?? null;
	}
}

final class WP_REST_Response {
	private array $headers = array();

	public function __construct( private array $data, private int $status ) {}

	public function get_data(): array {
		return $this->data;
	}

	public function get_status(): int {
		return $this->status;
	}

	public function header( string $name, string $value ): void {
		$this->headers[ $name ] = $value;
	}

	public function get_headers(): array {
		return $this->headers;
	}
}

final class WP_Error {
	public function __construct( private string $message ) {}

	public function get_error_message(): string {
		return $this->message;
	}
}

$GLOBALS['test_transients'] = array();
$GLOBALS['test_mail_calls'] = array();
$GLOBALS['test_mail_result'] = true;
$GLOBALS['test_route'] = null;

function add_action(): void {}
function register_setting(): void {}
function add_options_page(): void {}
function current_user_can(): bool { return true; }
function settings_fields(): void {}
function submit_button(): void {}
function esc_attr( string $value ): string { return $value; }
function esc_url_raw( string $value ): string { return $value; }
function esc_url( string $value ): string { return $value; }
function get_option( string $key, string $default ): string { return $default; }
function wp_check_invalid_utf8( string $value ): string { return $value; }
function wp_salt(): string { return 'test-only-contact-salt'; }
function wp_verify_nonce( string $nonce, string $action ): bool { return 'valid-nonce' === $nonce && 'wp_rest' === $action; }
function sanitize_email( string $value ): string { return (string) filter_var( trim( $value ), FILTER_SANITIZE_EMAIL ); }
function is_email( string $value ) { return filter_var( $value, FILTER_VALIDATE_EMAIL ) ? $value : false; }
function sanitize_text_field( string $value ): string { return trim( strip_tags( preg_replace( '/\s+/', ' ', $value ) ?? '' ) ); }
function sanitize_textarea_field( string $value ): string { return trim( strip_tags( str_replace( "\r", '', $value ) ) ); }
function apply_filters( string $hook, $value ) { return $value; }
function is_wp_error( $value ): bool { return $value instanceof WP_Error; }

function register_rest_route( string $namespace, string $route, array $args ): void {
	$GLOBALS['test_route'] = compact( 'namespace', 'route', 'args' );
}

function get_transient( string $key ) {
	return $GLOBALS['test_transients'][ $key ]['value'] ?? false;
}

function set_transient( string $key, $value, int $ttl ): bool {
	$GLOBALS['test_transients'][ $key ] = compact( 'value', 'ttl' );
	return true;
}

function delete_transient( string $key ): bool {
	unset( $GLOBALS['test_transients'][ $key ] );
	return true;
}

function wp_mail( string $to, string $subject, string $body, array $headers ): bool {
	$GLOBALS['test_mail_calls'][] = compact( 'to', 'subject', 'body', 'headers' );
	return $GLOBALS['test_mail_result'];
}

require dirname( __DIR__ ) . '/wordpress/wp-content/plugins/statek-cholupice-core/includes/contact.php';

function test_reset(): void {
	$GLOBALS['test_transients'] = array();
	$GLOBALS['test_mail_calls'] = array();
	$GLOBALS['test_mail_result'] = true;
	$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
}

function test_params(): array {
	return array(
		'name'            => 'Jan Novák',
		'email'           => 'jan@example.com',
		'message'         => 'Prosím o další informace k projektu.',
		'nonce'           => 'valid-nonce',
		'company'         => '',
		'form_started_at' => (string) ( (int) floor( microtime( true ) * 1000 ) - 5000 ),
	);
}

function test_request( array $changes = array(), array $remove = array() ): WP_REST_Response {
	$params = array_replace( test_params(), $changes );
	foreach ( $remove as $key ) {
		unset( $params[ $key ] );
	}
	return statek_cholupice_core_handle_contact( new WP_REST_Request( $params ) );
}

function test_same( $expected, $actual, string $message ): void {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . '; expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
	}
}

function test_true( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$tests = array();

$tests['route accepts only POST and has explicit permission callback'] = function (): void {
	statek_cholupice_core_register_contact_route();
	$route = $GLOBALS['test_route'];
	test_same( 'POST', $route['args']['methods'], 'REST route method' );
	test_same( '__return_true', $route['args']['permission_callback'], 'Permission callback' );
	test_same( 'statek_cholupice_core_handle_contact', $route['args']['callback'], 'REST callback' );
};

$tests['valid message sends once with safe headers'] = function (): void {
	$response = test_request();
	test_same( 200, $response->get_status(), 'Valid response status' );
	test_same( 1, count( $GLOBALS['test_mail_calls'] ), 'Mail call count' );
	$headers = $GLOBALS['test_mail_calls'][0]['headers'];
	test_true( in_array( 'From: Statek Cholupice <noreply@statekcholupice.cz>', $headers, true ), 'Missing domain From header' );
	test_true( in_array( 'Reply-To: jan@example.com', $headers, true ), 'Missing validated Reply-To header' );
};

$tests['filled honeypot is silently accepted without mail'] = function (): void {
	$response = test_request( array( 'company' => 'Robot s.r.o.' ) );
	test_same( 200, $response->get_status(), 'Honeypot response status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Honeypot must not send mail' );
};

$tests['missing honeypot field remains compatible'] = function (): void {
	$response = test_request( array(), array( 'company' ) );
	test_same( 200, $response->get_status(), 'Missing honeypot status' );
	test_same( 1, count( $GLOBALS['test_mail_calls'] ), 'Missing empty honeypot should not block a valid request' );
};

$tests['submission faster than three seconds is rejected'] = function (): void {
	$response = test_request( array( 'form_started_at' => (string) ( (int) floor( microtime( true ) * 1000 ) - 1000 ) ) );
	test_same( 400, $response->get_status(), 'Fast submission status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Fast submission must not send mail' );
};

$tests['missing and invalid timestamps are rejected'] = function (): void {
	test_same( 400, test_request( array(), array( 'form_started_at' ) )->get_status(), 'Missing timestamp status' );
	test_reset();
	test_same( 400, test_request( array( 'form_started_at' => 'not-a-number' ) )->get_status(), 'Invalid timestamp status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Invalid timestamps must not send mail' );
};

$tests['future timestamp is rejected'] = function (): void {
	$response = test_request( array( 'form_started_at' => (string) ( (int) floor( microtime( true ) * 1000 ) + 5000 ) ) );
	test_same( 400, $response->get_status(), 'Future timestamp status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Future timestamp must not send mail' );
};

$tests['invalid email is rejected'] = function (): void {
	test_same( 400, test_request( array( 'email' => 'invalid' ) )->get_status(), 'Invalid email status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Invalid email must not send mail' );
};

$tests['CRLF email header injection is rejected'] = function (): void {
	test_same( 400, test_request( array( 'email' => "victim@example.com\r\nBcc: attacker@example.com" ) )->get_status(), 'CRLF status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'CRLF attempt must not send mail' );
};

$tests['empty short and long messages are rejected'] = function (): void {
	test_same( 400, test_request( array( 'message' => '' ) )->get_status(), 'Empty message status' );
	test_reset();
	test_same( 400, test_request( array( 'message' => 'Krátké' ) )->get_status(), 'Short message status' );
	test_reset();
	test_same( 400, test_request( array( 'message' => str_repeat( 'x', 5001 ) ) )->get_status(), 'Long message status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Invalid message lengths must not send mail' );
};

$tests['name length is limited'] = function (): void {
	test_same( 400, test_request( array( 'name' => str_repeat( 'x', 121 ) ) )->get_status(), 'Long name status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Long name must not send mail' );
};

$tests['short rate limit blocks fourth attempt'] = function (): void {
	for ( $i = 1; $i <= 3; $i++ ) {
		$response = test_request( array( 'email' => "user{$i}@example.com", 'message' => "Jedinečný platný dotaz číslo {$i}." ) );
		test_same( 200, $response->get_status(), "Allowed attempt {$i}" );
	}
	$response = test_request( array( 'email' => 'user4@example.com', 'message' => 'Čtvrtý jedinečný platný dotaz.' ) );
	test_same( 429, $response->get_status(), 'Rate limit status' );
	test_same( '600', $response->get_headers()['Retry-After'] ?? '', 'Retry-After header' );
	test_same( 3, count( $GLOBALS['test_mail_calls'] ), 'Rate-limited request must not send mail' );
};

$tests['long rate limit blocks eleventh attempt'] = function (): void {
	$source_key = statek_cholupice_core_contact_source_key();
	for ( $i = 1; $i <= 10; $i++ ) {
		unset( $GLOBALS['test_transients'][ 'statek_contact_rl_10_' . $source_key ] );
		$response = test_request( array( 'email' => "hour{$i}@example.com", 'message' => "Hodinový jedinečný dotaz číslo {$i}." ) );
		test_same( 200, $response->get_status(), "Allowed hourly attempt {$i}" );
	}
	unset( $GLOBALS['test_transients'][ 'statek_contact_rl_10_' . $source_key ] );
	$response = test_request( array( 'email' => 'hour11@example.com', 'message' => 'Jedenáctý hodinový dotaz.' ) );
	test_same( 429, $response->get_status(), 'Hourly rate limit status' );
	test_same( 10, count( $GLOBALS['test_mail_calls'] ), 'Hourly limited request must not send mail' );
};

$tests['duplicate message sends only once'] = function (): void {
	test_same( 200, test_request()->get_status(), 'First duplicate status' );
	test_same( 200, test_request()->get_status(), 'Second duplicate status' );
	test_same( 1, count( $GLOBALS['test_mail_calls'] ), 'Duplicate must not send another mail' );
};

$tests['missing and invalid nonce are rejected'] = function (): void {
	test_same( 403, test_request( array(), array( 'nonce' ) )->get_status(), 'Missing nonce status' );
	test_reset();
	test_same( 403, test_request( array( 'nonce' => 'invalid' ) )->get_status(), 'Invalid nonce status' );
	test_same( 0, count( $GLOBALS['test_mail_calls'] ), 'Invalid nonce must not send mail' );
};

$tests['wp_mail failure is reported and duplicate reservation is released'] = function (): void {
	$GLOBALS['test_mail_result'] = false;
	test_same( 500, test_request()->get_status(), 'Mail failure status' );
	$GLOBALS['test_mail_result'] = true;
	test_same( 200, test_request()->get_status(), 'Retry after mail failure status' );
	test_same( 2, count( $GLOBALS['test_mail_calls'] ), 'Mail failure retry call count' );
};

$failures = 0;
foreach ( $tests as $name => $test ) {
	test_reset();
	try {
		$test();
		echo "PASS: {$name}\n";
	} catch ( Throwable $error ) {
		$failures++;
		echo "FAIL: {$name}: {$error->getMessage()}\n";
	}
}

echo sprintf( "\n%d tests, %d failures, %d mock mail calls in final isolated case.\n", count( $tests ), $failures, count( $GLOBALS['test_mail_calls'] ) );
exit( $failures > 0 ? 1 : 0 );
