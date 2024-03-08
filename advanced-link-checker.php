<?php
/**
 * Plugin Name: Advanced Link Checker
 * Plugin URI: http://yourwebsite.com/advanced-link-checker
 * Description: A comprehensive, efficient, and user-friendly WordPress plugin that detects, manages, and resolves broken links on a website.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: http://yourwebsite.com
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin paths and URLs
define('ALC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include necessary files
require_once ALC_PLUGIN_PATH . 'config.php';
require_once ALC_PLUGIN_PATH . 'helpers.php';
require_once ALC_PLUGIN_PATH . 'includes/scanner.php';
require_once ALC_PLUGIN_PATH . 'includes/db-manager.php';
require_once ALC_PLUGIN_PATH . 'admin/admin-page.php';
require_once ALC_PLUGIN_PATH . 'admin/link-table.php';
require_once ALC_PLUGIN_PATH . 'admin/settings-page.php';
require_once ALC_PLUGIN_PATH . 'admin/ajax-handlers.php';
require_once ALC_PLUGIN_PATH . 'includes/link-rechecker.php';
require_once ALC_PLUGIN_PATH . 'includes/email-notifications.php';
require_once ALC_PLUGIN_PATH . 'public/front-end-highlighting.php';
require_once ALC_PLUGIN_PATH . 'includes/reporting.php';
require_once ALC_PLUGIN_PATH . 'includes/security.php';
require_once ALC_PLUGIN_PATH . 'includes/performance.php';

// Activation hook
register_activation_hook(__FILE__, 'alc_activate_plugin');
function alc_activate_plugin() {
    // Initialize plugin settings and database tables
    include_once ALC_PLUGIN_PATH . 'includes/db-manager.php';
    ALC_DB_Manager::create_tables();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'alc_deactivate_plugin');
function alc_deactivate_plugin() {
    // Clean up tasks, like scheduled events
    wp_clear_scheduled_hook('alc_scheduled_link_check');
}

// Initialize the plugin
add_action('plugins_loaded', 'alc_initialize_plugin');
function alc_initialize_plugin() {
    // Load text domain for internationalization
    load_plugin_textdomain('advanced-link-checker', false, dirname(ALC_PLUGIN_BASENAME) . '/languages/');

    // Initialize admin pages if in the admin area
    if (is_admin()) {
        new ALC_Admin_Page();
        new ALC_Settings_Page();
    }

    // Schedule link checks
    if (!wp_next_scheduled('alc_scheduled_link_check')) {
        wp_schedule_event(time(), 'hourly', 'alc_scheduled_link_check');
    }

    // Hook into scheduled event
    add_action('alc_scheduled_link_check', 'ALC_Scanner::scan_links');
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'alc_enqueue_scripts');
function alc_enqueue_scripts() {
    if (!is_admin()) {
        wp_enqueue_style('alc-frontend-style', ALC_PLUGIN_URL . 'public/css/frontend-style.css');
        wp_enqueue_script('alc-frontend-script', ALC_PLUGIN_URL . 'public/js/frontend-script.js', array('jquery'), '', true);
    }
}

// Register shortcode for front-end link highlighting
add_shortcode('alc_highlight_broken_links', 'alc_highlight_broken_links_shortcode');
function alc_highlight_broken_links_shortcode($atts) {
    // Shortcode logic here
    return ALC_Front_End_Highlighting::highlight_links($atts);
}

?>
