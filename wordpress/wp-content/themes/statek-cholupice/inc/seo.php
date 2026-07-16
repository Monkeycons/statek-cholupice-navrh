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

function statek_cholupice_home_social_title(): string {
	return 'Statek Cholupice | Informace o budoucnosti areálu';
}

function statek_cholupice_home_social_description(): string {
	return 'Podrobné informace o plánované proměně Statku Cholupice, budoucím provozu, bezpečnosti, dopravě, životním prostředí a podobě areálu.';
}

function statek_cholupice_meta_description(): string {
	if ( is_front_page() ) {
		return statek_cholupice_home_social_description();
	}

	if ( is_singular( 'post' ) ) {
		$description = wp_strip_all_tags( get_the_excerpt() );
		if ( '' !== trim( $description ) ) {
			return $description;
		}
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

function statek_cholupice_social_https_url( string $url ): string {
	$home_scheme = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
	return 'https' === $home_scheme ? set_url_scheme( $url, 'https' ) : $url;
}

function statek_cholupice_default_social_image(): array {
	return array(
		'url'    => statek_cholupice_social_https_url( statek_cholupice_asset_url( 'images/social/statek-cholupice-home-1200x630.jpg' ) ),
		'width'  => 1200,
		'height' => 630,
		'alt'    => 'Vizualizace budoucí podoby areálu Statku Cholupice',
	);
}

function statek_cholupice_social_image(): array {
	$default = statek_cholupice_default_social_image();

	if ( is_front_page() || ! is_singular() || ! has_post_thumbnail() ) {
		return $default;
	}

	$attachment_id = (int) get_post_thumbnail_id();
	$mime_type     = (string) get_post_mime_type( $attachment_id );
	$file_path     = (string) get_attached_file( $attachment_id );
	$file_type     = wp_check_filetype( $file_path );

	if ( 'image/svg+xml' === $mime_type || 'svg' === strtolower( (string) ( $file_type['ext'] ?? '' ) ) ) {
		return $default;
	}

	$image = wp_get_attachment_image_src( $attachment_id, 'full' );
	if ( ! is_array( $image ) || empty( $image[0] ) || empty( $image[1] ) || empty( $image[2] ) ) {
		return $default;
	}

	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( '' === $alt ) {
		$alt = get_the_title();
	}

	return array(
		'url'    => statek_cholupice_social_https_url( (string) $image[0] ),
		'width'  => (int) $image[1],
		'height' => (int) $image[2],
		'alt'    => wp_strip_all_tags( $alt ),
	);
}

function statek_cholupice_head_meta(): void {
	$description = statek_cholupice_meta_description();
	$url         = statek_cholupice_canonical_url();
	$title       = is_front_page() ? statek_cholupice_home_social_title() : wp_get_document_title();
	$image       = statek_cholupice_social_image();
	$type        = is_singular( 'post' ) ? 'article' : 'website';
	$site_name   = 'Statek Cholupice';
	$card        = 'summary_large_image';

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );
	printf( '<meta property="og:image:secure_url" content="%s">' . "\n", esc_url( $image['url'] ) );
	printf( '<meta property="og:image:width" content="%s">' . "\n", esc_attr( (string) absint( $image['width'] ) ) );
	printf( '<meta property="og:image:height" content="%s">' . "\n", esc_attr( (string) absint( $image['height'] ) ) );
	printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
	printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $card ) );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image['url'] ) );

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
