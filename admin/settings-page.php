<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Settings Page for Advanced Link Checker Plugin
 *
 * This file is responsible for creating and managing the settings page for the Advanced Link Checker plugin.
 * It includes functions to register the settings, display the settings form, and handle form submissions.
 */

/**
 * Register the settings page for the Advanced Link Checker plugin.
 */
function alc_register_settings_menu() {
    add_options_page(
        __('Advanced Link Checker Settings', 'advanced-link-checker'),
        __('Link Checker Settings', 'advanced-link-checker'),
        'manage_options',
        'alc-settings',
        'alc_settings_page_html'
    );
}

add_action('admin_menu', 'alc_register_settings_menu');

/**
 * Register settings, sections, and fields for the Advanced Link Checker plugin.
 */
function alc_register_settings() {
    register_setting('alc_options_group', 'alc_options', 'alc_options_validate');

    // General Settings Section
    add_settings_section(
        'alc_general_settings',
        __('Scanning Settings', 'advanced-link-checker'),
        'alc_general_settings_section_text',
        'alc-settings'
    );

    add_settings_field(
        'alc_scan_frequency',
        __('Scan Frequency (hours)', 'advanced-link-checker'),
        'alc_scan_frequency_setting_html',
        'alc-settings',
        'alc_general_settings'
    );

    add_settings_field(
        'alc_links_per_scan',
        __('Links Per Scan', 'advanced-link-checker'),
        'alc_links_per_scan_setting_html',
        'alc-settings',
        'alc_general_settings'
    );

    add_settings_field(
        'alc_excluded_urls',
        __('Excluded URLs', 'advanced-link-checker'),
        'alc_excluded_urls_setting_html',
        'alc-settings',
        'alc_general_settings'
    );

    // Email Notification Section
    add_settings_section(
        'alc_email_settings',
        __('Email Notifications', 'advanced-link-checker'),
        'alc_email_settings_section_text',
        'alc-settings'
    );

    add_settings_field(
        'alc_notify_email',
        __('Enable Notifications', 'advanced-link-checker'),
        'alc_notify_email_setting_html',
        'alc-settings',
        'alc_email_settings'
    );

    add_settings_field(
        'alc_notify_recipients',
        __('Notification Recipients', 'advanced-link-checker'),
        'alc_notify_recipients_setting_html',
        'alc-settings',
        'alc_email_settings'
    );

    add_settings_field(
        'alc_notify_frequency',
        __('Notification Frequency', 'advanced-link-checker'),
        'alc_notify_frequency_setting_html',
        'alc-settings',
        'alc_email_settings'
    );

    add_settings_field(
        'alc_notify_threshold',
        __('Notification Threshold', 'advanced-link-checker'),
        'alc_notify_threshold_setting_html',
        'alc-settings',
        'alc_email_settings'
    );

    // Front-end Highlighting Section
    add_settings_section(
        'alc_highlight_settings',
        __('Front-end Highlighting', 'advanced-link-checker'),
        'alc_highlight_settings_section_text',
        'alc-settings'
    );

    add_settings_field(
        'alc_highlight_enable',
        __('Enable Highlighting', 'advanced-link-checker'),
        'alc_highlight_enable_setting_html',
        'alc-settings',
        'alc_highlight_settings'
    );

    add_settings_field(
        'alc_highlight_color',
        __('Highlight Color', 'advanced-link-checker'),
        'alc_highlight_color_setting_html',
        'alc-settings',
        'alc_highlight_settings'
    );

    add_settings_field(
        'alc_highlight_style',
        __('Highlight Style', 'advanced-link-checker'),
        'alc_highlight_style_setting_html',
        'alc-settings',
        'alc_highlight_settings'
    );
}

add_action('admin_init', 'alc_register_settings');

/**
 * Section descriptions
 */
function alc_general_settings_section_text() {
    echo '<p>' . __('Configure how and when the plugin scans for broken links.', 'advanced-link-checker') . '</p>';
}

function alc_email_settings_section_text() {
    echo '<p>' . __('Configure email notification settings for broken link alerts.', 'advanced-link-checker') . '</p>';
}

function alc_highlight_settings_section_text() {
    echo '<p>' . __('Configure how broken links are displayed to site visitors.', 'advanced-link-checker') . '</p>';
}

/**
 * Field rendering functions
 */
function alc_scan_frequency_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['scan_frequency']) ? $options['scan_frequency'] : ALC_SCAN_FREQUENCY;
    echo '<input id="alc_scan_frequency" name="alc_options[scan_frequency]" type="number" min="1" max="168" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __('How often to scan for broken links (1-168 hours).', 'advanced-link-checker') . '</p>';
}

function alc_links_per_scan_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['links_per_scan']) ? $options['links_per_scan'] : ALC_LINKS_PER_SCAN;
    echo '<input id="alc_links_per_scan" name="alc_options[links_per_scan]" type="number" min="10" max="5000" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __('Maximum number of links to check per scan (10-5000).', 'advanced-link-checker') . '</p>';
}

function alc_excluded_urls_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['excluded_urls']) ? $options['excluded_urls'] : '';
    echo '<textarea id="alc_excluded_urls" name="alc_options[excluded_urls]" rows="5" cols="50" class="large-text">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter URL patterns to exclude from scanning, one per line. Supports wildcards (*).', 'advanced-link-checker') . '</p>';
}

