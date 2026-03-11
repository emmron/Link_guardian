<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AJAX Handlers for Advanced Link Checker Plugin
 *
 * This file is responsible for handling all AJAX requests made by the admin interface of the Advanced Link Checker plugin.
 * It includes functions to recheck broken links, mark links as resolved, and perform bulk actions on links.
 */

/**
 * Handles the AJAX request to recheck a single broken link.
 */
function alc_handle_recheck_link() {
    check_ajax_referer('alc_recheck_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'advanced-link-checker'));
    }

    $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;

    if ($link_id > 0) {
        alc_recheck_link_status($link_id);
        wp_send_json_success(__('Link rechecked successfully.', 'advanced-link-checker'));
    } else {
        wp_send_json_error(__('Invalid link ID.', 'advanced-link-checker'));
    }

    wp_die();
}

add_action('wp_ajax_alc_recheck_link', 'alc_handle_recheck_link');

/**
 * Handles the AJAX request to resolve a single broken link (remove from database).
 */
function alc_handle_resolve_link() {
    check_ajax_referer('alc_resolve_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'advanced-link-checker'));
    }

    $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;

    if ($link_id > 0) {
        alc_delete_broken_link($link_id);
        wp_send_json_success(__('Link marked as resolved.', 'advanced-link-checker'));
    } else {
        wp_send_json_error(__('Invalid link ID.', 'advanced-link-checker'));
    }

    wp_die();
}

add_action('wp_ajax_alc_resolve_link', 'alc_handle_resolve_link');

/**
 * Handles the AJAX request to perform bulk actions on broken links.
 */
function alc_handle_bulk_action() {
    check_ajax_referer('alc_bulk_action_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'advanced-link-checker'));
    }

    $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
    $link_ids = isset($_POST['link_ids']) ? array_map('intval', $_POST['link_ids']) : array();

    if (!empty($link_ids) && !empty($bulk_action)) {
        switch ($bulk_action) {
            case 'recheck':
                foreach ($link_ids as $link_id) {
                    alc_recheck_link_status($link_id);
                }
                $message = sprintf(__('Rechecked %d links.', 'advanced-link-checker'), count($link_ids));
                wp_send_json_success($message);
                break;
            case 'resolve':
                foreach ($link_ids as $link_id) {
                    alc_delete_broken_link($link_id);
                }
                $message = sprintf(__('Marked %d links as resolved.', 'advanced-link-checker'), count($link_ids));
                wp_send_json_success($message);
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'advanced-link-checker'));
        }
    } else {
        wp_send_json_error(__('No links selected or invalid action.', 'advanced-link-checker'));
    }

    wp_die();
}

add_action('wp_ajax_alc_bulk_action', 'alc_handle_bulk_action');

/**
 * Handles the AJAX request to export broken links report as CSV.
 */
function alc_handle_export_csv() {
    check_ajax_referer('alc_export_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'advanced-link-checker'));
    }

    $report_data = alc_generate_report();
    alc_export_report_to_csv($report_data);

    wp_die();
}

add_action('wp_ajax_alc_export_csv', 'alc_handle_export_csv');
