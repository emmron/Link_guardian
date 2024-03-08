<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'db-manager.php';

/**
 * Link Rechecker file for Advanced Link Checker Plugin
 *
 * This file contains the logic for rechecking the status of broken links detected by the plugin.
 */

/**
 * Rechecks the status of a single broken link.
 *
 * @param int $link_id The ID of the link to recheck.
 * @return void
 */
function alc_recheck_link_status($link_id) {
    global $wpdb;
    global $alc_table_name;

    $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM $alc_table_name WHERE id = %d", $link_id));

    if (!$link) {
        return; // Link not found
    }

    $response = wp_remote_get($link->url, array('timeout' => 5));

    if (is_wp_error($response)) {
        // Handle errors (e.g., network issues)
        return;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code == 200) {
        // Link is now working, remove from the database
        $wpdb->delete($alc_table_name, array('id' => $link_id));
    } else {
        // Update the status code in the database
        $wpdb->update(
            $alc_table_name,
            array('status_code' => $status_code),
            array('id' => $link_id)
        );
    }
}

/**
 * Rechecks the status of all broken links.
 *
 * @return void
 */
function alc_recheck_all_links() {
    global $wpdb;
    global $alc_table_name;

    $links = $wpdb->get_results("SELECT * FROM $alc_table_name");

    foreach ($links as $link) {
        alc_recheck_link_status($link->id);
    }
}

// Hook into AJAX action for rechecking links
add_action('wp_ajax_alc_recheck_link', function() {
    $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;

    if ($link_id > 0) {
        alc_recheck_link_status($link_id);

        wp_send_json_success(array('message' => 'Link rechecked successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Invalid link ID.'));
    }
});

// Hook into AJAX action for rechecking all links
add_action('wp_ajax_alc_recheck_all_links', function() {
    alc_recheck_all_links();

    wp_send_json_success(array('message' => 'All links rechecked successfully.'));
});
?>
