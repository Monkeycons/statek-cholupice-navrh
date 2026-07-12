<?php
/**
 * Jednoduchá idempotentní inicializace obsahu.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_activate(): void {
	$page = get_page_by_path( 'domovska-stranka' );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Domovská stránka',
				'post_name'    => 'domovska-stranka',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => 'Obsah domovské stránky spravuje šablona Statek Cholupice.',
			)
		);
	} else {
		$page_id = (int) $page->ID;
	}

	if ( $page_id && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
	}

	statek_cholupice_core_ensure_primary_menu();
}
register_activation_hook( dirname( __DIR__ ) . '/statek-cholupice-core.php', 'statek_cholupice_core_activate' );

function statek_cholupice_core_ensure_primary_menu(): void {
	$menu_name = 'Statek Cholupice - hlavní menu';
	$menu      = wp_get_nav_menu_object( $menu_name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		$items = array(
			'O projektu'          => '#projekt',
			'Bezpečnost'         => '#bezpecnost',
			'Doprava'            => '#doprava',
			'Životní prostředí'  => '#zivotni-prostredi',
			'Přínosy'            => '#prinosy',
			'Časté dotazy'       => '#kontakt',
			'Kontakt'            => '#faq-contact-form',
		);
		foreach ( $items as $title => $url ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $title,
					'menu-item-url'    => home_url( '/' . $url ),
					'menu-item-status' => 'publish',
				)
			);
		}
	} else {
		$menu_id = (int) $menu->term_id;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
