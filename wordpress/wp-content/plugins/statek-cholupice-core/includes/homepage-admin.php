<?php
/**
 * Nativní editace obsahu homepage bez ACF.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_field_limits(): array {
	return array(
		'hero_kicker'      => 50,
		'hero_title'       => 90,
		'hero_text'        => 320,
		'cta_label'        => 40,
		'cta_url'          => 2048,
		'section_heading'  => 140,
		'card_heading'     => 90,
		'motto'            => 240,
		'short_intro'      => 500,
		'benefit_text'     => 500,
		'list_text'        => 700,
		'long_paragraph'   => 1800,
		'faq_question'     => 180,
		'faq_answer'       => 3000,
		'contact_heading'  => 140,
		'rich_text'        => 900,
		'footer_short'     => 500,
		'footer_text'      => 1200,
		'alt_text'         => 180,
	);
}

function statek_cholupice_core_field_limit( string $key ): int {
	$limits = statek_cholupice_core_field_limits();
	return isset( $limits[ $key ] ) ? (int) $limits[ $key ] : 0;
}

function statek_cholupice_core_unicode_substr( string $value, int $limit ): string {
	if ( $limit <= 0 ) {
		return $value;
	}
	if ( function_exists( 'mb_substr' ) ) {
		return (string) mb_substr( $value, 0, $limit, 'UTF-8' );
	}
	$match_count = preg_match_all( '/./us', $value, $characters );
	if ( false !== $match_count ) {
		return implode( '', array_slice( $characters[0], 0, $limit ) );
	}
	return substr( $value, 0, $limit );
}

function statek_cholupice_core_clean_html( $value, int $limit ): string {
	$value = statek_cholupice_core_unicode_substr( (string) $value, $limit );
	return trim( force_balance_tags( wp_kses_post( $value ) ) );
}

function statek_cholupice_core_cta_url_fallback( string $key ): string {
	return 'statek_home_hero_secondary_url' === $key ? '#prinosy' : '#projekt';
}

function statek_cholupice_core_sanitize_cta_url( $value, string $fallback ): string {
	$value = trim( statek_cholupice_core_unicode_substr( (string) $value, statek_cholupice_core_field_limit( 'cta_url' ) ) );
	if ( '' === $value ) {
		return $fallback;
	}
	if ( str_starts_with( $value, '#' ) ) {
		return preg_match( '/^#[A-Za-z][A-Za-z0-9_.:-]*$/', $value ) ? $value : $fallback;
	}
	if ( preg_match( '/[\\x00-\\x20\\x7f]/', $value ) || str_contains( $value, '\\' ) || str_starts_with( $value, '//' ) ) {
		return $fallback;
	}

	$parts  = wp_parse_url( $value );
	$scheme = is_array( $parts ) && isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
	if ( '' !== $scheme ) {
		if ( 'https' !== $scheme || empty( $parts['host'] ) ) {
			return $fallback;
		}
		$clean = esc_url_raw( $value, array( 'https' ) );
		return '' !== $clean ? $clean : $fallback;
	}

	if ( ! preg_match( '#^(?:/(?!/)|\.{1,2}/|\?|[A-Za-z0-9_-])#', $value ) ) {
		return $fallback;
	}
	$prefixed = ! str_starts_with( $value, '/' ) && ! str_starts_with( $value, '?' );
	$clean    = esc_url_raw( $prefixed ? './' . $value : $value, array( 'https' ) );
	if ( $prefixed && str_starts_with( $clean, './' ) ) {
		$clean = substr( $clean, 2 );
	}
	return '' !== $clean ? $clean : $fallback;
}

function statek_cholupice_core_clean_image_id( $value ): int {
	$image_id = absint( $value );
	return $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0;
}

function statek_cholupice_core_normalize_order( $value, int $position, int $maximum ): int {
	$order = is_scalar( $value ) && preg_match( '/^[0-9]+$/', trim( (string) $value ) ) ? (int) $value : $position + 1;
	return max( 1, min( $maximum, $order ) );
}

function statek_cholupice_core_stable_order_sort( array $items ): array {
	usort(
		$items,
		static fn( $first, $second ) => ( $first['order'] <=> $second['order'] ) ?: ( $first['_position'] <=> $second['_position'] )
	);
	return array_map(
		static function ( $item ) {
			unset( $item['_position'] );
			return $item;
		},
		$items
	);
}

function statek_cholupice_core_home_fields(): array {
	return array(
		'statek_home_hero_kicker'        => array( 'type' => 'text', 'limit' => 'hero_kicker' ),
		'statek_home_hero_title'         => array( 'type' => 'text', 'limit' => 'hero_title' ),
		'statek_home_hero_text'          => array( 'type' => 'text', 'limit' => 'hero_text' ),
		'statek_home_hero_primary'       => array( 'type' => 'text', 'limit' => 'cta_label' ),
		'statek_home_hero_secondary'     => array( 'type' => 'text', 'limit' => 'cta_label' ),
		'statek_home_hero_primary_url'   => array( 'type' => 'url', 'limit' => 'cta_url' ),
		'statek_home_hero_secondary_url' => array( 'type' => 'url', 'limit' => 'cta_url' ),
		'statek_home_hero_image_id'      => array( 'type' => 'image_id' ),
		'statek_home_faq_heading'        => array( 'type' => 'text', 'limit' => 'section_heading' ),
		'statek_home_faq_motto'          => array( 'type' => 'text', 'limit' => 'motto' ),
		'statek_home_faq_intro_1'        => array( 'type' => 'text', 'limit' => 'short_intro' ),
		'statek_home_faq_intro_2'        => array( 'type' => 'html', 'limit' => 'rich_text' ),
		'statek_home_contact_heading'    => array( 'type' => 'text', 'limit' => 'contact_heading' ),
		'statek_home_contact_motto'      => array( 'type' => 'text', 'limit' => 'motto' ),
		'statek_home_contact_text'       => array( 'type' => 'html', 'limit' => 'rich_text' ),
		'statek_home_area_heading'       => array( 'type' => 'text', 'limit' => 'section_heading' ),
		'statek_home_area_motto'         => array( 'type' => 'text', 'limit' => 'motto' ),
		'statek_home_project_blocks'     => array( 'type' => 'json' ),
		'statek_home_area_items'         => array( 'type' => 'json' ),
		'statek_home_operation'          => array( 'type' => 'json' ),
		'statek_home_topics'             => array( 'type' => 'json' ),
		'statek_home_benefits'           => array( 'type' => 'json' ),
		'statek_home_faq_items'          => array( 'type' => 'json' ),
		'statek_home_footer'             => array( 'type' => 'json' ),
	);
}

function statek_cholupice_core_register_home_meta(): void {
	foreach ( statek_cholupice_core_home_fields() as $key => $field ) {
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'statek_cholupice_core_sanitize_home_meta',
				'auth_callback'     => static fn( ...$args ) => current_user_can( 'edit_pages' ),
				'show_in_rest'      => false,
			)
		);
	}
}
add_action( 'init', 'statek_cholupice_core_register_home_meta' );

function statek_cholupice_core_sanitize_home_meta( $value, string $key = '' ): string {
	$value = is_scalar( $value ) ? (string) $value : '';
	$field = statek_cholupice_core_home_fields()[ $key ] ?? null;
	if ( $field && 'json' === $field['type'] ) {
		$decoded = json_decode( $value, true );
		return is_array( $decoded ) ? (string) wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
	}
	if ( $field && 'html' === $field['type'] ) {
		return statek_cholupice_core_clean_html( $value, statek_cholupice_core_field_limit( $field['limit'] ?? '' ) );
	}
	if ( $field && 'url' === $field['type'] ) {
		return statek_cholupice_core_sanitize_cta_url( $value, statek_cholupice_core_cta_url_fallback( $key ) );
	}
	if ( $field && 'image_id' === $field['type'] ) {
		return (string) statek_cholupice_core_clean_image_id( $value );
	}
	return statek_cholupice_core_clean_textarea( $value, statek_cholupice_core_field_limit( $field['limit'] ?? '' ) );
}

function statek_cholupice_core_is_front_page_edit( ?WP_Post $post ): bool {
	return $post instanceof WP_Post && 'page' === $post->post_type && (int) $post->ID === (int) get_option( 'page_on_front' );
}

function statek_cholupice_core_home_metabox( string $post_type, ?WP_Post $post = null ): void {
	if ( 'page' !== $post_type || ! statek_cholupice_core_is_front_page_edit( $post ) ) {
		return;
	}

	$boxes = array(
		'hero'      => array( 'Hero a úvod FAQ', 'statek_cholupice_core_render_home_hero_metabox', 'high' ),
		'project'   => array( 'O projektu', 'statek_cholupice_core_render_project_metabox', 'default' ),
		'area'      => array( 'Šest částí areálu', 'statek_cholupice_core_render_area_metabox', 'default' ),
		'operation' => array( 'Jak bude areál fungovat', 'statek_cholupice_core_render_operation_metabox', 'default' ),
		'topics'    => array( 'Bezpečnost, doprava a životní prostředí', 'statek_cholupice_core_render_topics_metabox', 'default' ),
		'benefits'  => array( 'Přínosy', 'statek_cholupice_core_render_benefits_metabox', 'default' ),
		'faq'       => array( 'Časté dotazy', 'statek_cholupice_core_render_faq_metabox', 'default' ),
		'contact'   => array( 'Kontakt a patička', 'statek_cholupice_core_render_contact_footer_metabox', 'default' ),
	);

	foreach ( $boxes as $id => $box ) {
		add_meta_box(
			'statek-cholupice-home-' . $id,
			$box[0],
			$box[1],
			'page',
			'normal',
			$box[2]
		);
	}
}
add_action( 'add_meta_boxes', 'statek_cholupice_core_home_metabox', 10, 2 );

function statek_cholupice_core_enqueue_home_admin( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || (int) $post_id !== (int) get_option( 'page_on_front' ) ) {
		return;
	}

	$plugin_file = dirname( __DIR__ ) . '/statek-cholupice-core.php';
	$base_url    = plugin_dir_url( $plugin_file );
	$base_path   = plugin_dir_path( $plugin_file );
	$css_path    = $base_path . 'assets/admin-homepage.css';
	$js_path     = $base_path . 'assets/admin-homepage.js';
	$css_version = file_exists( $css_path ) ? (string) filemtime( $css_path ) : STATEK_CHOLUPICE_CORE_VERSION;
	$js_version  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : STATEK_CHOLUPICE_CORE_VERSION;
	wp_enqueue_media();
	wp_enqueue_style( 'statek-cholupice-home-admin', $base_url . 'assets/admin-homepage.css', array(), $css_version );
	wp_enqueue_script( 'statek-cholupice-home-admin', $base_url . 'assets/admin-homepage.js', array(), $js_version, true );
}
add_action( 'admin_enqueue_scripts', 'statek_cholupice_core_enqueue_home_admin' );

function statek_cholupice_core_theme_data( string $function, array $fallback = array() ): array {
	return function_exists( $function ) ? (array) call_user_func( $function ) : $fallback;
}

function statek_cholupice_core_meta_value( WP_Post $post, string $key, string $fallback = '' ): string {
	$value = (string) get_post_meta( $post->ID, $key, true );
	return '' !== trim( $value ) ? $value : $fallback;
}

function statek_cholupice_core_field_id( string $name ): string {
	$name = str_replace( '__INDEX__', 'statekrepeaterindextoken', $name );
	$id   = sanitize_key( str_replace( array( '[', ']' ), '_', $name ) );
	return str_replace( 'statekrepeaterindextoken', '__INDEX__', $id );
}

function statek_cholupice_core_limit_key_for_name( string $name ): string {
	$field = statek_cholupice_core_home_fields()[ $name ] ?? null;
	if ( is_array( $field ) && ! empty( $field['limit'] ) ) {
		return (string) $field['limit'];
	}
	if ( str_ends_with( $name, '[alt]' ) ) {
		return 'alt_text';
	}
	if ( str_ends_with( $name, '[question]' ) ) {
		return 'faq_question';
	}
	if ( str_ends_with( $name, '[answer]' ) ) {
		return 'faq_answer';
	}
	if ( str_contains( $name, 'statek_home_benefits[cards]' ) && str_ends_with( $name, '[text]' ) ) {
		return 'benefit_text';
	}
	if ( str_contains( $name, 'statek_home_topics' ) && str_ends_with( $name, '[title]' ) ) {
		return 'section_heading';
	}
	if ( str_contains( $name, 'statek_home_topics' ) && str_ends_with( $name, '[intro]' ) ) {
		return 'long_paragraph';
	}
	if ( str_contains( $name, 'statek_home_footer' ) ) {
		if ( str_ends_with( $name, '[info_text]' ) || str_ends_with( $name, '[visuals_text]' ) ) {
			return 'footer_text';
		}
		if ( str_ends_with( $name, '[info_heading]' ) || str_ends_with( $name, '[visuals_heading]' ) ) {
			return 'section_heading';
		}
		return 'footer_short';
	}
	if ( str_contains( $name, '[paragraphs]' ) || str_contains( $name, '[details]' ) || str_contains( $name, '[intro][' ) ) {
		return 'long_paragraph';
	}
	if ( str_ends_with( $name, '[heading]' ) ) {
		return 'section_heading';
	}
	if ( str_ends_with( $name, '[motto]' ) ) {
		return 'motto';
	}
	if ( str_ends_with( $name, '[intro]' ) ) {
		return 'short_intro';
	}
	if ( str_ends_with( $name, '[title]' ) ) {
		return 'card_heading';
	}
	if ( str_ends_with( $name, '[text]' ) ) {
		return 'list_text';
	}
	return '';
}

function statek_cholupice_core_render_field( string $name, string $label, string $value, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'type'        => 'text',
			'rows'        => 3,
			'description' => '',
			'class'       => '',
			'input_class' => '',
			'limit'       => '',
			'min'         => '',
			'max'         => '',
			'order'       => false,
		)
	);
	$id        = statek_cholupice_core_field_id( $name );
	$limit_key = $args['limit'] ?: statek_cholupice_core_limit_key_for_name( $name );
	$maxlength = statek_cholupice_core_field_limit( (string) $limit_key );
	$attrs     = array(
		'id="' . esc_attr( $id ) . '"',
		'name="' . esc_attr( $name ) . '"',
	);
	if ( $args['input_class'] ) {
		$attrs[] = 'class="' . esc_attr( (string) $args['input_class'] ) . '"';
	}
	if ( $maxlength > 0 && 'number' !== $args['type'] ) {
		$attrs[] = 'maxlength="' . esc_attr( (string) $maxlength ) . '"';
	}
	if ( '' !== (string) $args['min'] ) {
		$attrs[] = 'min="' . esc_attr( (string) $args['min'] ) . '"';
	}
	if ( '' !== (string) $args['max'] ) {
		$attrs[] = 'max="' . esc_attr( (string) $args['max'] ) . '"';
	}
	if ( $args['order'] ) {
		$attrs[] = 'data-order-field';
	}
	echo '<label class="statek-admin-field ' . esc_attr( $args['class'] ) . '" for="' . esc_attr( $id ) . '">';
	echo '<span>' . esc_html( $label ) . '</span>';
	if ( 'textarea' === $args['type'] ) {
		$attrs[] = 'rows="' . esc_attr( (string) $args['rows'] ) . '"';
		echo '<textarea ' . implode( ' ', $attrs ) . '>' . esc_textarea( $value ) . '</textarea>';
	} else {
		if ( 'url' === $args['type'] ) {
			$attrs[] = 'inputmode="url"';
			$attrs[] = 'autocapitalize="off"';
			$attrs[] = 'spellcheck="false"';
		}
		$attrs[] = 'type="' . esc_attr( 'url' === $args['type'] ? 'text' : (string) $args['type'] ) . '"';
		$attrs[] = 'value="' . esc_attr( $value ) . '"';
		echo '<input ' . implode( ' ', $attrs ) . '>';
	}
	$description = trim( (string) $args['description'] );
	if ( $maxlength > 0 ) {
		$description = trim( $description . ' Maximálně ' . $maxlength . ' znaků.' );
	}
	if ( $description ) {
		echo '<small>' . esc_html( $description ) . '</small>';
	}
	echo '</label>';
}

function statek_cholupice_core_render_paragraphs( string $name, array $paragraphs, string $label, int $max = 6 ): void {
	echo '<div class="statek-admin-field statek-repeater" data-max="' . esc_attr( (string) $max ) . '">';
	echo '<span>' . esc_html( $label ) . '</span>';
	echo '<div class="statek-repeater-items">';
	foreach ( array_slice( $paragraphs, 0, $max ) as $index => $paragraph ) {
		statek_cholupice_core_render_paragraph_row( $name, (int) $index, (string) $paragraph );
	}
	echo '</div>';
	echo '<template>';
	statek_cholupice_core_render_paragraph_row( $name, '__INDEX__', '' );
	echo '</template>';
	echo '<button type="button" class="button statek-repeater-add">Přidat odstavec</button>';
	echo '<small>Doporučení: kratší odstavce drží layout přehledný. Maximum ' . esc_html( (string) $max ) . ' odstavců, každý nejvýše ' . esc_html( (string) statek_cholupice_core_field_limit( 'long_paragraph' ) ) . ' znaků.</small>';
	echo '</div>';
}

function statek_cholupice_core_render_paragraph_row( string $name, $index, string $value ): void {
	$field_name = $name . '[' . $index . ']';
	$limit      = statek_cholupice_core_field_limit( 'long_paragraph' );
	echo '<div class="statek-repeater-row statek-admin-inline-row" data-repeater-index="' . esc_attr( (string) $index ) . '">';
	echo '<textarea id="' . esc_attr( statek_cholupice_core_field_id( $field_name ) ) . '" name="' . esc_attr( $field_name ) . '" rows="3" maxlength="' . esc_attr( (string) $limit ) . '">' . esc_textarea( $value ) . '</textarea>';
	echo '<button type="button" class="button-link-delete statek-repeater-remove">Odebrat</button>';
	echo '</div>';
}

function statek_cholupice_core_render_media_field( string $name, array $image, string $label, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'id_name'      => $name . '[id]',
			'show_alt'     => true,
			'description'  => '',
			'fallback_text' => 'Je použit schválený výchozí obrázek šablony.',
		)
	);
	$id      = absint( $image['id'] ?? 0 );
	$alt     = (string) ( $image['alt'] ?? '' );
	$preview = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
	echo '<div class="statek-admin-field statek-media-field" data-fallback-text="' . esc_attr( (string) $args['fallback_text'] ) . '">';
	echo '<span>' . esc_html( $label ) . '</span>';
	echo '<input class="statek-media-id" name="' . esc_attr( (string) $args['id_name'] ) . '" type="hidden" value="' . esc_attr( (string) $id ) . '">';
	echo '<div class="statek-media-preview">';
	if ( $preview ) {
		echo '<img src="' . esc_url( $preview ) . '" alt="">';
	} else {
		echo '<em>' . esc_html( (string) $args['fallback_text'] ) . '</em>';
	}
	echo '</div>';
	echo '<p><button type="button" class="button statek-media-select">Vybrat obrázek</button> <button type="button" class="button-link-delete statek-media-clear">Odebrat vybraný obrázek</button></p>';
	if ( $args['show_alt'] ) {
		statek_cholupice_core_render_field( $name . '[alt]', 'Alt text obrázku', $alt, array( 'description' => 'Po výměně obrázku zkontrolujte jeho alt text. Nevytváří se automaticky z názvu souboru.' ) );
	}
	if ( $args['description'] ) {
		echo '<small>' . esc_html( (string) $args['description'] ) . '</small>';
	}
	echo '<p class="statek-media-status" aria-live="polite"></p>';
	echo '</div>';
}

function statek_cholupice_core_render_home_hero_metabox( WP_Post $post ): void {
	$hero_image_id = absint( get_post_meta( $post->ID, 'statek_home_hero_image_id', true ) );
	wp_nonce_field( 'statek_cholupice_home_save', 'statek_cholupice_home_nonce' );
	echo '<p class="statek-admin-help">Prázdné textové pole ponechá schválený výchozí obsah. Vlastní obrázek a cíle tlačítek nemění schválený layout hero sekce.</p>';
	echo '<div class="statek-admin-grid">';
	statek_cholupice_core_render_field( 'statek_home_hero_kicker', 'Hero - malý nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_kicker', 'Revitalizace brownfieldu' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_title', 'Hero - hlavní nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_title', 'Nový život pro Statek Cholupice' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_text', 'Hero - podnadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_text', 'Citlivá přestavba historického areálu propojí bydlení, služby pro obyvatele, moderní výrobu a respekt k místu.' ), array( 'type' => 'textarea', 'rows' => 3 ) );
	statek_cholupice_core_render_field( 'statek_home_hero_primary', 'Hero - první tlačítko', statek_cholupice_core_meta_value( $post, 'statek_home_hero_primary', 'Poznat projekt' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_secondary', 'Hero - druhé tlačítko', statek_cholupice_core_meta_value( $post, 'statek_home_hero_secondary', 'Dobrý soused' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_primary_url', 'Cíl prvního tlačítka', statek_cholupice_core_meta_value( $post, 'statek_home_hero_primary_url', '#projekt' ), array( 'type' => 'url', 'description' => 'Kotva, interní cesta nebo bezpečná HTTPS URL.' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_secondary_url', 'Cíl druhého tlačítka', statek_cholupice_core_meta_value( $post, 'statek_home_hero_secondary_url', '#prinosy' ), array( 'type' => 'url', 'description' => 'Kotva, interní cesta nebo bezpečná HTTPS URL.' ) );
	echo '<div class="statek-admin-wide">';
	statek_cholupice_core_render_media_field(
		'statek_home_hero_image',
		array( 'id' => $hero_image_id, 'alt' => '' ),
		'Hero obrázek',
		array(
			'id_name'       => 'statek_home_hero_image_id',
			'show_alt'      => false,
			'description'   => 'Doporučeno alespoň 1920 × 1080 px v poměru 16:9. Hero je dekorativní a používá prázdný alt text.',
			'fallback_text' => 'Je použit schválený optimalizovaný hero obrázek šablony.',
		)
	);
	echo '</div>';
	statek_cholupice_core_render_field( 'statek_home_faq_heading', 'Časté dotazy - nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_faq_heading', 'Na co se nás lidé ptají nejčastěji' ) );
	statek_cholupice_core_render_field( 'statek_home_faq_motto', 'Časté dotazy - podnadpis', statek_cholupice_core_meta_value( $post, 'statek_home_faq_motto', 'Vše podstatné o proměně statku, budoucím provozu a jeho dopadech na okolí.' ), array( 'type' => 'textarea', 'rows' => 2 ) );
	statek_cholupice_core_render_field( 'statek_home_faq_intro_1', 'Časté dotazy - úvodní odstavec', statek_cholupice_core_meta_value( $post, 'statek_home_faq_intro_1', 'Uvědomujeme si, že statek je významnou součástí Cholupic, a rozumíme proto tomu, že jeho plánovaná proměna vyvolává otázky. Na ty nejčastější zde otevřeně odpovídáme.' ), array( 'type' => 'textarea', 'rows' => 4, 'class' => 'statek-admin-wide' ) );
	statek_cholupice_core_render_field( 'statek_home_faq_intro_2', 'Časté dotazy - odstavec s kontaktem', statek_cholupice_core_meta_value( $post, 'statek_home_faq_intro_2', 'Pokud odpověď na svou otázku nenajdete, <a href="#faq-contact-form">napište nám</a>. Vaše podněty budeme průběžně zpracovávat a nejčastější otázky doplňovat.' ), array( 'type' => 'textarea', 'rows' => 4, 'class' => 'statek-admin-wide', 'description' => 'Toto pole může obsahovat jednoduchý odkaz.' ) );
	echo '</div>';
}

function statek_cholupice_core_render_project_metabox(): void {
	$blocks = statek_cholupice_core_theme_data( 'statek_cholupice_project_blocks' );
	foreach ( array_slice( $blocks, 0, 2 ) as $index => $block ) {
		echo '<div class="statek-admin-card">';
		echo '<h3>' . esc_html( 0 === $index ? 'První část' : 'Druhá část a porovnání před / po' ) . '</h3>';
		statek_cholupice_core_render_field( "statek_home_project_blocks[$index][title]", 'Nadpis', (string) ( $block['title'] ?? '' ) );
		statek_cholupice_core_render_paragraphs( "statek_home_project_blocks[$index][paragraphs]", (array) ( $block['paragraphs'] ?? array() ), 'Odstavce', 5 );
		if ( 0 === $index ) {
			statek_cholupice_core_render_media_field( "statek_home_project_blocks[$index][image]", (array) ( $block['image'] ?? array() ), 'Hlavní obrázek' );
		} else {
			statek_cholupice_core_render_media_field( "statek_home_project_blocks[$index][before]", (array) ( $block['before'] ?? array() ), 'Obrázek současného stavu' );
			statek_cholupice_core_render_media_field( "statek_home_project_blocks[$index][after]", (array) ( $block['after'] ?? array() ), 'Obrázek navrhované podoby' );
		}
		echo '</div>';
	}
}

function statek_cholupice_core_render_area_metabox( WP_Post $post ): void {
	$items = statek_cholupice_core_theme_data( 'statek_cholupice_area_items' );
	echo '<p class="statek-admin-help">Layout počítá maximálně se šesti částmi. Pořadí určíte číslem 1-6.</p>';
	echo '<div class="statek-admin-card">';
	statek_cholupice_core_render_field( 'statek_home_area_heading', 'Nadpis kapitoly', statek_cholupice_core_meta_value( $post, 'statek_home_area_heading', 'Šest částí, jeden živý areál' ) );
	statek_cholupice_core_render_field( 'statek_home_area_motto', 'Podnadpis kapitoly', statek_cholupice_core_meta_value( $post, 'statek_home_area_motto', 'Promyšlené spojení různých funkcí vrací Statku Cholupice život.' ), array( 'type' => 'textarea', 'rows' => 2 ) );
	echo '</div>';
	foreach ( array_slice( $items, 0, 6 ) as $index => $item ) {
		echo '<div class="statek-admin-card">';
		echo '<h3>' . esc_html( (string) ( $item['title'] ?? 'Část areálu' ) ) . '</h3>';
		statek_cholupice_core_render_field( "statek_home_area_items[$index][order]", 'Pořadí', (string) ( $item['order'] ?? ( $index + 1 ) ), array( 'type' => 'number', 'min' => 1, 'max' => 6, 'order' => true, 'input_class' => 'statek-order-input', 'description' => 'Číslo 1 až 6.' ) );
		statek_cholupice_core_render_field( "statek_home_area_items[$index][title]", 'Název', (string) ( $item['title'] ?? '' ) );
		statek_cholupice_core_render_paragraphs( "statek_home_area_items[$index][paragraphs]", (array) ( $item['paragraphs'] ?? array() ), 'Odstavce', 3 );
		statek_cholupice_core_render_media_field( "statek_home_area_items[$index][image]", (array) ( $item['image'] ?? array() ), 'Obrázek' );
		echo '</div>';
	}
}

function statek_cholupice_core_render_operation_metabox(): void {
	$operation = statek_cholupice_core_theme_data( 'statek_cholupice_operation_data' );
	echo '<div class="statek-admin-card">';
	statek_cholupice_core_render_field( 'statek_home_operation[heading]', 'Nadpis', (string) ( $operation['heading'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_operation[motto]', 'Motto', (string) ( $operation['motto'] ?? '' ) );
	statek_cholupice_core_render_paragraphs( 'statek_home_operation[intro]', (array) ( $operation['intro'] ?? array() ), 'Úvodní odstavce', 4 );
	echo '</div>';
	statek_cholupice_core_render_list_repeater( 'statek_home_operation[include]', (array) ( $operation['include'] ?? array() ), 'Co bude součástí provozu', 6 );
	statek_cholupice_core_render_list_repeater( 'statek_home_operation[exclude]', (array) ( $operation['exclude'] ?? array() ), 'Co nebude součástí provozu', 6 );
}

function statek_cholupice_core_render_list_repeater( string $name, array $items, string $heading, int $max ): void {
	echo '<div class="statek-admin-card statek-repeater" data-max="' . esc_attr( (string) $max ) . '">';
	echo '<h3>' . esc_html( $heading ) . '</h3>';
	echo '<div class="statek-repeater-items">';
	foreach ( array_slice( $items, 0, $max ) as $index => $item ) {
		statek_cholupice_core_render_list_row( $name, (int) $index, (array) $item );
	}
	echo '</div><template>';
	statek_cholupice_core_render_list_row( $name, '__INDEX__', array() );
	echo '</template>';
	echo '<button type="button" class="button statek-repeater-add">Přidat položku</button>';
	echo '<p class="description">Maximum ' . esc_html( (string) $max ) . ' položek.</p>';
	echo '</div>';
}

function statek_cholupice_core_render_list_row( string $name, $index, array $item ): void {
	echo '<div class="statek-repeater-row statek-admin-nested" data-repeater-index="' . esc_attr( (string) $index ) . '">';
	statek_cholupice_core_render_field( $name . '[' . $index . '][title]', 'Nadpis položky', (string) ( $item['title'] ?? '' ) );
	statek_cholupice_core_render_field( $name . '[' . $index . '][text]', 'Text položky', (string) ( $item['text'] ?? '' ), array( 'type' => 'textarea', 'rows' => 3 ) );
	echo '<button type="button" class="button-link-delete statek-repeater-remove">Odebrat položku</button>';
	echo '</div>';
}

function statek_cholupice_core_render_topics_metabox(): void {
	$topics = statek_cholupice_core_theme_data( 'statek_cholupice_topic_sections' );
	foreach ( array_slice( $topics, 0, 3 ) as $index => $topic ) {
		echo '<div class="statek-admin-card">';
		echo '<h3>' . esc_html( (string) ( $topic['title'] ?? 'Sekce' ) ) . '</h3>';
		statek_cholupice_core_render_field( "statek_home_topics[$index][title]", 'Nadpis', (string) ( $topic['title'] ?? '' ) );
		statek_cholupice_core_render_field( "statek_home_topics[$index][motto]", 'Podnadpis', (string) ( $topic['motto'] ?? '' ) );
		statek_cholupice_core_render_field( "statek_home_topics[$index][intro]", 'Úvodní text', (string) ( $topic['intro'] ?? '' ), array( 'type' => 'textarea', 'rows' => 5 ) );
		statek_cholupice_core_render_paragraphs( "statek_home_topics[$index][details]", (array) ( $topic['details'] ?? array() ), 'Rozklikávací text', 8 );
		statek_cholupice_core_render_media_field( "statek_home_topics[$index][image]", (array) ( $topic['image'] ?? array() ), 'Ilustrační foto' );
		echo '</div>';
	}
}

function statek_cholupice_core_render_benefits_metabox(): void {
	$benefits = statek_cholupice_core_theme_data( 'statek_cholupice_benefits_data' );
	echo '<div class="statek-admin-card">';
	statek_cholupice_core_render_field( 'statek_home_benefits[heading]', 'Nadpis', (string) ( $benefits['heading'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_benefits[motto]', 'Podnadpis', (string) ( $benefits['motto'] ?? '' ), array( 'type' => 'textarea', 'rows' => 2 ) );
	statek_cholupice_core_render_field( 'statek_home_benefits[intro]', 'Úvodní text', (string) ( $benefits['intro'] ?? '' ), array( 'type' => 'textarea', 'rows' => 4 ) );
	echo '</div>';
	foreach ( array_slice( (array) ( $benefits['cards'] ?? array() ), 0, 6 ) as $index => $card ) {
		echo '<div class="statek-admin-card">';
		echo '<h3>Karta ' . esc_html( (string) ( $index + 1 ) ) . '</h3>';
		statek_cholupice_core_render_field( "statek_home_benefits[cards][$index][order]", 'Pořadí', (string) ( $card['order'] ?? ( $index + 1 ) ), array( 'type' => 'number', 'min' => 1, 'max' => 6, 'order' => true, 'input_class' => 'statek-order-input' ) );
		echo '<label class="statek-admin-field"><span>Ikona</span><select name="' . esc_attr( "statek_home_benefits[cards][$index][icon]" ) . '">';
		foreach ( statek_cholupice_core_icon_options() as $icon => $label ) {
			echo '<option value="' . esc_attr( $icon ) . '"' . selected( $icon, (string) ( $card['icon'] ?? 'work' ), false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></label>';
		statek_cholupice_core_render_field( "statek_home_benefits[cards][$index][title]", 'Nadpis karty', (string) ( $card['title'] ?? '' ) );
		statek_cholupice_core_render_field( "statek_home_benefits[cards][$index][text]", 'Text karty', (string) ( $card['text'] ?? '' ), array( 'type' => 'textarea', 'rows' => 5 ) );
		echo '</div>';
	}
}

function statek_cholupice_core_icon_options(): array {
	return array(
		'work'     => 'Práce / kufřík',
		'delivery' => 'Doprava / dodávka',
		'shop'     => 'Služby / obchod',
		'shield'   => 'Bezpečí / štít',
		'heritage' => 'Historie / památka',
		'heart'    => 'Dobrý soused / srdce',
	);
}

function statek_cholupice_core_render_faq_metabox(): void {
	$items = statek_cholupice_core_theme_data( 'statek_cholupice_faq_items' );
	echo '<p class="statek-admin-help">FAQ se už needituje jako JSON. Každá otázka má vlastní pole. Odpověď pište jako běžný text, odstavce oddělte prázdným řádkem. Maximum 12 položek.</p>';
	echo '<div class="statek-repeater" data-max="12"><div class="statek-repeater-items">';
	foreach ( array_slice( $items, 0, 12 ) as $index => $item ) {
		statek_cholupice_core_render_faq_row( (int) $index, (array) $item );
	}
	echo '</div><template>';
	statek_cholupice_core_render_faq_row( '__INDEX__', array() );
	echo '</template>';
	echo '<button type="button" class="button statek-repeater-add">Přidat otázku</button></div>';
}

function statek_cholupice_core_render_faq_row( $index, array $item ): void {
	echo '<div class="statek-admin-card statek-repeater-row statek-admin-nested" data-repeater-index="' . esc_attr( (string) $index ) . '">';
	statek_cholupice_core_render_field( "statek_home_faq_items[$index][order]", 'Pořadí', (string) ( $item['order'] ?? ( is_numeric( $index ) ? ( (int) $index + 1 ) : '' ) ), array( 'type' => 'number', 'min' => 1, 'max' => 12, 'order' => true, 'input_class' => 'statek-order-input' ) );
	statek_cholupice_core_render_field( "statek_home_faq_items[$index][question]", 'Otázka', (string) ( $item['question'] ?? '' ) );
	statek_cholupice_core_render_field( "statek_home_faq_items[$index][answer]", 'Odpověď', statek_cholupice_core_answer_to_edit_text( (string) ( $item['answer'] ?? '' ) ), array( 'type' => 'textarea', 'rows' => 6 ) );
	echo '<button type="button" class="button-link-delete statek-repeater-remove">Odebrat otázku</button>';
	echo '</div>';
}

function statek_cholupice_core_answer_to_edit_text( string $answer ): string {
	$answer = (string) preg_replace( "/\r\n?/", "\n", $answer );
	$answer = (string) preg_replace( '#</p>\s*<p\b[^>]*>#i', "\n\n", $answer );
	$answer = (string) preg_replace( '#<br\s*/?>#i', "\n", $answer );
	$answer = (string) preg_replace( '#</?p\b[^>]*>#i', '', $answer );
	$answer = html_entity_decode( wp_strip_all_tags( $answer ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) );
	$answer = (string) preg_replace( "/[ \t]*\n[ \t]*/", "\n", $answer );
	$answer = (string) preg_replace( "/\n{3,}/", "\n\n", $answer );
	return trim( $answer );
}

