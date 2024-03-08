<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Scanner file for Advanced Link Checker Plugin
 *
 * This file contains the logic for scanning posts, pages, and custom post types for broken links.
 */

require_once plugin_dir_path(__FILE__) . 'db-manager.php';

/**
 * Initiates the link scanning process.
 */
function alc_start_scanning() {
    if (!wp_next_scheduled('alc_scheduled_scan')) {
        wp_schedule_event(time(), ALC_SCAN_FREQUENCY, 'alc_scheduled_scan');
    }
}

add_action('alc_scheduled_scan', 'alc_perform_scan');

/**
 * Performs the actual scanning of content for broken links.
 */
function alc_perform_scan() {
    $args = array(
        'post_type'      => array('post', 'page', 'any_custom_post_type_you_want'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $content = get_the_content();
            // Extract links from content
            $links = alc_extract_links($content);
            foreach ($links as $link) {
                alc_check_link($link);
            }
        }
    }

    wp_reset_postdata();
}

/**
 * Extracts links from the provided content.
 *
 * @param string $content The content from which to extract links.
 * @return array An array of links found in the content.
 */
function alc_extract_links($content) {
    $links = array();
    $dom = new DOMDocument();
    @$dom->loadHTML($content);
    $anchorTags = $dom->getElementsByTagName('a');

    foreach ($anchorTags as $tag) {
        $href = $tag->getAttribute('href');
        if (!empty($href)) {
            $links[] = $href;
        }
    }

    return $links;
}

/**
 * Checks the status of a single link.
 *
 * @param string $link The URL to check.
 */
function alc_check_link($link) {
    $response = wp_remote_head($link, array('timeout' => 5));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        alc_store_broken_link($link, wp_remote_retrieve_response_code($response));
    }
}

/**
 * Stores a broken link in the database.
 *
 * @param string $link The broken link URL.
 * @param int $status The HTTP status code of the broken link.
 */
function alc_store_broken_link($link, $status) {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $wpdb->insert(
        $table_name,
        array(
            'url' => $link,
            'status_code' => $status,
            'post_id' => get_the_ID(),
            'detection_time' => current_time('mysql', 1),
        ),
        array('%s', '%d', '%d', '%s')
    );
}

/**
 * Schedules and hooks the scanning process.
 */
function alc_setup_scanner() {
    add_action('wp', 'alc_start_scanning');
}

alc_setup_scanner();
