<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Security file for Advanced Link Checker Plugin
 *
 * This file contains security measures to protect the plugin from common vulnerabilities.
 *
 * Note: Core sanitize/verify functions (alc_sanitize_input, alc_verify_nonce)
 * are defined in helpers.php to avoid redeclaration.
 */

/**
 * Escape output for safe HTML display.
 *
 * @param string $output The output to be escaped.
 * @return string The escaped output.
 */
function alc_escape_output($output) {
    return esc_html($output);
}

/**
 * Add security headers for plugin admin pages.
 */
function alc_add_security_headers() {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && strpos($screen->id, 'advanced-link-checker') !== false) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
    }
}

add_action('admin_init', 'alc_add_security_headers');
