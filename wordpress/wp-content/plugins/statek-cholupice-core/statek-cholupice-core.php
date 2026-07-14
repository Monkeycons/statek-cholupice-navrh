<?php
/**
 * Plugin Name: Statek Cholupice Core
 * Description: Projektové funkce pro web Statek Cholupice.
 * Version: 1.1.0-rc.3
 * Author: Monkey Consulting
 * Text Domain: statek-cholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STATEK_CHOLUPICE_CORE_VERSION', '1.1.0-rc.3' );

require_once __DIR__ . '/includes/contact.php';
require_once __DIR__ . '/includes/setup.php';
require_once __DIR__ . '/includes/homepage-admin.php';
