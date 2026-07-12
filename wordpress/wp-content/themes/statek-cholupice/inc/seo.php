<?php
/**
 * Lehká produkční metadata bez SEO pluginu.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'wp_head', 'rel_canonical' );

function statek_cholupice_meta_description(): string {
	if ( is_singular( 'post' ) ) {
		return wp_strip_all_tags( get_the_excerpt() );
	}
	return 'Proměna bývalého hospodářského areálu v Cholupicích: nové využití brownfieldu, služby, pracovní místa, zeleň a záchrana historického špejcharu.';
}

function statek_cholupice_canonical_url(): string {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_home() || is_archive() || is_search() ) {
		return get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
	}
	return home_url( '/' );
}

function statek_cholupice_og_image_url(): string {
	if ( is_singular( 'post' ) && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( $image ) {
			return $image;
		}
	}
	return statek_cholupice_best_image_url( 'images/hero_vizualizace/cholupice-hero-super-render-web-spravne.png', 'jpg' );
}

function statek_cholupice_head_meta(): void {
	$description = statek_cholupice_meta_description();
	$url         = statek_cholupice_canonical_url();
	$title       = wp_get_document_title();
	$image       = statek_cholupice_og_image_url();

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	$schema = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'WebSite',
				'@id'   => home_url( '/#website' ),
				'name'  => 'Statek Cholupice',
				'url'   => home_url( '/' ),
			),
			array(
				'@type' => 'Organization',
				'@id'   => home_url( '/#organization' ),
				'name'  => 'DSS a.s.',
				'url'   => home_url( '/' ),
				'email' => statek_cholupice_contact_email(),
			),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'statek_cholupice_head_meta', 5 );
