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

function statek_cholupice_normalize_menu_url( string $url ): string {
	if ( ! is_front_page() ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || empty( $parts['fragment'] ) ) {
		return $url;
	}

	$home_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$same_host = empty( $parts['host'] ) || ( $home_host && $parts['host'] === $home_host );
	if ( $same_host ) {
		return '#' . ltrim( (string) $parts['fragment'], '#' );
	}

	return $url;
}

function statek_cholupice_contact_email(): string {
	$email = get_option( 'statek_cholupice_contact_email', 'info@statekcholupice.cz' );
	return is_email( $email ) ? $email : 'info@statekcholupice.cz';
}

function statek_cholupice_privacy_url(): string {
	$url = get_option( 'statek_cholupice_privacy_url', 'https://www.statekcholupice.cz/ochrana-osobnich-udaju/' );
	return esc_url_raw( $url ?: 'https://www.statekcholupice.cz/ochrana-osobnich-udaju/' );
}

function statek_cholupice_home_meta( string $key, string $fallback ): string {
	$post_id = (int) get_option( 'page_on_front' );
	if ( is_front_page() ) {
		$post_id = get_queried_object_id() ?: $post_id;
	}
	$value = $post_id ? (string) get_post_meta( $post_id, $key, true ) : '';
	return '' !== trim( $value ) ? $value : $fallback;
}

function statek_cholupice_default_faq_items(): array {
	return array(
		array(
			'question' => 'Proč zrovna Cholupice?',
			'answer'   => '<p>S růstem naší společnosti roste i potřeba nových prostor. Od začátku jsme ale nechtěli stavět „na zelené louce“. Naším cílem bylo najít místo s historií a potenciálem, kterému půjde vrátit život.</p><p>Bývalý hospodářský dvůr v Cholupicích nás zaujal svou atmosférou, historickou hodnotou i možností citlivé revitalizace. Dnes je areál dlouhodobě nevyužívaný a ve špatném technickém stavu. Projekt umožní jeho obnovu, odstranění ekologické zátěže a návrat smysluplného využití bez nutnosti zabírat další krajinu nebo zemědělskou půdu.</p><p>V Praze a Středočeském kraji existuje jen velmi málo míst, kde může podobný provoz vzniknout v již existujícím areálu a bez dalšího záboru krajiny. Jsme firma z Prahy, máme k tomuto regionu vztah a chceme zde dlouhodobě působit i investovat.</p>',
		),
		array(
			'question' => 'Bude se v areálu střílet?',
			'answer'   => '<p>Ne. Součástí areálu nebude střelnice ani zkušební střelba. Provoz nebude spojen s pravidelným hlukem tohoto typu.</p>',
		),
		array(
			'question' => 'Jak bude zabezpečena munice a kdo ponese odpovědnost za případné škody?',
			'answer'   => '<p>V areálu nebude skladována ani vyráběna munice. Projekt s tímto typem provozu vůbec nepočítá.</p>',
		),
		array(
			'question' => 'Kolik kamionů bude denně jezdit do vaší továrny?',
			'answer'   => '<p>Doprava bude oproti původnímu využití areálu výrazně menší. Předpokládá se především provoz menších zásobovacích vozů v rozsahu přibližně jedné až dvou dodávek denně. Nejde o těžký průmyslový nebo logistický provoz.</p>',
		),
		array(
			'question' => 'Co to přinese Cholupicím?',
			'answer'   => '<p>Projekt přinese revitalizaci chátrajícího areálu, odstranění staré ekologické zátěže, nová pracovní místa a podporu místních služeb i podnikatelů. Součástí bude také nová zeleň, úprava okolí a zlepšení stavu návsi.</p><p>Zatímco v minulosti byl areál spojen s provozem zemědělského družstva, těžkou technikou, prašností a zanedbanými objekty, nový projekt přináší moderní výrobu s minimálními dopady na okolí a výrazné zlepšení celkového vzhledu lokality.</p>',
		),
		array(
			'question' => 'Jak bude zajištěno, aby se výrobky nedostaly mimo kontrolovaný režim?',
			'answer'   => '<p>Provoz bude podléhat přísným bezpečnostním opatřením i státní kontrole. Areál bude zabezpečen moderními technologiemi a režimem odpovídajícím legislativním požadavkům na tento typ provozu.</p>',
		),
		array(
			'question' => 'Kdo nám vynahradí ztrátu hodnoty nemovitostí?',
			'answer'   => '<p>Neexistují důkazy, že by podobné projekty automaticky vedly ke snížení hodnoty nemovitostí. Naopak revitalizace zanedbaného areálu, odstranění ekologické zátěže a úprava okolí mohou mít pozitivní vliv na vzhled i fungování celé lokality.</p>',
		),
		array(
			'question' => 'Kdo za tímto projektem stojí?',
			'answer'   => '<p>Projekt připravuje a vlastní společnost DSS a.s., která ponese odpovědnost za provoz areálu, jeho zabezpečení i dodržování všech zákonných povinností.</p>',
		),
	);
}

