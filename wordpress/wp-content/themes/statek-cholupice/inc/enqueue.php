<?php
/**
 * Načítání front-end assetů.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_enqueue_assets(): void {
	wp_enqueue_style(
		'statek-cholupice-main',
		statek_cholupice_asset_url( 'css/main.css' ),
		array(),
		statek_cholupice_asset_version( 'css/main.css' )
	);

	wp_enqueue_script(
		'statek-cholupice-main',
		statek_cholupice_asset_url( 'js/main.js' ),
		array(),
		statek_cholupice_asset_version( 'js/main.js' ),
		true
	);

	wp_localize_script(
		'statek-cholupice-main',
		'StatekCholupice',
		array(
			'contactEndpoint' => esc_url_raw( rest_url( 'statek-cholupice/v1/contact' ) ),
			'contactNonce'    => wp_create_nonce( 'wp_rest' ),
			'newsIndexUrl'    => esc_url_raw( get_post_type_archive_link( 'post' ) ?: home_url( '/novinky/' ) ),
			'newsItems'       => statek_cholupice_news_items(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'statek_cholupice_enqueue_assets' );

function statek_cholupice_preload_assets(): void {
	$font = statek_cholupice_asset_url( 'fonts/InterVariable.woff2' );
	echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "
";
}
add_action( 'wp_head', 'statek_cholupice_preload_assets', 1 );
