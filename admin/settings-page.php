<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Settings Page for Advanced Link Checker Plugin
 *
 * This file is responsible for creating and managing the settings page for the Advanced Link Checker plugin.
 * It includes functions to register the settings, display the settings form, and handle form submissions.
 */

// Include necessary files
require_once ALC_PLUGIN_PATH . 'includes/db-manager.php';

/**
 * Register the settings page for the Advanced Link Checker plugin.
 */
function alc_register_settings_menu() {
    add_options_page(
        __('Advanced Link Checker Settings', 'advanced-link-checker'), // Page title
        __('Link Checker Settings', 'advanced-link-checker'), // Menu title
        'manage_options', // Capability
        'alc-settings', // Menu slug
        'alc_settings_page_html' // Function to display the settings page
    );
}

add_action('admin_menu', 'alc_register_settings_menu');

/**
 * Register settings, sections, and fields for the Advanced Link Checker plugin.
 */
function alc_register_settings() {
    // Register a new setting for the plugin options
    register_setting('alc_options_group', 'alc_options', 'alc_options_validate');

    // Add a new section to the settings page
    add_settings_section(
        'alc_general_settings', // ID
        __('General Settings', 'advanced-link-checker'), // Title
        'alc_general_settings_section_text', // Callback function to display the section description
        'alc-settings' // Page on which to add the section
    );

    // Add a new field to the section
    add_settings_field(
        'alc_scan_frequency', // ID
        __('Scan Frequency', 'advanced-link-checker'), // Title
        'alc_scan_frequency_setting_html', // Callback function to display the field
        'alc-settings', // Page
        'alc_general_settings' // Section
    );

    // More fields can be added here
}

add_action('admin_init', 'alc_register_settings');

/**
 * Display the section text for the general settings.
 */
function alc_general_settings_section_text() {
    echo '<p>' . __('Configure the general settings for the Advanced Link Checker.', 'advanced-link-checker') . '</p>';
}

/**
 * HTML for the scan frequency setting.
 */
function alc_scan_frequency_setting_html() {
    $options = get_option('alc_options');
    echo "<input id='alc_scan_frequency' name='alc_options[scan_frequency]' size='40' type='text' value='{$options['scan_frequency']}' />";
}

/**
 * Validate and sanitize settings input before saving to database.
 */
function alc_options_validate($input) {
    // Validate and sanitize each setting field here
    $new_input['scan_frequency'] = absint($input['scan_frequency']);

    // Return the sanitized settings
    return $new_input;
}

/**
 * Display the settings page HTML.
 */
function alc_settings_page_html() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'advanced-link-checker'));
    }
    ?>
    <div class="wrap">
        <h2><?php echo __('Advanced Link Checker Settings', 'advanced-link-checker'); ?></h2>
        <form action="options.php" method="post">
            <?php settings_fields('alc_options_group'); ?>
            <?php do_settings_sections('alc-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
