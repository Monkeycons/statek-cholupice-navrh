<?php
/**
 * Plugin Name: Statek Cholupice Core
 * Description: Projektové funkce pro web Statek Cholupice.
 * Version: 1.0.0
 * Author: Monkey Consulting
 * Text Domain: statek-cholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STATEK_CHOLUPICE_CORE_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/contact.php';
require_once __DIR__ . '/includes/setup.php';
require_once __DIR__ . '/includes/homepage-admin.php';