function statek_cholupice_faq_items(): array {
	$json = statek_cholupice_home_meta( 'statek_home_faq_items', '' );
	if ( '' === $json ) {
		return statek_cholupice_default_faq_items();
	}
	$items = json_decode( $json, true );
	if ( ! is_array( $items ) ) {
		return statek_cholupice_default_faq_items();
	}
	$clean = array();
	foreach ( array_slice( $items, 0, 12 ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$question = sanitize_text_field( $item['question'] ?? '' );
		$answer   = wp_kses_post( $item['answer'] ?? '' );
		if ( '' !== $question && '' !== $answer ) {
			$order   = isset( $item['order'] ) ? max( 1, min( 12, (int) $item['order'] ) ) : $index + 1;
			$clean[] = compact( 'order', 'question', 'answer' ) + array( '_position' => $index );
		}
	}
	if ( ! $clean ) {
		return statek_cholupice_default_faq_items();
	}
	usort( $clean, static fn( $first, $second ) => ( $first['order'] <=> $second['order'] ) ?: ( $first['_position'] <=> $second['_position'] ) );
	return array_map(
		static function ( $item ) {
			unset( $item['_position'] );
			return $item;
		},
		$clean
	);
}

function statek_cholupice_image_variants( string $path, string $extension ): array {
	static $cache = array();

	$cache_key = $path . '|' . $extension;
	if ( array_key_exists( $cache_key, $cache ) ) {
		return $cache[ $cache_key ];
	}

	$image_path = preg_replace( '#^images/#', '', ltrim( $path, '/' ) );
	$stem       = pathinfo( $image_path, PATHINFO_FILENAME );
	$directory  = pathinfo( $image_path, PATHINFO_DIRNAME );
	$directory  = '.' === $directory ? '' : trailingslashit( $directory );
	$pattern    = statek_cholupice_asset_path( 'images/optimized/' . $directory . $stem . '-*.' . $extension );
	$files      = glob( $pattern );
	$variants   = array();

	if ( ! is_array( $files ) ) {
		$cache[ $cache_key ] = array();
		return $cache[ $cache_key ];
	}

	foreach ( $files as $file ) {
		if ( ! preg_match( '/-(\d+)\.' . preg_quote( $extension, '/' ) . '$/', $file, $matches ) ) {
			continue;
		}
		$width = (int) $matches[1];
		if ( $width <= 0 ) {
			continue;
		}
		$relative          = 'images/optimized/' . $directory . basename( $file );
		$variants[ $width ] = array(
			'width' => $width,
			'url'   => statek_cholupice_asset_url( $relative ),
			'path'  => $file,
		);
	}

	ksort( $variants, SORT_NUMERIC );
	$cache[ $cache_key ] = array_values( $variants );
	return $cache[ $cache_key ];
}

function statek_cholupice_srcset( array $variants ): string {
	$srcset = array();
	foreach ( $variants as $variant ) {
		$srcset[] = esc_url( $variant['url'] ) . ' ' . (int) $variant['width'] . 'w';
	}
	return implode( ', ', $srcset );
}

function statek_cholupice_largest_variant( array $variants ): ?array {
	if ( empty( $variants ) ) {
		return null;
	}
	return $variants[ array_key_last( $variants ) ];
}

function statek_cholupice_best_image_url( string $path, string $format = 'jpg' ): string {
	$variant = statek_cholupice_largest_variant( statek_cholupice_image_variants( $path, $format ) );
	if ( $variant ) {
		return $variant['url'];
	}
	return statek_cholupice_asset_url( $path );
}

function statek_cholupice_picture( string $path, string $alt, array $args = array() ): string {
	$defaults = array(
		'class'          => '',
		'picture_class'  => '',
		'sizes'          => '100vw',
		'loading'        => 'lazy',
		'decoding'       => 'async',
		'fetchpriority'  => '',
		'aria_hidden'    => false,
	);
	$args = wp_parse_args( $args, $defaults );

	$avif   = statek_cholupice_image_variants( $path, 'avif' );
	$webp   = statek_cholupice_image_variants( $path, 'webp' );
	$jpeg   = statek_cholupice_image_variants( $path, 'jpg' );
	$chosen = statek_cholupice_largest_variant( $jpeg ) ?: statek_cholupice_largest_variant( $webp );

	$src    = $chosen ? $chosen['url'] : statek_cholupice_asset_url( $path );
	$source = $chosen ? $chosen['path'] : statek_cholupice_asset_path( $path );
	static $size_cache = array();

	if ( array_key_exists( $source, $size_cache ) ) {
		$size = $size_cache[ $source ];
	} else {
		$size = is_readable( $source ) ? getimagesize( $source ) : false;
		$size_cache[ $source ] = $size;
	}
	$width  = is_array( $size ) ? (int) $size[0] : 0;
	$height = is_array( $size ) ? (int) $size[1] : 0;

	$picture_attrs = $args['picture_class'] ? ' class="' . esc_attr( $args['picture_class'] ) . '"' : '';
	$img_attrs     = array(
		'src="' . esc_url( $src ) . '"',
		'alt="' . esc_attr( $alt ) . '"',
	);

	if ( $args['class'] ) {
		$img_attrs[] = 'class="' . esc_attr( $args['class'] ) . '"';
	}
	if ( $width && $height ) {
		$img_attrs[] = 'width="' . esc_attr( (string) $width ) . '"';
		$img_attrs[] = 'height="' . esc_attr( (string) $height ) . '"';
	}
	if ( $args['loading'] ) {
		$img_attrs[] = 'loading="' . esc_attr( $args['loading'] ) . '"';
	}
	if ( $args['decoding'] ) {
		$img_attrs[] = 'decoding="' . esc_attr( $args['decoding'] ) . '"';
	}
	if ( $args['fetchpriority'] ) {
		$img_attrs[] = 'fetchpriority="' . esc_attr( $args['fetchpriority'] ) . '"';
	}
	if ( $args['aria_hidden'] ) {
		$img_attrs[] = 'aria-hidden="true"';
	}

	$output = '<picture' . $picture_attrs . '>';
	if ( $avif ) {
		$output .= '<source type="image/avif" srcset="' . esc_attr( statek_cholupice_srcset( $avif ) ) . '" sizes="' . esc_attr( $args['sizes'] ) . '">';
	}
	if ( $webp ) {
		$output .= '<source type="image/webp" srcset="' . esc_attr( statek_cholupice_srcset( $webp ) ) . '" sizes="' . esc_attr( $args['sizes'] ) . '">';
	}
	if ( $jpeg ) {
		$img_attrs[] = 'srcset="' . esc_attr( statek_cholupice_srcset( $jpeg ) ) . '"';
		$img_attrs[] = 'sizes="' . esc_attr( $args['sizes'] ) . '"';
	}
	$output .= '<img ' . implode( ' ', $img_attrs ) . '>';
	$output .= '</picture>';

	return $output;
}

function statek_cholupice_hero_picture(): string {
	$image_id = absint( statek_cholupice_home_meta( 'statek_home_hero_image_id', '0' ) );
	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		$image = wp_get_attachment_image(
			$image_id,
			'full',
			false,
			array(
				'class'         => 'hero-media-image',
				'alt'           => '',
				'sizes'         => '100vw',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
				'aria-hidden'   => 'true',
			)
		);
		if ( $image ) {
			return '<picture class="hero-media">' . $image . '</picture>';
		}
	}

	return statek_cholupice_picture(
		'images/hero_vizualizace/cholupice-hero-super-render-web-spravne.png',
		'',
		array(
			'picture_class' => 'hero-media',
			'class'         => 'hero-media-image',
			'sizes'         => '100vw',
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'aria_hidden'   => true,
		)
	);
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

class Statek_Cholupice_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = array();
		$url     = isset( $item->url ) ? statek_cholupice_normalize_menu_url( $item->url ) : '';

		if ( str_contains( $url, 'faq-contact-form' ) || 'Kontakt' === trim( wp_strip_all_tags( $item->title ) ) ) {
			$classes[] = 'button';
		}
		if ( is_array( $item->classes ) ) {
			foreach ( $item->classes as $class ) {
				if ( ! is_string( $class ) || '' === $class ) {
					continue;
				}
				if ( str_starts_with( $class, 'menu-item' ) || str_starts_with( $class, 'page-item' ) || str_starts_with( $class, 'page_item' ) || str_starts_with( $class, 'current-' ) || str_starts_with( $class, 'current_' ) || str_starts_with( $class, 'current_page' ) ) {
					continue;
				}
				$classes[] = $class;
			}
		}

		$output .= sprintf(
			'<a%s href="%s">%s</a>',
			$classes ? ' class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"' : '',
			esc_url( $url ),
			esc_html( $item->title )
		);
	}
}

function statek_cholupice_primary_navigation_fallback(): void {
	foreach ( statek_cholupice_default_menu_items() as $anchor => $label ) {
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

function statek_cholupice_primary_navigation( bool $mobile = false ): void {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 1,
				'walker'         => new Statek_Cholupice_Nav_Walker(),
				'fallback_cb'    => 'statek_cholupice_primary_navigation_fallback',
			)
		);
		return;
	}

	statek_cholupice_primary_navigation_fallback();
}

function statek_cholupice_news_query( int $posts_per_page = 6 ): WP_Query {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'ignore_sticky_posts' => true,
		)
	);
}

function statek_cholupice_news_items(): array {
	$query = statek_cholupice_news_query( 6 );

	$items = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( ! $image ) {
			$image = statek_cholupice_best_image_url( 'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png', 'jpg' );
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
