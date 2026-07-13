<?php
/**
 * Nativní editace obsahu homepage bez ACF.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_home_fields(): array {
	return array(
		'statek_home_hero_kicker'     => array( 'type' => 'text' ),
		'statek_home_hero_title'      => array( 'type' => 'text' ),
		'statek_home_hero_text'       => array( 'type' => 'text' ),
		'statek_home_hero_primary'    => array( 'type' => 'text' ),
		'statek_home_hero_secondary'  => array( 'type' => 'text' ),
		'statek_home_faq_heading'     => array( 'type' => 'text' ),
		'statek_home_faq_motto'       => array( 'type' => 'text' ),
		'statek_home_faq_intro_1'     => array( 'type' => 'text' ),
		'statek_home_faq_intro_2'     => array( 'type' => 'html' ),
		'statek_home_contact_heading' => array( 'type' => 'text' ),
		'statek_home_contact_motto'   => array( 'type' => 'text' ),
		'statek_home_contact_text'    => array( 'type' => 'html' ),
		'statek_home_area_heading'    => array( 'type' => 'text' ),
		'statek_home_area_motto'      => array( 'type' => 'text' ),
		'statek_home_project_blocks'  => array( 'type' => 'json' ),
		'statek_home_area_items'      => array( 'type' => 'json' ),
		'statek_home_operation'       => array( 'type' => 'json' ),
		'statek_home_topics'          => array( 'type' => 'json' ),
		'statek_home_benefits'        => array( 'type' => 'json' ),
		'statek_home_faq_items'       => array( 'type' => 'json' ),
		'statek_home_footer'          => array( 'type' => 'json' ),
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
		return wp_kses_post( $value );
	}
	return sanitize_textarea_field( $value );
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

	$base_url = plugin_dir_url( dirname( __DIR__ ) . '/statek-cholupice-core.php' );
	wp_enqueue_media();
	wp_enqueue_style( 'statek-cholupice-home-admin', $base_url . 'assets/admin-homepage.css', array(), STATEK_CHOLUPICE_CORE_VERSION );
	wp_enqueue_script( 'statek-cholupice-home-admin', $base_url . 'assets/admin-homepage.js', array(), STATEK_CHOLUPICE_CORE_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'statek_cholupice_core_enqueue_home_admin' );

function statek_cholupice_core_theme_data( string $function, array $fallback = array() ): array {
	return function_exists( $function ) ? (array) call_user_func( $function ) : $fallback;
}

function statek_cholupice_core_meta_value( WP_Post $post, string $key, string $fallback = '' ): string {
	$value = (string) get_post_meta( $post->ID, $key, true );
	return '' !== trim( $value ) ? $value : $fallback;
}

function statek_cholupice_core_render_field( string $name, string $label, string $value, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'type'        => 'text',
			'rows'        => 3,
			'description' => '',
			'class'       => '',
		)
	);
	$id = sanitize_key( str_replace( array( '[', ']' ), '_', $name ) );
	echo '<label class="statek-admin-field ' . esc_attr( $args['class'] ) . '" for="' . esc_attr( $id ) . '">';
	echo '<span>' . esc_html( $label ) . '</span>';
	if ( 'textarea' === $args['type'] ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( (string) $args['rows'] ) . '">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="text" value="' . esc_attr( $value ) . '">';
	}
	if ( $args['description'] ) {
		echo '<small>' . esc_html( $args['description'] ) . '</small>';
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
	echo '<small>Doporučení: kratší odstavce drží layout přehledný. Maximum ' . esc_html( (string) $max ) . '.</small>';
	echo '</div>';
}

function statek_cholupice_core_render_paragraph_row( string $name, $index, string $value ): void {
	echo '<div class="statek-repeater-row statek-admin-inline-row">';
	echo '<textarea name="' . esc_attr( $name . '[' . $index . ']' ) . '" rows="3">' . esc_textarea( $value ) . '</textarea>';
	echo '<button type="button" class="button-link-delete statek-repeater-remove">Odebrat</button>';
	echo '</div>';
}

function statek_cholupice_core_render_media_field( string $name, array $image, string $label ): void {
	$id      = absint( $image['id'] ?? 0 );
	$alt     = (string) ( $image['alt'] ?? '' );
	$preview = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
	echo '<div class="statek-admin-field statek-media-field">';
	echo '<span>' . esc_html( $label ) . '</span>';
	echo '<input class="statek-media-id" name="' . esc_attr( $name . '[id]' ) . '" type="hidden" value="' . esc_attr( (string) $id ) . '">';
	echo '<div class="statek-media-preview">';
	if ( $preview ) {
		echo '<img src="' . esc_url( $preview ) . '" alt="">';
	} else {
		echo '<em>Je použit schválený výchozí obrázek šablony.</em>';
	}
	echo '</div>';
	echo '<p><button type="button" class="button statek-media-select">Vybrat obrázek</button> <button type="button" class="button-link-delete statek-media-clear">Odebrat vybraný obrázek</button></p>';
	statek_cholupice_core_render_field( $name . '[alt]', 'Alt text obrázku', $alt, array( 'description' => 'Krátce popište, co je na obrázku. Pokud necháte prázdné, použije se schválený fallback.' ) );
	echo '</div>';
}

function statek_cholupice_core_render_home_hero_metabox( WP_Post $post ): void {
	wp_nonce_field( 'statek_cholupice_home_save', 'statek_cholupice_home_nonce' );
	echo '<p class="statek-admin-help">Prázdné pole ponechá schválený výchozí obsah. Tato část upravuje pouze texty, nikoli vzhled hero sekce.</p>';
	echo '<div class="statek-admin-grid">';
	statek_cholupice_core_render_field( 'statek_home_hero_kicker', 'Hero - malý nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_kicker', 'Revitalizace brownfieldu' ), array( 'description' => 'Doporučeně do 40 znaků.' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_title', 'Hero - hlavní nadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_title', 'Nový život pro Statek Cholupice' ), array( 'description' => 'Doporučeně do 60 znaků.' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_text', 'Hero - podnadpis', statek_cholupice_core_meta_value( $post, 'statek_home_hero_text', 'Citlivá přestavba historického areálu propojí bydlení, služby pro obyvatele, moderní výrobu a respekt k místu.' ), array( 'type' => 'textarea', 'rows' => 3 ) );
	statek_cholupice_core_render_field( 'statek_home_hero_primary', 'Hero - první tlačítko', statek_cholupice_core_meta_value( $post, 'statek_home_hero_primary', 'Poznat projekt' ) );
	statek_cholupice_core_render_field( 'statek_home_hero_secondary', 'Hero - druhé tlačítko', statek_cholupice_core_meta_value( $post, 'statek_home_hero_secondary', 'Dobrý soused' ) );
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
		statek_cholupice_core_render_field( "statek_home_area_items[$index][order]", 'Pořadí', (string) ( $item['order'] ?? ( $index + 1 ) ), array( 'description' => 'Číslo 1 až 6.' ) );
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
	echo '<div class="statek-repeater-row statek-admin-nested">';
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
		statek_cholupice_core_render_field( "statek_home_benefits[cards][$index][order]", 'Pořadí', (string) ( $card['order'] ?? ( $index + 1 ) ) );
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
	echo '<div class="statek-admin-card statek-repeater-row statek-admin-nested">';
	statek_cholupice_core_render_field( "statek_home_faq_items[$index][order]", 'Pořadí', (string) ( $item['order'] ?? ( is_numeric( $index ) ? ( (int) $index + 1 ) : '' ) ) );
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

	foreach ( statek_cholupice_core_scalar_save_fields() as $key => $type ) {
		if ( ! array_key_exists( $key, $_POST ) ) {
			continue;
		}
		$value = 'html' === $type ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : statek_cholupice_core_clean_textarea( wp_unslash( $_POST[ $key ] ), 1600 );
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
	return array(
		'statek_home_hero_kicker'     => 'text',
		'statek_home_hero_title'      => 'text',
		'statek_home_hero_text'       => 'text',
		'statek_home_hero_primary'    => 'text',
		'statek_home_hero_secondary'  => 'text',
		'statek_home_faq_heading'     => 'text',
		'statek_home_faq_motto'       => 'text',
		'statek_home_faq_intro_1'     => 'text',
		'statek_home_faq_intro_2'     => 'html',
		'statek_home_contact_heading' => 'text',
		'statek_home_contact_motto'   => 'text',
		'statek_home_contact_text'    => 'html',
		'statek_home_area_heading'    => 'text',
		'statek_home_area_motto'      => 'text',
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
	if ( '' === $value ) {
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
	if ( function_exists( 'mb_substr' ) ) {
		return trim( mb_substr( $value, 0, $limit ) );
	}
	return trim( substr( $value, 0, $limit ) );
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
		'id'  => absint( $value['id'] ?? 0 ),
		'alt' => statek_cholupice_core_clean_textarea( $value['alt'] ?? '', 180 ),
	);
}

function statek_cholupice_core_clean_project_blocks( array $raw ): array {
	$blocks = array();
	for ( $index = 0; $index < 2; $index++ ) {
		$item  = is_array( $raw[ $index ] ?? null ) ? $raw[ $index ] : array();
		$block = array(
			'title'      => statek_cholupice_core_clean_textarea( $item['title'] ?? '', 180 ),
			'paragraphs' => statek_cholupice_core_clean_paragraphs( $item['paragraphs'] ?? array(), 5, 1400 ),
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
			'order'      => max( 1, min( 6, absint( $item['order'] ?? ( $index + 1 ) ) ) ),
			'title'      => statek_cholupice_core_clean_textarea( $item['title'] ?? '', 120 ),
			'paragraphs' => statek_cholupice_core_clean_paragraphs( $item['paragraphs'] ?? array(), 3, 1200 ),
			'image'      => statek_cholupice_core_clean_image( $item['image'] ?? array() ),
		);
	}
	return $items;
}

function statek_cholupice_core_clean_list_items( $raw, int $max = 6 ): array {
	$items = array();
	$raw   = is_array( $raw ) ? $raw : array();
	foreach ( array_slice( $raw, 0, $max ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = statek_cholupice_core_clean_textarea( $item['title'] ?? '', 140 );
		$text  = statek_cholupice_core_clean_textarea( $item['text'] ?? '', 700 );
		if ( '' !== $title && '' !== $text ) {
			$items[] = compact( 'title', 'text' );
		}
	}
	return $items;
}

function statek_cholupice_core_clean_operation( array $raw ): array {
	return array(
		'heading' => statek_cholupice_core_clean_textarea( $raw['heading'] ?? '', 120 ),
		'motto'   => statek_cholupice_core_clean_textarea( $raw['motto'] ?? '', 160 ),
		'intro'   => statek_cholupice_core_clean_paragraphs( $raw['intro'] ?? array(), 4, 1200 ),
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
			'title'   => statek_cholupice_core_clean_textarea( $item['title'] ?? '', 120 ),
			'motto'   => statek_cholupice_core_clean_textarea( $item['motto'] ?? '', 180 ),
			'intro'   => statek_cholupice_core_clean_textarea( $item['intro'] ?? '', 1400 ),
			'details' => statek_cholupice_core_clean_paragraphs( $item['details'] ?? array(), 8, 1400 ),
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
		$title = statek_cholupice_core_clean_textarea( $item['title'] ?? '', 120 );
		$text  = statek_cholupice_core_clean_textarea( $item['text'] ?? '', 1000 );
		if ( '' === $title || '' === $text ) {
			continue;
		}
		$icon    = sanitize_key( $item['icon'] ?? 'work' );
		$cards[] = array(
			'order' => max( 1, min( 6, absint( $item['order'] ?? ( $index + 1 ) ) ) ),
			'icon'  => array_key_exists( $icon, statek_cholupice_core_icon_options() ) ? $icon : 'work',
			'title' => $title,
			'text'  => $text,
		);
	}
	return array(
		'heading' => statek_cholupice_core_clean_textarea( $raw['heading'] ?? '', 120 ),
		'motto'   => statek_cholupice_core_clean_textarea( $raw['motto'] ?? '', 220 ),
		'intro'   => statek_cholupice_core_clean_textarea( $raw['intro'] ?? '', 900 ),
		'cards'   => $cards,
	);
}

function statek_cholupice_core_clean_faq_items( array $raw ): array {
	$items = array();
	foreach ( array_slice( $raw, 0, 12 ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$question = statek_cholupice_core_clean_textarea( $item['question'] ?? '', 220 );
		$answer   = trim( (string) ( $item['answer'] ?? '' ) );
		if ( '' === $question || '' === $answer ) {
			continue;
		}
		$answer  = wpautop( esc_html( statek_cholupice_core_clean_textarea( $answer, 2200 ) ) );
		$items[] = array(
			'order'    => absint( $item['order'] ?? ( $index + 1 ) ),
			'question' => $question,
			'answer'   => wp_kses_post( $answer ),
		);
	}
	usort( $items, static fn( $a, $b ) => ( $a['order'] <=> $b['order'] ) );
	return array_map(
		static fn( $item ) => array(
			'question' => $item['question'],
			'answer'   => $item['answer'],
		),
		$items
	);
}

function statek_cholupice_core_clean_footer( array $raw ): array {
	$keys  = array( 'investor_name', 'investor_address', 'investor_id', 'investor_registry', 'info_heading', 'info_text', 'visuals_heading', 'visuals_text' );
	$clean = array();
	foreach ( $keys as $key ) {
		$clean[ $key ] = statek_cholupice_core_clean_textarea( $raw[ $key ] ?? '', 900 );
	}
	return $clean;
}
