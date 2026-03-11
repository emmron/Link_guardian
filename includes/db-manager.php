<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

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
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(2083) NOT NULL,
        status_code varchar(10) NOT NULL,
        post_id bigint(20) NOT NULL,
        detection_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        notification_sent tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY notification_sent (notification_sent)
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
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    // Check if this URL + post_id combo already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE url = %s AND post_id = %d",
        $url, $post_id
    ));

    if ($existing) {
        // Update existing record
        $wpdb->update(
            $table_name,
            array(
                'status_code'    => $status_code,
                'detection_time' => current_time('mysql'),
            ),
            array('id' => $existing),
            array('%s', '%s'),
            array('%d')
        );
    } else {
        // Insert new record
        $wpdb->insert(
            $table_name,
            array(
                'url'               => $url,
                'status_code'       => $status_code,
                'post_id'           => $post_id,
                'detection_time'    => current_time('mysql'),
                'notification_sent' => 0,
            ),
            array('%s', '%s', '%d', '%s', '%d')
        );
    }
}

/**
 * Retrieves broken links from the database.
 *
 * @param array $args Optional arguments to filter the results.
 * @return array An array of broken link records.
 */
function alc_get_broken_links($args = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $defaults = array(
        'orderby' => 'detection_time',
        'order'   => 'DESC',
        'number'  => 20,
        'offset'  => 0,
    );

    $args = wp_parse_args($args, $defaults);

    // Whitelist allowed orderby columns
    $allowed_orderby = array('id', 'url', 'status_code', 'post_id', 'detection_time');
    $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'detection_time';
    $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

    $query = $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
        intval($args['number']),
        intval($args['offset'])
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
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $wpdb->delete($table_name, array('id' => $id), array('%d'));
}

/**
 * Updates the status of a broken link record.
 *
 * @param int $id The ID of the broken link record to update.
 * @param string $status_code The new HTTP status code.
 */
function alc_update_broken_link_status($id, $status_code) {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $wpdb->update(
        $table_name,
        array('status_code' => $status_code),
        array('id' => $id),
        array('%s'),
        array('%d')
    );
}

/**
 * Gets total count of broken links.
 *
 * @return int The total number of broken links.
 */
function alc_count_broken_links() {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
}
