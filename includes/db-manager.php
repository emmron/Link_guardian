<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

global $wpdb;
$alc_table_name = $wpdb->prefix . ALC_TABLE_NAME;

/**
 * Database Manager for Advanced Link Checker Plugin
 *
 * This file handles all database operations for storing, retrieving,
 * updating, and deleting broken link data.
 */

/**
 * Creates or updates the custom database table for storing broken links.
 */
function alc_install_db() {
    global $wpdb;
    global $alc_table_name;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $alc_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        status_code varchar(10) NOT NULL,
        post_id bigint(20) NOT NULL,
        detection_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('alc_db_version', ALC_DB_VERSION);
}

/**
 * Inserts a new broken link record into the database.
 *
 * @param string $url The URL of the broken link.
 * @param string $status_code The HTTP status code of the broken link.
 * @param int $post_id The ID of the post containing the broken link.
 */
function alc_insert_broken_link($url, $status_code, $post_id) {
    global $wpdb;
    global $alc_table_name;

    $wpdb->insert(
        $alc_table_name,
        array(
            'url' => $url,
            'status_code' => $status_code,
            'post_id' => $post_id,
            'detection_time' => current_time('mysql'),
        ),
        array('%s', '%s', '%d', '%s')
    );
}

/**
 * Retrieves broken links from the database.
 *
 * @param array $args Optional arguments to filter the results.
 * @return array An array of broken link records.
 */
function alc_get_broken_links($args = array()) {
    global $wpdb;
    global $alc_table_name;

    $defaults = array(
        'orderby' => 'detection_time',
        'order' => 'DESC',
        'number' => 20,
        'offset' => 0,
    );

    $args = wp_parse_args($args, $defaults);

    $query = $wpdb->prepare(
        "SELECT * FROM $alc_table_name ORDER BY %s %s LIMIT %d, %d",
        $args['orderby'], $args['order'], $args['offset'], $args['number']
    );

    return $wpdb->get_results($query, ARRAY_A);
}

/**
 * Deletes a broken link record from the database.
 *
 * @param int $id The ID of the broken link record to delete.
 */
function alc_delete_broken_link($id) {
    global $wpdb;
    global $alc_table_name;

    $wpdb->delete($alc_table_name, array('id' => $id), array('%d'));
}

/**
 * Updates the status of a broken link record.
 *
 * @param int $id The ID of the broken link record to update.
 * @param string $status_code The new HTTP status code.
 */
function alc_update_broken_link_status($id, $status_code) {
    global $wpdb;
    global $alc_table_name;

    $wpdb->update(
        $alc_table_name,
        array('status_code' => $status_code),
        array('id' => $id),
        array('%s'),
        array('%d')
    );
}

// Hook alc_install_db to the activation hook for plugin installation
register_activation_hook(__FILE__, 'alc_install_db');
