<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Link Rechecker file for Advanced Link Checker Plugin
 *
 * This file contains the logic for rechecking the status of broken links detected by the plugin.
 */

/**
 * Rechecks the status of a single broken link.
 *
 * @param int $link_id The ID of the link to recheck.
 * @return bool Whether the recheck was successful.
 */
function alc_recheck_link_status($link_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $link_id));

    if (!$link) {
        return false;
    }

    $response = wp_remote_head($link->url, array(
        'timeout'     => 10,
        'redirection' => 5,
        'sslverify'   => false,
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code == 200) {
        // Link is now working, remove from the database
        $wpdb->delete($table_name, array('id' => $link_id), array('%d'));
    } else {
        // Update the status code in the database
        $wpdb->update(
            $table_name,
            array('status_code' => $status_code),
            array('id' => $link_id),
            array('%s'),
            array('%d')
        );
    }

    return true;
}

/**
 * Rechecks the status of all broken links.
 */
function alc_recheck_all_links() {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $links = $wpdb->get_results("SELECT id FROM $table_name");

    foreach ($links as $link) {
        alc_recheck_link_status($link->id);
    }
}

// Hook into AJAX action for rechecking links
add_action('wp_ajax_alc_recheck_link', function() {
    check_ajax_referer('alc_recheck_nonce', 'security');

    $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;

    if ($link_id > 0) {
        $result = alc_recheck_link_status($link_id);

        if ($result) {
            wp_send_json_success(array('message' => __('Link rechecked successfully.', 'advanced-link-checker')));
        } else {
            wp_send_json_error(array('message' => __('Failed to recheck the link.', 'advanced-link-checker')));
        }
    } else {
        wp_send_json_error(array('message' => __('Invalid link ID.', 'advanced-link-checker')));
    }
});

// Hook into AJAX action for rechecking all links
add_action('wp_ajax_alc_recheck_all_links', function() {
    check_ajax_referer('alc_recheck_nonce', 'security');

    alc_recheck_all_links();

    wp_send_json_success(array('message' => __('All links rechecked successfully.', 'advanced-link-checker')));
});
