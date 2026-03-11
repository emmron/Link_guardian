<?php
/**
 * Plugin Name: Advanced Link Checker
 * Plugin URI: https://github.com/emmron/Link_guardian
 * Description: A comprehensive, efficient, and user-friendly WordPress plugin that detects, manages, and resolves broken links on a website.
 * Version: 1.0.0
 * Author: Link Guardian
 * Author URI: https://github.com/emmron/Link_guardian
 * License: GPL2
 * Text Domain: advanced-link-checker
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
require_once ALC_PLUGIN_PATH . 'includes/db-manager.php';
require_once ALC_PLUGIN_PATH . 'includes/scanner.php';
require_once ALC_PLUGIN_PATH . 'includes/link-rechecker.php';
require_once ALC_PLUGIN_PATH . 'includes/email-notifications.php';
require_once ALC_PLUGIN_PATH . 'includes/reporting.php';
require_once ALC_PLUGIN_PATH . 'includes/security.php';
require_once ALC_PLUGIN_PATH . 'includes/performance.php';
require_once ALC_PLUGIN_PATH . 'public/front-end-highlighting.php';

// Admin-only includes
if (is_admin()) {
    require_once ALC_PLUGIN_PATH . 'admin/admin-page.php';
    require_once ALC_PLUGIN_PATH . 'admin/link-table.php';
    require_once ALC_PLUGIN_PATH . 'admin/settings-page.php';
    require_once ALC_PLUGIN_PATH . 'admin/ajax-handlers.php';
}

// Activation hook
register_activation_hook(__FILE__, 'alc_activate_plugin');
function alc_activate_plugin() {
    alc_install_db();

    // Set default options
    if (!get_option('alc_options')) {
        update_option('alc_options', array(
            'scan_frequency'       => ALC_SCAN_FREQUENCY,
            'links_per_scan'       => ALC_LINKS_PER_SCAN,
            'notify_email'         => ALC_NOTIFY_EMAIL,
            'notify_recipients'    => get_option('admin_email'),
            'notify_frequency'     => ALC_NOTIFY_FREQUENCY,
            'notify_threshold'     => ALC_NOTIFY_THRESHOLD,
            'highlight_enable'     => ALC_HIGHLIGHT_ENABLE,
            'highlight_color'      => '#ff0000',
            'highlight_style'      => 'wavy',
            'excluded_urls'        => '',
        ));
    }

    // Schedule scanning
    if (!wp_next_scheduled('alc_scheduled_link_check')) {
        wp_schedule_event(time(), 'hourly', 'alc_scheduled_link_check');
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'alc_deactivate_plugin');
function alc_deactivate_plugin() {
    wp_clear_scheduled_hook('alc_scheduled_link_check');
    wp_clear_scheduled_hook('alc_scheduled_scan');
    wp_clear_scheduled_hook('alc_send_email_notifications');
}

// Initialize the plugin
add_action('plugins_loaded', 'alc_initialize_plugin');
function alc_initialize_plugin() {
    load_plugin_textdomain('advanced-link-checker', false, dirname(ALC_PLUGIN_BASENAME) . '/languages/');

    // Schedule link checks if not already scheduled
    if (!wp_next_scheduled('alc_scheduled_link_check')) {
        wp_schedule_event(time(), 'hourly', 'alc_scheduled_link_check');
    }

    add_action('alc_scheduled_link_check', 'alc_perform_scan');
}

// Enqueue frontend scripts and styles
add_action('wp_enqueue_scripts', 'alc_enqueue_scripts');
function alc_enqueue_scripts() {
    if (!is_admin()) {
        $options = get_option('alc_options', array());
        $highlight_enabled = isset($options['highlight_enable']) ? $options['highlight_enable'] : true;

        if ($highlight_enabled) {
            wp_enqueue_style('alc-frontend-style', ALC_PLUGIN_URL . 'public/css/frontend-style.css', array(), ALC_VERSION);
            wp_enqueue_script('alc-frontend-script', ALC_PLUGIN_URL . 'public/js/frontend-script.js', array('jquery'), ALC_VERSION, true);
        }
    }
}

// Register shortcode for front-end link highlighting
add_shortcode('alc_highlight_broken_links', 'alc_highlight_broken_links_shortcode');
function alc_highlight_broken_links_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
    ), $atts, 'alc_highlight_broken_links');

    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $broken_links = $wpdb->get_results($wpdb->prepare(
        "SELECT url, status_code FROM $table_name WHERE post_id = %d",
        intval($atts['post_id'])
    ));

    if (empty($broken_links)) {
        return '<p>' . __('No broken links found for this post.', 'advanced-link-checker') . '</p>';
    }

    $output = '<ul class="alc-broken-links-list">';
    foreach ($broken_links as $link) {
        $output .= sprintf(
            '<li><a href="%s" class="alc-broken-link" target="_blank">%s</a> (Status: %s)</li>',
            esc_url($link->url),
            esc_html($link->url),
            esc_html($link->status_code)
        );
    }
    $output .= '</ul>';

    return $output;
}
