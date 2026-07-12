<?php
/**
 * Lehká produkční metadata bez SEO pluginu.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_meta_description(): string {
	if ( is_singular( 'post' ) ) {
		return wp_strip_all_tags( get_the_excerpt() );
	}
	return 'Proměna bývalého hospodářského areálu v Cholupicích: nové využití brownfieldu, služby, pracovní místa, zeleň a záchrana historického špejcharu.';
}

function statek_cholupice_head_meta(): void {
	$description = statek_cholupice_meta_description();
	$url         = is_singular() ? get_permalink() : home_url( '/' );
	$title       = wp_get_document_title();
	$image       = statek_cholupice_asset_url( 'images/hero_vizualizace/cholupice-hero-super-render-web-spravne.png' );

	printf( '<meta name="description" content="%s">' . "
", esc_attr( $description ) );
	printf( '<link rel="canonical" href="%s">' . "
", esc_url( $url ) );
	printf( '<meta property="og:type" content="%s">' . "
", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "
", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "
", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "
", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "
", esc_url( $image ) );
	echo '<meta name="twitter:card" content="summary_large_image">' . "
";
}
add_action( 'wp_head', 'statek_cholupice_head_meta', 5 );
