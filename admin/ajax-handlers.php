<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AJAX Handlers for Advanced Link Checker Plugin
 *
 * This file is responsible for handling all AJAX requests made by the admin interface of the Advanced Link Checker plugin.
 * It includes functions to recheck broken links, mark links as resolved, and perform bulk actions on links.
 */

// Include necessary files
require_once ALC_PLUGIN_PATH . 'includes/db-manager.php';
require_once ALC_PLUGIN_PATH . 'includes/link-rechecker.php';

/**
 * Register AJAX actions for both logged in and non-logged in users
 */
add_action('wp_ajax_alc_recheck_link', 'alc_handle_recheck_link'); $results = alc_bulk_recheck_links($link_ids);
                $message = sprintf(__('Rechecked %d links.', 'advanced-link-checker'), count($results));
                wp_send_json_success($message);
                break;
            case 'resolve':
                $results = alc_bulk_resolve_links($link_ids);
                $message = sprintf(__('Marked %d links as resolved.', 'advanced-link-checker'), count($results));
                wp_send_json_success($message);
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'advanced-link-checker'));
        }
    } else {
        wp_send_json_error(__('No links selected or invalid action.', 'advanced-link-checker'));
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

add_action('wp_ajax_alc_bulk_action', 'alc_handle_bulk_action');

/**
 * Handles the AJAX request to recheck a single broken link.
 */
function alc_handle_recheck_link() {
    // Verify nonce for security
    check_ajax_referer('alc_recheck_nonce', 'security');

    $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;

    if ($link_id > 0) {
        $result = alc_recheck_link($link_id);

        if ($result) {
            wp_send_json_success(__('Link rechecked successfully.', 'advanced-link-checker'));
        } else {
            wp_send_json_error(__('Failed to recheck the link.', 'advanced-link-checker'));
        }
    } else {
        wp_send_json_error(__('Invalid link ID.', 'advanced-link-checker'));
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

/**
 * Handles the AJAX request to perform bulk actions on broken links.
 */
function alc_handle_bulk_action() {
    // Verify nonce for security
    check_ajax_referer('alc_bulk_action_nonce', 'security');

    $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
    $link_ids = isset($_POST['link_ids']) ? array_map('intval', $_POST['link_ids']) : array();

    if (!empty($link_ids) && !empty($action)) {
        switch ($action) {
            case 'recheck':
               