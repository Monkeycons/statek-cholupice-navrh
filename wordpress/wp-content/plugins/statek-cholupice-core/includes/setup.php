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
}
register_activation_hook( dirname( __DIR__ ) . '/statek-cholupice-core.php', 'statek_cholupice_core_activate' );