function statek_cholupice_core_render_contact_footer_metabox( WP_Post $post ): void {
	$footer = statek_cholupice_core_theme_data( 'statek_cholupice_footer_data' );
	echo '<div class="statek-admin-card">';
	echo '<h3>Kontaktní blok pod FAQ</h3>';
	statek_cholupice_core_render_field( 'statek_home_contact_heading', 'Nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_contact_heading', 'Máte další otázku k projektu?' ) );
	statek_cholupice_core_render_field( 'statek_home_contact_motto', 'Podnadpis', statek_cholupice_core_meta_value( $post, 'statek_home_contact_motto', 'Zajímá vás něco, co jsme nezodpověděli?' ) );
	statek_cholupice_core_render_field( 'statek_home_contact_text', 'Doplňující text', statek_cholupice_core_meta_value( $post, 'statek_home_contact_text', 'Napište nám prostřednictvím formuláře nebo přímo na <a href="mailto:info@statekcholupice.cz">info@statekcholupice.cz</a>. Vaše podněty nám pomohou průběžně doplňovat informace, které jsou pro Cholupice důležité.' ), array( 'type' => 'textarea', 'rows' => 4, 'description' => 'Může obsahovat jednoduchý odkaz.' ) );
	echo '</div><div class="statek-admin-card">';
	echo '<h3>Patička</h3>';
	statek_cholupice_core_render_field( 'statek_home_footer[investor_name]', 'Investor', (string) ( $footer['investor_name'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_footer[investor_address]', 'Adresa', (string) ( $footer['investor_address'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_footer[investor_id]', 'IČ a DIČ', (string) ( $footer['investor_id'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_footer[investor_registry]', 'Zápis v rejstříku', (string) ( $footer['investor_registry'] ?? '' ), array( 'type' => 'textarea', 'rows' => 2 ) );
	statek_cholupice_core_render_field( 'statek_home_footer[info_heading]', 'Nadpis aktuálnosti informací', (string) ( $footer['info_heading'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_footer[info_text]', 'Text aktuálnosti informací', (string) ( $footer['info_text'] ?? '' ), array( 'type' => 'textarea', 'rows' => 4 ) );
	statek_cholupice_core_render_field( 'statek_home_footer[visuals_heading]', 'Nadpis k vizualizacím', (string) ( $footer['visuals_heading'] ?? '' ) );
	statek_cholupice_core_render_field( 'statek_home_footer[visuals_text]', 'Text k vizualizacím', (string) ( $footer['visuals_text'] ?? '' ), array( 'type' => 'textarea', 'rows' => 4 ) );
	echo '</div>';
}

function statek_cholupice_core_save_home_metabox( int $post_id ): void {
	if ( ! isset( $_POST['statek_cholupice_home_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['statek_cholupice_home_nonce'] ) ), 'statek_cholupice_home_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || (int) $post_id !== (int) get_option( 'page_on_front' ) ) {
		return;
	}

	foreach ( statek_cholupice_core_scalar_save_fields() as $key => $field ) {
		if ( ! array_key_exists( $key, $_POST ) ) {
			continue;
		}
		$raw   = wp_unslash( $_POST[ $key ] );
		$type  = (string) ( $field['type'] ?? 'text' );
		$limit = statek_cholupice_core_field_limit( (string) ( $field['limit'] ?? '' ) );
		if ( 'image_id' === $type ) {
			$value = (string) statek_cholupice_core_clean_image_id( $raw );
		} elseif ( 'url' === $type ) {
			$value = statek_cholupice_core_sanitize_cta_url( $raw, statek_cholupice_core_cta_url_fallback( $key ) );
		} elseif ( 'html' === $type ) {
			$value = statek_cholupice_core_clean_html( $raw, $limit );
		} else {
			$value = statek_cholupice_core_clean_textarea( $raw, $limit );
		}
		statek_cholupice_core_update_or_delete_meta( $post_id, $key, $value );
	}

	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_project_blocks', statek_cholupice_core_clean_project_blocks( statek_cholupice_core_post_array( 'statek_home_project_blocks' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_area_items', statek_cholupice_core_clean_area_items( statek_cholupice_core_post_array( 'statek_home_area_items' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_operation', statek_cholupice_core_clean_operation( statek_cholupice_core_post_array( 'statek_home_operation' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_topics', statek_cholupice_core_clean_topics( statek_cholupice_core_post_array( 'statek_home_topics' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_benefits', statek_cholupice_core_clean_benefits( statek_cholupice_core_post_array( 'statek_home_benefits' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_faq_items', statek_cholupice_core_clean_faq_items( statek_cholupice_core_post_array( 'statek_home_faq_items' ) ) );
	statek_cholupice_core_save_json_meta( $post_id, 'statek_home_footer', statek_cholupice_core_clean_footer( statek_cholupice_core_post_array( 'statek_home_footer' ) ) );
}
add_action( 'save_post_page', 'statek_cholupice_core_save_home_metabox' );

function statek_cholupice_core_scalar_save_fields(): array {
	return array_filter(
		statek_cholupice_core_home_fields(),
		static fn( $field ) => 'json' !== ( $field['type'] ?? '' )
	);
}

function statek_cholupice_core_post_array( string $key ): array {
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return array();
	}
	return wp_unslash( $_POST[ $key ] );
}

function statek_cholupice_core_update_or_delete_meta( int $post_id, string $key, string $value ): void {
	$value = trim( $value );
	if ( '' === $value || ( 'statek_home_hero_image_id' === $key && '0' === $value ) ) {
		delete_post_meta( $post_id, $key );
		return;
	}
	update_post_meta( $post_id, $key, $value );
}

function statek_cholupice_core_save_json_meta( int $post_id, string $key, array $value ): void {
	if ( empty( $value ) ) {
		delete_post_meta( $post_id, $key );
		return;
	}

	$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( false === $json ) {
		return;
	}

	update_post_meta( $post_id, $key, wp_slash( $json ) );
}

function statek_cholupice_core_clean_textarea( $value, int $limit = 1200 ): string {
	$value = sanitize_textarea_field( (string) $value );
	return trim( statek_cholupice_core_unicode_substr( $value, $limit ) );
}

function statek_cholupice_core_clean_paragraphs( $value, int $max = 6, int $limit = 1200 ): array {
	$value      = is_array( $value ) ? $value : array();
	$paragraphs = array();
	foreach ( array_slice( $value, 0, $max ) as $paragraph ) {
		$paragraph = statek_cholupice_core_clean_textarea( $paragraph, $limit );
		if ( '' !== $paragraph ) {
			$paragraphs[] = $paragraph;
		}
	}
	return $paragraphs;
}

function statek_cholupice_core_clean_image( $value ): array {
	$value = is_array( $value ) ? $value : array();
	return array(
		'id'  => statek_cholupice_core_clean_image_id( $value['id'] ?? 0 ),
		'alt' => statek_cholupice_core_clean_textarea( $value['alt'] ?? '', statek_cholupice_core_field_limit( 'alt_text' ) ),
	);
}

function statek_cholupice_core_clean_project_blocks( array $raw ): array {
	$blocks = array();
	for ( $index = 0; $index < 2; $index++ ) {
		$item  = is_array( $raw[ $index ] ?? null ) ? $raw[ $index ] : array();
		$block = array(
			'title'      => statek_cholupice_core_clean_textarea( $item['title'] ?? '', statek_cholupice_core_field_limit( 'card_heading' ) ),
			'paragraphs' => statek_cholupice_core_clean_paragraphs( $item['paragraphs'] ?? array(), 5, statek_cholupice_core_field_limit( 'long_paragraph' ) ),
		);
		if ( 0 === $index ) {
			$block['image'] = statek_cholupice_core_clean_image( $item['image'] ?? array() );
		} else {
			$block['before'] = statek_cholupice_core_clean_image( $item['before'] ?? array() );
			$block['after']  = statek_cholupice_core_clean_image( $item['after'] ?? array() );
		}
		$blocks[] = $block;
	}
	return $blocks;
}

function statek_cholupice_core_clean_area_items( array $raw ): array {
	$items = array();
	foreach ( array_slice( $raw, 0, 6 ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$items[] = array(
			'order'      => statek_cholupice_core_normalize_order( $item['order'] ?? '', (int) $index, 6 ),
			'_position'  => (int) $index,
			'title'      => statek_cholupice_core_clean_textarea( $item['title'] ?? '', statek_cholupice_core_field_limit( 'card_heading' ) ),
			'paragraphs' => statek_cholupice_core_clean_paragraphs( $item['paragraphs'] ?? array(), 3, statek_cholupice_core_field_limit( 'long_paragraph' ) ),
			'image'      => statek_cholupice_core_clean_image( $item['image'] ?? array() ),
		);
	}
	return statek_cholupice_core_stable_order_sort( $items );
}

function statek_cholupice_core_clean_list_items( $raw, int $max = 6 ): array {
	$items = array();
	$raw   = is_array( $raw ) ? $raw : array();
	foreach ( array_slice( $raw, 0, $max ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = statek_cholupice_core_clean_textarea( $item['title'] ?? '', statek_cholupice_core_field_limit( 'card_heading' ) );
		$text  = statek_cholupice_core_clean_textarea( $item['text'] ?? '', statek_cholupice_core_field_limit( 'list_text' ) );
		if ( '' !== $title && '' !== $text ) {
			$items[] = compact( 'title', 'text' );
		}
	}
	return $items;
}

function statek_cholupice_core_clean_operation( array $raw ): array {
	return array(
		'heading' => statek_cholupice_core_clean_textarea( $raw['heading'] ?? '', statek_cholupice_core_field_limit( 'section_heading' ) ),
		'motto'   => statek_cholupice_core_clean_textarea( $raw['motto'] ?? '', statek_cholupice_core_field_limit( 'motto' ) ),
		'intro'   => statek_cholupice_core_clean_paragraphs( $raw['intro'] ?? array(), 4, statek_cholupice_core_field_limit( 'long_paragraph' ) ),
		'include' => statek_cholupice_core_clean_list_items( $raw['include'] ?? array(), 6 ),
		'exclude' => statek_cholupice_core_clean_list_items( $raw['exclude'] ?? array(), 6 ),
	);
}

function statek_cholupice_core_clean_topics( array $raw ): array {
	$topics = array();
	foreach ( array_slice( $raw, 0, 3 ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$topics[] = array(
			'title'   => statek_cholupice_core_clean_textarea( $item['title'] ?? '', statek_cholupice_core_field_limit( 'section_heading' ) ),
			'motto'   => statek_cholupice_core_clean_textarea( $item['motto'] ?? '', statek_cholupice_core_field_limit( 'motto' ) ),
			'intro'   => statek_cholupice_core_clean_textarea( $item['intro'] ?? '', statek_cholupice_core_field_limit( 'long_paragraph' ) ),
			'details' => statek_cholupice_core_clean_paragraphs( $item['details'] ?? array(), 8, statek_cholupice_core_field_limit( 'long_paragraph' ) ),
			'image'   => statek_cholupice_core_clean_image( $item['image'] ?? array() ),
		);
	}
	return $topics;
}

function statek_cholupice_core_clean_benefits( array $raw ): array {
	$cards = array();
	foreach ( array_slice( (array) ( $raw['cards'] ?? array() ), 0, 6 ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = statek_cholupice_core_clean_textarea( $item['title'] ?? '', statek_cholupice_core_field_limit( 'card_heading' ) );
		$text  = statek_cholupice_core_clean_textarea( $item['text'] ?? '', statek_cholupice_core_field_limit( 'benefit_text' ) );
		if ( '' === $title || '' === $text ) {
			continue;
		}
		$icon    = sanitize_key( $item['icon'] ?? 'work' );
		$cards[] = array(
			'order'     => statek_cholupice_core_normalize_order( $item['order'] ?? '', (int) $index, 6 ),
			'_position' => (int) $index,
			'icon'      => array_key_exists( $icon, statek_cholupice_core_icon_options() ) ? $icon : 'work',
			'title'     => $title,
			'text'      => $text,
		);
	}
	return array(
		'heading' => statek_cholupice_core_clean_textarea( $raw['heading'] ?? '', statek_cholupice_core_field_limit( 'section_heading' ) ),
		'motto'   => statek_cholupice_core_clean_textarea( $raw['motto'] ?? '', statek_cholupice_core_field_limit( 'motto' ) ),
		'intro'   => statek_cholupice_core_clean_textarea( $raw['intro'] ?? '', statek_cholupice_core_field_limit( 'short_intro' ) ),
		'cards'   => statek_cholupice_core_stable_order_sort( $cards ),
	);
}

function statek_cholupice_core_clean_faq_items( array $raw ): array {
	$items = array();
	foreach ( array_slice( $raw, 0, 12 ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$question = statek_cholupice_core_clean_textarea( $item['question'] ?? '', statek_cholupice_core_field_limit( 'faq_question' ) );
		$answer   = trim( (string) ( $item['answer'] ?? '' ) );
		if ( '' === $question || '' === $answer ) {
			continue;
		}
		$answer  = wpautop( esc_html( statek_cholupice_core_clean_textarea( $answer, statek_cholupice_core_field_limit( 'faq_answer' ) ) ) );
		$items[] = array(
			'order'     => statek_cholupice_core_normalize_order( $item['order'] ?? '', (int) $index, 12 ),
			'_position' => (int) $index,
			'question'  => $question,
			'answer'    => wp_kses_post( $answer ),
		);
	}
	return statek_cholupice_core_stable_order_sort( $items );
}

function statek_cholupice_core_clean_footer( array $raw ): array {
	$keys  = array( 'investor_name', 'investor_address', 'investor_id', 'investor_registry', 'info_heading', 'info_text', 'visuals_heading', 'visuals_text' );
	$clean = array();
	foreach ( $keys as $key ) {
		$limit_key     = in_array( $key, array( 'info_text', 'visuals_text' ), true ) ? 'footer_text' : ( str_ends_with( $key, '_heading' ) ? 'section_heading' : 'footer_short' );
		$clean[ $key ] = statek_cholupice_core_clean_textarea( $raw[ $key ] ?? '', statek_cholupice_core_field_limit( $limit_key ) );
	}
	return $clean;
}
