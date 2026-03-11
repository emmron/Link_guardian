<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helpers file for Advanced Link Checker Plugin
 *
 * This file contains various helper functions used throughout the plugin to perform
 * common tasks such as sending emails, formatting data, and more.
 */

/**
 * Send email notifications about broken links.
 *
 * @param array $brokenLinks An array of broken links information.
 * @return void
 */
function alc_send_email_notifications($brokenLinks) {
    $options = get_option('alc_options', array());
    $notify_enabled = isset($options['notify_email']) ? $options['notify_email'] : ALC_NOTIFY_EMAIL;

    if (!$notify_enabled || empty($brokenLinks)) {
        return;
    }

    $to = isset($options['notify_recipients']) ? $options['notify_recipients'] : get_option('admin_email');
    $site_name = get_bloginfo('name');
    $subject = sprintf('[%s] Broken Links Detected', $site_name);

    $body = "Hello,\n\n";
    $body .= sprintf("The following broken links have been detected on %s:\n\n", $site_name);

    foreach ($brokenLinks as $link) {
        $url = is_object($link) ? $link->url : (isset($link['url']) ? $link['url'] : '');
        $status = is_object($link) ? $link->status_code : (isset($link['status_code']) ? $link['status_code'] : 'Unknown');
        $detected = is_object($link) ? $link->detection_time : (isset($link['detection_time']) ? $link['detection_time'] : '');

        $body .= sprintf("  URL: %s\n  Status: %s\n  Detected: %s\n\n", $url, $status, $detected);
    }

    $body .= "You can manage broken links in your WordPress admin panel.\n";
    $body .= admin_url('admin.php?page=advanced-link-checker') . "\n\n";
    $body .= "Thank you.";

    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail($to, $subject, $body, $headers);
}

/**
 * Format the broken link data for display.
 *
 * @param array $linkData An array containing information about a broken link.
 * @return string Formatted string for display.
 */
function alc_format_link_data_for_display($linkData) {
    return sprintf(
        'URL: %s, Status Code: %s, Detected On: %s',
        esc_url($linkData['url']),
        esc_html($linkData['status_code']),
        esc_html(date_i18n('Y-m-d H:i:s', strtotime($linkData['detection_time'])))
    );
}

/**
 * Verify nonce for security.
 *
 * @param string $nonce Nonce that was used for verification.
 * @param string $action Action name.
 * @return bool Whether the nonce is valid.
 */
function alc_verify_nonce($nonce, $action) {
    return wp_verify_nonce($nonce, $action);
}

/**
 * Sanitize input data.
 *
 * @param mixed $data The input data to be sanitized.
 * @return mixed Sanitized data.
 */
function alc_sanitize_input($data) {
    if (is_array($data)) {
        return array_map('alc_sanitize_input', $data);
    }
    return is_string($data) ? sanitize_text_field($data) : $data;
}

/**
 * Check if a given URL is excluded based on the plugin's settings.
 *
 * @param string $url The URL to check.
 * @return bool True if the URL is excluded, false otherwise.
 */
function alc_is_url_excluded($url) {
    $options = get_option('alc_options', array());
    $excluded_urls = isset($options['excluded_urls']) ? $options['excluded_urls'] : '';

    if (empty($excluded_urls)) {
        return false;
    }

    $patterns = array_filter(array_map('trim', explode("\n", $excluded_urls)));

    foreach ($patterns as $pattern) {
        // Convert wildcard pattern to regex
        $regex = str_replace(
            array('\*', '\?'),
            array('.*', '.'),
            preg_quote($pattern, '/')
        );

        if (preg_match('/^' . $regex . '$/i', $url)) {
            return true;
        }
    }

    return false;
}

/**
 * Log activity or errors for debugging purposes.
 *
 * @param mixed $message The message to log.
 * @return void
 */
function alc_log($message) {
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('[Advanced Link Checker] ' . print_r($message, true));
    }
}
