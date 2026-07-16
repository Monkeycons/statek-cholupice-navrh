<?php
/**
 * Bezpečný kontaktní formulář.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const STATEK_CHOLUPICE_CORE_MIN_FORM_SECONDS = 3;
const STATEK_CHOLUPICE_CORE_NAME_MAX_LENGTH = 120;
const STATEK_CHOLUPICE_CORE_EMAIL_MAX_LENGTH = 254;
const STATEK_CHOLUPICE_CORE_MESSAGE_MIN_LENGTH = 10;
const STATEK_CHOLUPICE_CORE_MESSAGE_MAX_LENGTH = 5000;
const STATEK_CHOLUPICE_CORE_COMPANY_MAX_LENGTH = 120;
const STATEK_CHOLUPICE_CORE_RATE_SHORT_LIMIT = 3;
const STATEK_CHOLUPICE_CORE_RATE_SHORT_TTL = 10 * MINUTE_IN_SECONDS;
const STATEK_CHOLUPICE_CORE_RATE_LONG_LIMIT = 10;
const STATEK_CHOLUPICE_CORE_RATE_LONG_TTL = HOUR_IN_SECONDS;
const STATEK_CHOLUPICE_CORE_DUPLICATE_TTL = 10 * MINUTE_IN_SECONDS;

function statek_cholupice_core_contact_email(): string {
	$email = get_option( 'statek_cholupice_contact_email', 'info@statekcholupice.cz' );
	return is_email( $email ) ? $email : 'info@statekcholupice.cz';
}

function statek_cholupice_core_privacy_url(): string {
	$url = get_option( 'statek_cholupice_privacy_url', 'https://www.statekcholupice.cz/ochrana-osobnich-udaju/' );
	return esc_url_raw( $url ?: 'https://www.statekcholupice.cz/ochrana-osobnich-udaju/' );
}

function statek_cholupice_core_register_settings(): void {
	register_setting(
		'statek_cholupice_settings',
		'statek_cholupice_contact_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => 'info@statekcholupice.cz',
		)
	);
	register_setting(
		'statek_cholupice_settings',
		'statek_cholupice_privacy_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => 'https://www.statekcholupice.cz/ochrana-osobnich-udaju/',
		)
	);
}
add_action( 'admin_init', 'statek_cholupice_core_register_settings' );

function statek_cholupice_core_settings_page(): void {
	add_options_page(
		'Statek Cholupice',
		'Statek Cholupice',
		'manage_options',
		'statek-cholupice',
		'statek_cholupice_core_render_settings_page'
	);
}
add_action( 'admin_menu', 'statek_cholupice_core_settings_page' );

function statek_cholupice_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Statek Cholupice</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'statek_cholupice_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="statek_cholupice_contact_email">E-mail pro dotazy</label></th>
					<td><input class="regular-text" id="statek_cholupice_contact_email" name="statek_cholupice_contact_email" type="email" value="<?php echo esc_attr( statek_cholupice_core_contact_email() ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="statek_cholupice_privacy_url">URL zásad zpracování osobních údajů</label></th>
					<td><input class="regular-text" id="statek_cholupice_privacy_url" name="statek_cholupice_privacy_url" type="url" value="<?php echo esc_url( statek_cholupice_core_privacy_url() ); ?>"></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function statek_cholupice_core_register_contact_route(): void {
	register_rest_route(
		'statek-cholupice/v1',
		'/contact',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'statek_cholupice_core_handle_contact',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'statek_cholupice_core_register_contact_route' );

function statek_cholupice_core_contact_param( WP_REST_Request $request, string $key ): string {
	$value = $request->get_param( $key );
	return is_scalar( $value ) ? (string) $value : '';
}

function statek_cholupice_core_contact_length( string $value ): int {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

function statek_cholupice_core_clean_contact_text( string $value, bool $multiline = false ): string {
	$value = wp_check_invalid_utf8( $value );
	$value = str_replace( "\0", '', $value );
	if ( $multiline ) {
		$value = preg_replace( '/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value ) ?? '';
		return sanitize_textarea_field( $value );
	}

	$value = preg_replace( '/[\x00-\x1F\x7F]/', ' ', $value ) ?? '';
	return sanitize_text_field( $value );
}

function statek_cholupice_core_contact_source_key(): string {
	$ip     = trim( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$packed = filter_var( $ip, FILTER_VALIDATE_IP ) ? @inet_pton( $ip ) : false;
	$source = false !== $packed ? bin2hex( $packed ) : 'unknown';
	return substr( hash_hmac( 'sha256', $source, wp_salt( 'nonce' ) ), 0, 32 );
}

function statek_cholupice_core_contact_consume_limit( string $key, int $limit, int $ttl ): bool {
	$count = (int) get_transient( $key );
	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, $ttl );
	return true;
}

function statek_cholupice_core_contact_rate_limited( string $source_key ): bool {
	$short_allowed = statek_cholupice_core_contact_consume_limit(
		'statek_contact_rl_10_' . $source_key,
		STATEK_CHOLUPICE_CORE_RATE_SHORT_LIMIT,
		STATEK_CHOLUPICE_CORE_RATE_SHORT_TTL
	);
	$long_allowed  = statek_cholupice_core_contact_consume_limit(
		'statek_contact_rl_60_' . $source_key,
		STATEK_CHOLUPICE_CORE_RATE_LONG_LIMIT,
		STATEK_CHOLUPICE_CORE_RATE_LONG_TTL
	);

	return ! $short_allowed || ! $long_allowed;
}

function statek_cholupice_core_contact_limit_response(): WP_REST_Response {
	$response = new WP_REST_Response(
		array( 'message' => 'Odesíláte příliš mnoho požadavků. Zkuste to prosím znovu později.' ),
		429
	);
	$response->header( 'Retry-After', (string) STATEK_CHOLUPICE_CORE_RATE_SHORT_TTL );
	return $response;
}

function statek_cholupice_core_contact_duplicate_key( string $email, string $message ): string {
	$normalized_message = preg_replace( '/\s+/u', ' ', trim( $message ) );
	if ( null === $normalized_message ) {
		$normalized_message = trim( $message );
	}
	$fingerprint = strtolower( $email ) . "\n" . $normalized_message;
	return 'statek_contact_dup_' . substr( hash_hmac( 'sha256', $fingerprint, wp_salt( 'nonce' ) ), 0, 32 );
}

function statek_cholupice_core_handle_contact( WP_REST_Request $request ): WP_REST_Response {
	$source_key = statek_cholupice_core_contact_source_key();
	if ( statek_cholupice_core_contact_rate_limited( $source_key ) ) {
		return statek_cholupice_core_contact_limit_response();
	}

	$honeypot = trim( statek_cholupice_core_contact_param( $request, 'company' ) );
	if ( '' !== $honeypot || statek_cholupice_core_contact_length( $honeypot ) > STATEK_CHOLUPICE_CORE_COMPANY_MAX_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz byl přijat.' ), 200 );
	}

	$started_raw = statek_cholupice_core_contact_param( $request, 'form_started_at' );
	$now_ms      = (int) floor( microtime( true ) * 1000 );
	if ( ! preg_match( '/^\d{1,16}$/', $started_raw ) ) {
		return new WP_REST_Response( array( 'message' => 'Odeslání se nepodařilo ověřit. Obnovte stránku a zkuste to znovu.' ), 400 );
	}
	$started_at = (int) $started_raw;
	$elapsed_ms = $now_ms - $started_at;
	if ( $started_at <= 0 || $started_at > $now_ms || $elapsed_ms < STATEK_CHOLUPICE_CORE_MIN_FORM_SECONDS * 1000 ) {
		return new WP_REST_Response( array( 'message' => 'Odeslání se nepodařilo ověřit. Obnovte stránku a zkuste to znovu.' ), 400 );
	}

	$nonce = statek_cholupice_core_contact_param( $request, 'nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'message' => 'Neplatné ověření formuláře. Obnovte stránku a zkuste to znovu.' ), 403 );
	}

	$raw_email = trim( wp_check_invalid_utf8( statek_cholupice_core_contact_param( $request, 'email' ) ) );
	if ( preg_match( '/[\x00-\x1F\x7F]/', $raw_email ) || statek_cholupice_core_contact_length( $raw_email ) > STATEK_CHOLUPICE_CORE_EMAIL_MAX_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Zadejte prosím platnou e-mailovou adresu.' ), 400 );
	}

	$name    = statek_cholupice_core_clean_contact_text( statek_cholupice_core_contact_param( $request, 'name' ) );
	$email   = sanitize_email( $raw_email );
	$message = statek_cholupice_core_clean_contact_text( statek_cholupice_core_contact_param( $request, 'message' ), true );

	if ( statek_cholupice_core_contact_length( $name ) > STATEK_CHOLUPICE_CORE_NAME_MAX_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Jméno je příliš dlouhé.' ), 400 );
	}
	if ( ! is_email( $email ) || statek_cholupice_core_contact_length( $email ) > STATEK_CHOLUPICE_CORE_EMAIL_MAX_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Zadejte prosím platnou e-mailovou adresu.' ), 400 );
	}
	$message_length = statek_cholupice_core_contact_length( $message );
	if ( $message_length < STATEK_CHOLUPICE_CORE_MESSAGE_MIN_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz musí mít alespoň 10 znaků.' ), 400 );
	}
	if ( $message_length > STATEK_CHOLUPICE_CORE_MESSAGE_MAX_LENGTH ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz může mít nejvýše 5 000 znaků.' ), 400 );
	}

	$extra_antispam = apply_filters( 'statek_cholupice_core_contact_extra_antispam', true, $request );
	if ( is_wp_error( $extra_antispam ) ) {
		return new WP_REST_Response( array( 'message' => 'Odeslání se nepodařilo ověřit. Zkuste to prosím znovu.' ), 400 );
	}
	if ( true !== $extra_antispam ) {
		return new WP_REST_Response( array( 'message' => 'Odeslání se nepodařilo ověřit. Zkuste to prosím znovu.' ), 400 );
	}

	$duplicate_key = statek_cholupice_core_contact_duplicate_key( $email, $message );
	if ( get_transient( $duplicate_key ) ) {
		return new WP_REST_Response( array( 'message' => 'Děkujeme. Váš dotaz jsme přijali.' ), 200 );
	}
	set_transient( $duplicate_key, 1, STATEK_CHOLUPICE_CORE_DUPLICATE_TTL );

	$subject = 'Dotaz z webu Statek Cholupice';
	$body    = "Jméno: {$name}
E-mail: {$email}

Dotaz:
{$message}";
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: Statek Cholupice <noreply@statekcholupice.cz>',
		'Reply-To: ' . $email,
	);

	$sent = wp_mail( statek_cholupice_core_contact_email(), $subject, $body, $headers );
	if ( ! $sent ) {
		delete_transient( $duplicate_key );
		return new WP_REST_Response( array( 'message' => 'Dotaz se nepodařilo odeslat. Napište prosím přímo na ' . statek_cholupice_core_contact_email() . '.' ), 500 );
	}

	return new WP_REST_Response( array( 'message' => 'Děkujeme. Váš dotaz jsme přijali.' ), 200 );
}
