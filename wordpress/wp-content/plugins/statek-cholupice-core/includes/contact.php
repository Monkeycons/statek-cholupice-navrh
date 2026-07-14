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

function statek_cholupice_core_handle_contact( WP_REST_Request $request ): WP_REST_Response {
	$nonce = (string) $request->get_param( 'nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'message' => 'Neplatné ověření formuláře. Obnovte stránku a zkuste to znovu.' ), 403 );
	}

	$honeypot = trim( (string) $request->get_param( 'company' ) );
	if ( '' !== $honeypot ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz byl přijat.' ), 200 );
	}

	$started_at = (int) $request->get_param( 'form_started_at' );
	$elapsed_ms = (int) floor( microtime( true ) * 1000 ) - $started_at;
	if ( $started_at <= 0 || $elapsed_ms < STATEK_CHOLUPICE_CORE_MIN_FORM_SECONDS * 1000 ) {
		return new WP_REST_Response( array( 'message' => 'Formulář byl odeslán příliš rychle. Zkuste to prosím znovu.' ), 429 );
	}

	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
	$limited = get_transient( 'statek_contact_' . md5( $ip ) );
	if ( $limited ) {
		return new WP_REST_Response( array( 'message' => 'Zkuste to prosím znovu za chvíli.' ), 429 );
	}

	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

	if ( ! is_email( $email ) || '' === $message ) {
		return new WP_REST_Response( array( 'message' => 'Vyplňte prosím platný e-mail a dotaz.' ), 400 );
	}

	$extra_antispam = apply_filters( 'statek_cholupice_core_contact_extra_antispam', true, $request );
	if ( is_wp_error( $extra_antispam ) ) {
		return new WP_REST_Response(
			array( 'message' => $extra_antispam->get_error_message() ),
			400
		);
	}
	if ( true !== $extra_antispam ) {
		return new WP_REST_Response( array( 'message' => 'Odeslání se nepodařilo ověřit. Zkuste to prosím znovu.' ), 400 );
	}

	set_transient( 'statek_contact_' . md5( $ip ), 1, MINUTE_IN_SECONDS );

	$subject = 'Dotaz z webu Statek Cholupice';
	$body    = "Jméno: {$name}
E-mail: {$email}

Dotaz:
{$message}";
	$headers = array( 'Reply-To: ' . $email );

	$sent = wp_mail( statek_cholupice_core_contact_email(), $subject, $body, $headers );
	if ( ! $sent ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz se nepodařilo odeslat. Napište prosím přímo na ' . statek_cholupice_core_contact_email() . '.' ), 500 );
	}

	return new WP_REST_Response( array( 'message' => 'Děkujeme. Váš dotaz jsme přijali.' ), 200 );
}
