<?php
/**
 * Privacy-first Google Analytics 4 integration and campaign redirect.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const STATEK_CHOLUPICE_GA4_OPTION          = 'statek_cholupice_ga4_measurement_id';
const STATEK_CHOLUPICE_GA4_DEFAULT_ID      = 'G-6WYM4Z2VWZ';
const STATEK_CHOLUPICE_CONSENT_VERSION     = 1;
const STATEK_CHOLUPICE_CONSENT_COOKIE_NAME = 'statek_cookie_consent';

/**
 * Return the configured measurement ID, or an empty string when disabled/invalid.
 */
function statek_cholupice_ga4_measurement_id(): string {
	$value = get_option( STATEK_CHOLUPICE_GA4_OPTION, STATEK_CHOLUPICE_GA4_DEFAULT_ID );
	$value = is_string( $value ) ? strtoupper( trim( $value ) ) : '';

	return preg_match( '/^G-[A-Z0-9]+$/', $value ) ? $value : '';
}

/**
 * Sanitize the measurement ID saved from Settings > General.
 */
function statek_cholupice_sanitize_ga4_measurement_id( $value ): string {
	$value = is_string( $value ) ? strtoupper( trim( wp_unslash( $value ) ) ) : '';

	if ( '' === $value ) {
		return '';
	}

	if ( ! preg_match( '/^G-[A-Z0-9]+$/', $value ) ) {
		add_settings_error(
			STATEK_CHOLUPICE_GA4_OPTION,
			'statek_cholupice_ga4_invalid',
			__( 'Měřicí ID musí mít formát G-XXXXXXXX. Analytika byla vypnuta.', 'statek-cholupice' ),
			'error'
		);
		return '';
	}

	return $value;
}

/**
 * Register the GA4 field on Settings > General.
 */
function statek_cholupice_register_analytics_setting(): void {
	register_setting(
		'general',
		STATEK_CHOLUPICE_GA4_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'statek_cholupice_sanitize_ga4_measurement_id',
			'default'           => STATEK_CHOLUPICE_GA4_DEFAULT_ID,
		)
	);

	add_settings_field(
		STATEK_CHOLUPICE_GA4_OPTION,
		__( 'Google Analytics 4 – měřicí ID', 'statek-cholupice' ),
		'statek_cholupice_render_analytics_setting',
		'general'
	);
}
add_action( 'admin_init', 'statek_cholupice_register_analytics_setting' );

/**
 * Render the GA4 setting field.
 */
function statek_cholupice_render_analytics_setting(): void {
	$value = get_option( STATEK_CHOLUPICE_GA4_OPTION, STATEK_CHOLUPICE_GA4_DEFAULT_ID );
	$value = is_string( $value ) ? $value : '';
	?>
	<input
		class="regular-text code"
		id="<?php echo esc_attr( STATEK_CHOLUPICE_GA4_OPTION ); ?>"
		name="<?php echo esc_attr( STATEK_CHOLUPICE_GA4_OPTION ); ?>"
		pattern="G-[A-Z0-9]+"
		placeholder="G-XXXXXXXX"
		type="text"
		value="<?php echo esc_attr( $value ); ?>"
	>
	<p class="description">
		<?php esc_html_e( 'Prázdná nebo neplatná hodnota analytiku zcela vypne. Měření se spustí jen na produkční doméně a až po souhlasu návštěvníka.', 'statek-cholupice' ); ?>
	</p>
	<?php
}

/**
 * Return the normalized hostname for the current HTTP request.
 */
function statek_cholupice_request_hostname(): string {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$host = strtolower( preg_replace( '/:\d+$/', '', $host ) ?? '' );

	return rtrim( $host, '.' );
}

/**
 * Return the only hostnames where production analytics may run.
 *
 * @return string[]
 */
function statek_cholupice_analytics_production_domains(): array {
	return array( 'statekcholupice.cz', 'www.statekcholupice.cz' );
}

/**
 * Determine whether analytics assets and consent controls may run for this request.
 */
