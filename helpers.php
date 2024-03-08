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
    if (!ALC_NOTIFY_EMAIL || empty($brokenLinks)) {
        return;
    }

    $to = ALC_NOTIFY_RECIPIENTS;
    $subject = 'Broken Links Detected on Your Website';
    $body = "Hello,\n\nThe following broken links have been detected on your website:\n\n";
    foreach ($brokenLinks as $link) {
        $body .= "URL: {$link['url']}, Status: {$link['status']}, Detected On: {$link['detected_on']}\n";
    }
    $body .= "\nPlease take the necessary actions to resolve these issues.\n\nThank you.";
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
        esc_html($linkData['status']),
        esc_html(date('Y-m-d H:i:s', strtotime($linkData['detected_on'])))
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
    // Placeholder for future implementation. This function should check against
    // a list of user-defined URL patterns or domains to exclude from scanning.
    return false;
}

/**
 * Log activity or errors for debugging purposes.
 *
 * @param mixed $message The message to log.
 * @return void
 */
function alc_log($message) {
    if (WP_DEBUG_LOG) {
        error_log(print_r($message, true));
    }
}
