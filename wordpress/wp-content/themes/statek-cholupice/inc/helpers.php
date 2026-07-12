<?php
/**
 * Pomocné funkce šablony.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_asset_url( string $path ): string {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

function statek_cholupice_asset_path( string $path ): string {
	return get_theme_file_path( 'assets/' . ltrim( $path, '/' ) );
}

function statek_cholupice_asset_version( string $path ): string {
	$file = statek_cholupice_asset_path( $path );
	return file_exists( $file ) ? (string) filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

function statek_cholupice_anchor_url( string $anchor ): string {
	$anchor = ltrim( $anchor, '#' );
	if ( is_front_page() ) {
		return '#' . $anchor;
	}
	return home_url( '/#' . $anchor );
}

function statek_cholupice_default_menu_items(): array {
	return array(
		'projekt'           => __( 'O projektu', 'statek-cholupice' ),
		'bezpecnost'        => __( 'Bezpečnost', 'statek-cholupice' ),
		'doprava'           => __( 'Doprava', 'statek-cholupice' ),
		'zivotni-prostredi' => __( 'Životní prostředí', 'statek-cholupice' ),
		'prinosy'           => __( 'Přínosy', 'statek-cholupice' ),
		'kontakt'           => __( 'Časté dotazy', 'statek-cholupice' ),
	);
}

function statek_cholupice_primary_navigation( bool $mobile = false ): void {
	$items = statek_cholupice_default_menu_items();
	foreach ( $items as $anchor => $label ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( statek_cholupice_anchor_url( $anchor ) ),
			esc_html( $label )
		);
	}
	printf(
		'<a class="button" href="%s">%s</a>',
		esc_url( statek_cholupice_anchor_url( 'faq-contact-form' ) ),
		esc_html__( 'Kontakt', 'statek-cholupice' )
	);
}

function statek_cholupice_news_items(): array {
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$items = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( ! $image ) {
			$image = statek_cholupice_asset_url( 'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png' );
		}
		$items[] = array(
			'status'   => 'published',
			'slug'     => get_post_field( 'post_name', get_the_ID() ),
			'title'    => get_the_title(),
			'date'     => get_the_date( 'c' ),
			'image'    => esc_url_raw( $image ),
			'imageAlt' => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title(),
			'excerpt'  => wp_strip_all_tags( get_the_excerpt() ),
			'url'      => get_permalink(),
		);
	}
	wp_reset_postdata();

	return $items;
}
