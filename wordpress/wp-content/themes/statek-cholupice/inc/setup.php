<?php
/**
 * Základní nastavení šablony.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_setup(): void {
	load_theme_textdomain( 'statek-cholupice', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_editor_style( 'assets/css/main.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Hlavní menu', 'statek-cholupice' ),
		)
	);
}
add_action( 'after_setup_theme', 'statek_cholupice_setup' );