function alc_notify_email_setting_html() {
    $options = get_option('alc_options', array());
    $checked = isset($options['notify_email']) ? $options['notify_email'] : true;
    echo '<label><input id="alc_notify_email" name="alc_options[notify_email]" type="checkbox" value="1" ' . checked(1, $checked, false) . ' />';
    echo ' ' . __('Send email notifications when broken links are detected.', 'advanced-link-checker') . '</label>';
}

function alc_notify_recipients_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['notify_recipients']) ? $options['notify_recipients'] : get_option('admin_email');
    echo '<input id="alc_notify_recipients" name="alc_options[notify_recipients]" type="text" class="regular-text" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __('Email address(es) to send notifications to. Separate multiple with commas.', 'advanced-link-checker') . '</p>';
}

function alc_notify_frequency_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['notify_frequency']) ? $options['notify_frequency'] : 'daily';
    $frequencies = array(
        'instantly' => __('Instantly', 'advanced-link-checker'),
        'daily'    => __('Daily', 'advanced-link-checker'),
        'weekly'   => __('Weekly', 'advanced-link-checker'),
    );
    echo '<select id="alc_notify_frequency" name="alc_options[notify_frequency]">';
    foreach ($frequencies as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function alc_notify_threshold_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['notify_threshold']) ? $options['notify_threshold'] : ALC_NOTIFY_THRESHOLD;
    echo '<input id="alc_notify_threshold" name="alc_options[notify_threshold]" type="number" min="1" max="100" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . __('Minimum number of broken links required to trigger a notification.', 'advanced-link-checker') . '</p>';
}

function alc_highlight_enable_setting_html() {
    $options = get_option('alc_options', array());
    $checked = isset($options['highlight_enable']) ? $options['highlight_enable'] : true;
    echo '<label><input id="alc_highlight_enable" name="alc_options[highlight_enable]" type="checkbox" value="1" ' . checked(1, $checked, false) . ' />';
    echo ' ' . __('Visually highlight broken links for site visitors.', 'advanced-link-checker') . '</label>';
}

function alc_highlight_color_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['highlight_color']) ? $options['highlight_color'] : '#ff0000';
    echo '<input id="alc_highlight_color" name="alc_options[highlight_color]" type="color" value="' . esc_attr($value) . '" />';
}

function alc_highlight_style_setting_html() {
    $options = get_option('alc_options', array());
    $value = isset($options['highlight_style']) ? $options['highlight_style'] : 'wavy';
    $styles = array(
        'wavy'   => __('Wavy Underline', 'advanced-link-checker'),
        'solid'  => __('Solid Underline', 'advanced-link-checker'),
        'dashed' => __('Dashed Underline', 'advanced-link-checker'),
        'dotted' => __('Dotted Underline', 'advanced-link-checker'),
    );
    echo '<select id="alc_highlight_style" name="alc_options[highlight_style]">';
    foreach ($styles as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

/**
 * Validate and sanitize settings input before saving to database.
 */
function alc_options_validate($input) {
    $new_input = array();

    $new_input['scan_frequency']    = isset($input['scan_frequency']) ? absint($input['scan_frequency']) : ALC_SCAN_FREQUENCY;
    $new_input['links_per_scan']    = isset($input['links_per_scan']) ? absint($input['links_per_scan']) : ALC_LINKS_PER_SCAN;
    $new_input['excluded_urls']     = isset($input['excluded_urls']) ? sanitize_textarea_field($input['excluded_urls']) : '';
    $new_input['notify_email']      = isset($input['notify_email']) ? 1 : 0;
    $new_input['notify_recipients'] = isset($input['notify_recipients']) ? sanitize_text_field($input['notify_recipients']) : get_option('admin_email');
    $new_input['notify_frequency']  = isset($input['notify_frequency']) && in_array($input['notify_frequency'], array('instantly', 'daily', 'weekly')) ? $input['notify_frequency'] : 'daily';
    $new_input['notify_threshold']  = isset($input['notify_threshold']) ? absint($input['notify_threshold']) : ALC_NOTIFY_THRESHOLD;
    $new_input['highlight_enable']  = isset($input['highlight_enable']) ? 1 : 0;
    $new_input['highlight_color']   = isset($input['highlight_color']) ? sanitize_hex_color($input['highlight_color']) : '#ff0000';
    $new_input['highlight_style']   = isset($input['highlight_style']) && in_array($input['highlight_style'], array('wavy', 'solid', 'dashed', 'dotted')) ? $input['highlight_style'] : 'wavy';

    // Clamp values
    if ($new_input['scan_frequency'] < 1) $new_input['scan_frequency'] = 1;
    if ($new_input['scan_frequency'] > 168) $new_input['scan_frequency'] = 168;
    if ($new_input['links_per_scan'] < 10) $new_input['links_per_scan'] = 10;
    if ($new_input['links_per_scan'] > 5000) $new_input['links_per_scan'] = 5000;

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
        <h1><?php echo esc_html__('Advanced Link Checker Settings', 'advanced-link-checker'); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields('alc_options_group'); ?>
            <?php do_settings_sections('alc-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
