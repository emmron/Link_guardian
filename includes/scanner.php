<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Scanner file for Advanced Link Checker Plugin
 *
 * This file contains the logic for scanning posts, pages, and custom post types for broken links.
 */

/**
 * Initiates the link scanning process.
 */
function alc_start_scanning() {
    if (!wp_next_scheduled('alc_scheduled_scan')) {
        $options = get_option('alc_options', array());
        $frequency = isset($options['scan_frequency']) ? intval($options['scan_frequency']) : ALC_SCAN_FREQUENCY;

        $recurrence = ($frequency <= 1) ? 'hourly' : 'twicedaily';
        if ($frequency >= 24) {
            $recurrence = 'daily';
        }

        wp_schedule_event(time(), $recurrence, 'alc_scheduled_scan');
    }
}

add_action('alc_scheduled_scan', 'alc_perform_scan');

/**
 * Performs the actual scanning of content for broken links.
 */
function alc_perform_scan() {
    $options = get_option('alc_options', array());
    $links_per_scan = isset($options['links_per_scan']) ? intval($options['links_per_scan']) : ALC_LINKS_PER_SCAN;

    $args = array(
        'post_type'      => array('post', 'page'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);
    $links_checked = 0;

    if ($query->have_posts()) {
        while ($query->have_posts() && $links_checked < $links_per_scan) {
            $query->the_post();
            $content = get_the_content();
            $post_id = get_the_ID();

            $links = alc_extract_links($content);
            foreach ($links as $link) {
                if ($links_checked >= $links_per_scan) {
                    break;
                }

                // Skip excluded URLs
                if (alc_is_url_excluded($link)) {
                    continue;
                }

                // Skip mailto, tel, anchor links
                if (preg_match('/^(mailto:|tel:|#|javascript:)/i', $link)) {
                    continue;
                }

                // Skip relative URLs that aren't full URLs
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    continue;
                }

                alc_check_link($link, $post_id);
                $links_checked++;
            }
        }
    }

    wp_reset_postdata();

    alc_log(sprintf('Scan completed. Checked %d links.', $links_checked));
}

/**
 * Extracts links from the provided content.
 *
 * @param string $content The content from which to extract links.
 * @return array An array of links found in the content.
 */
function alc_extract_links($content) {
    $links = array();

    if (empty($content)) {
        return $links;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);
    $anchorTags = $dom->getElementsByTagName('a');

    foreach ($anchorTags as $tag) {
        $href = $tag->getAttribute('href');
        if (!empty($href)) {
            $links[] = $href;
        }
    }

    return array_unique($links);
}

/**
 * Checks the status of a single link.
 *
 * @param string $link The URL to check.
 * @param int $post_id The ID of the post containing the link.
 */
function alc_check_link($link, $post_id = 0) {
    $response = wp_remote_head($link, array(
        'timeout'     => 10,
        'redirection' => 5,
        'sslverify'   => false,
    ));

    if (is_wp_error($response)) {
        alc_store_broken_link($link, 0, $post_id);
        return;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code >= 400 || $status_code === 0) {
        alc_store_broken_link($link, $status_code, $post_id);
    }
}

/**
 * Stores a broken link in the database.
 *
 * @param string $link The broken link URL.
 * @param int $status The HTTP status code of the broken link.
 * @param int $post_id The ID of the post containing the link.
 */
function alc_store_broken_link($link, $status, $post_id = 0) {
    if ($post_id === 0) {
        $post_id = get_the_ID();
    }

    alc_insert_broken_link($link, $status, $post_id);
}

/**
 * Schedules and hooks the scanning process.
 */
function alc_setup_scanner() {
    add_action('wp', 'alc_start_scanning');
}

alc_setup_scanner();
