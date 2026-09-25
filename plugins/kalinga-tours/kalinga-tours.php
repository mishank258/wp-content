<?php
/**
 * Plugin Name: Kalinga Tours
 * Description: Tours, destinations and enquiries for Kalinga Travels.
 * Version:     1.0.0
 * Author:      Ishank Malhotra
 * Requires PHP: 8.0
 * Text Domain: kalinga-tours
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constants used throughout the plugin
define('KALINGA_TOURS_VERSION', '1.0.0');
define('KALINGA_TOURS_DB_VERSION', '1.0');
define('KALINGA_TOURS_PATH', plugin_dir_path(__FILE__));
define('KALINGA_TOURS_URL', plugin_dir_url(__FILE__));

// Load the plugin's parts
require_once KALINGA_TOURS_PATH . 'includes/install.php';
require_once KALINGA_TOURS_PATH . 'includes/helpers.php';
require_once KALINGA_TOURS_PATH . 'includes/post-types.php';
require_once KALINGA_TOURS_PATH . 'includes/meta-boxes.php';
require_once KALINGA_TOURS_PATH . 'includes/shortcodes.php';
require_once KALINGA_TOURS_PATH . 'includes/assets.php';
require_once KALINGA_TOURS_PATH . 'includes/ajax.php';
require_once KALINGA_TOURS_PATH . 'includes/enquiries.php';

// Admin-only code: only loaded in the dashboard
if (is_admin()) {
    require_once KALINGA_TOURS_PATH . 'includes/admin-enquiries.php';
}

// Runs once, when the plugin is activated
function kalinga_tours_activate() {
    kalinga_tours_create_tables();
    kalinga_tours_register_post_type();
    kalinga_tours_register_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'kalinga_tours_activate');