function statek_cholupice_analytics_is_allowed(): bool {
	if ( '' === statek_cholupice_ga4_measurement_id() ) {
		return false;
	}

	if ( is_admin() || is_user_logged_in() ) {
		return false;
	}

	if (
		( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
		( defined( 'DOING_CRON' ) && DOING_CRON ) ||
		( defined( 'WP_CLI' ) && WP_CLI ) ||
		( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() )
	) {
		return false;
	}

	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return false;
	}

	return in_array( statek_cholupice_request_hostname(), statek_cholupice_analytics_production_domains(), true );
}

/**
 * Enqueue the local consent and analytics controller only on eligible requests.
 */
function statek_cholupice_enqueue_analytics(): void {
	if ( ! statek_cholupice_analytics_is_allowed() ) {
		return;
	}

	wp_enqueue_script(
		'statek-cholupice-analytics',
		statek_cholupice_asset_url( 'js/analytics.js' ),
		array(),
		statek_cholupice_asset_version( 'js/analytics.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'statek_cholupice_enqueue_analytics', 5 );

/**
 * Render the non-blocking consent banner before footer scripts.
 */
function statek_cholupice_render_cookie_banner(): void {
	if ( ! statek_cholupice_analytics_is_allowed() ) {
		return;
	}
	?>
	<aside
		class="cookie-banner"
		data-cookie-banner
		data-measurement-id="<?php echo esc_attr( statek_cholupice_ga4_measurement_id() ); ?>"
		data-production-domains="<?php echo esc_attr( implode( ',', statek_cholupice_analytics_production_domains() ) ); ?>"
		data-consent-version="<?php echo esc_attr( (string) STATEK_CHOLUPICE_CONSENT_VERSION ); ?>"
		data-consent-cookie="<?php echo esc_attr( STATEK_CHOLUPICE_CONSENT_COOKIE_NAME ); ?>"
		data-privacy-url="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>"
		aria-labelledby="cookie-banner-title"
		aria-describedby="cookie-banner-description"
		role="region"
		hidden
	>
		<div class="cookie-banner__inner">
			<div class="cookie-banner__copy">
				<h2 id="cookie-banner-title"><?php esc_html_e( 'Nastavení cookies', 'statek-cholupice' ); ?></h2>
				<p id="cookie-banner-description"><?php esc_html_e( 'Používáme nezbytné cookies pro správné fungování webu. S vaším souhlasem také Google Analytics, abychom věděli, které informace jsou pro návštěvníky užitečné. Svou volbu můžete kdykoli změnit.', 'statek-cholupice' ); ?></p>
			</div>
			<div class="cookie-banner__actions">
				<button class="cookie-banner__button" type="button" data-cookie-allow><?php esc_html_e( 'Povolit analytiku', 'statek-cholupice' ); ?></button>
				<button class="cookie-banner__button" type="button" data-cookie-deny><?php esc_html_e( 'Odmítnout', 'statek-cholupice' ); ?></button>
				<a class="cookie-banner__more" href="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>"><?php esc_html_e( 'Více informací', 'statek-cholupice' ); ?></a>
			</div>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', 'statek_cholupice_render_cookie_banner', 5 );

/**
 * Render a footer control that reopens cookie preferences without navigating.
 */
function statek_cholupice_cookie_preferences_button(): void {
	if ( ! statek_cholupice_analytics_is_allowed() ) {
		return;
	}
	?>
	<button class="footer-cookie-settings" type="button" data-cookie-preferences><?php esc_html_e( 'Nastavení cookies', 'statek-cholupice' ); ?></button>
	<?php
}

/**
 * Redirect the stable flyer URL to the campaign-tagged homepage.
 */
function statek_cholupice_flyer_redirect(): void {
	if ( ! in_array( statek_cholupice_request_hostname(), statek_cholupice_analytics_production_domains(), true ) ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path        = wp_parse_url( $request_uri, PHP_URL_PATH );
	if ( ! is_string( $path ) || ( '/letak' !== $path && '/letak/' !== $path ) ) {
		return;
	}

	$target = add_query_arg(
		array(
			'utm_source'   => 'letak',
			'utm_medium'   => 'qr',
			'utm_campaign' => 'spusteni_webu_2026',
			'utm_content'  => 'a5_cholupice',
		),
		'https://' . statek_cholupice_request_hostname() . '/'
	);

	nocache_headers();
	wp_safe_redirect( $target, 302, 'Statek Cholupice' );
	exit;
}
add_action( 'template_redirect', 'statek_cholupice_flyer_redirect', 1 );
