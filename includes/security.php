<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Security file for Advanced Link Checker Plugin
 *
 * This file contains security measures to protect the plugin from common vulnerabilities
 * such as SQL injection, cross-site scripting (XSS), and other potential security threats.
 */

/**
 * Sanitize input data.
 *
 * @param mixed $data The input data to be sanitized.
 * @return mixed The sanitized data.
 */
function alc_sanitize_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = alc_sanitize_input($value);
        }
    } else {
        $data = sanitize_text_field($data);
    }
    return $data;
}

/**
 * Validate nonce for security.
 *
 * @param string $nonce The nonce to validate.
 * @param string $action The action associated with the nonce.
 * @return bool True if nonce is valid, false otherwise.
 */
function alc_verify_nonce($nonce, $action) {
    if (!wp_verify_nonce($nonce, $action)) {
        wp_die(__('Security check failed.', 'advanced-link-checker'));
        return false;
    }
    return true;
}

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
 * Implement Content Security Policy (CSP) headers.
 */
function alc_add_csp_headers() {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
}

add_action('init', 'alc_add_csp_headers');

/**
 * Prevent SQL Injection by using prepared statements for database queries.
 * Note: This is a conceptual implementation. Actual database queries should use
 * WordPress's $wpdb->prepare method for security.
 */
function alc_prepared_query_example() {
    global $wpdb;
    $query = "SELECT * FROM {$wpdb->prefix}your_table WHERE column = %s";
    $safe_query = $wpdb->prepare($query, $your_variable);
    $results = $wpdb->get_results($safe_query);
}

/**
 * Prevent Cross-Site Scripting (XSS) by sanitizing and escaping all user inputs and outputs.
 * Note: Use alc_sanitize_input() for sanitizing inputs and alc_escape_output() for escaping outputs.
 */

/**
 * Additional security measures can be implemented as needed, following WordPress's best practices
 * and guidelines for plugin development.
 */
