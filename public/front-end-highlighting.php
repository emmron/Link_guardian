<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Front-end Link Highlighting for Advanced Link Checker Plugin
 *
 * This file is responsible for highlighting broken links on the front-end of the website.
 * It utilizes custom styles and JavaScript to make broken links easily noticeable to visitors.
 */

// Enqueue the necessary styles and scripts for front-end highlighting
function alc_enqueue_front_end_scripts() {
    wp_enqueue_style('alc-front-end-styles', plugins_url('/css/front-end-styles.css', __FILE__));
    wp_enqueue_script('alc-front-end-scripts', plugins_url('/js/front-end-scripts.js', __FILE__), array('jquery'), false, true);
}

add_action('wp_enqueue_scripts', 'alc_enqueue_front_end_scripts');

/**
 * Add custom classes to broken links in the content
 *
 * @param string $content The content of the post.
 * @return string Modified content with broken links highlighted.
 */
function alc_highlight_broken_links($content) {
    global $wpdb;
    $alc_table_name = $wpdb->prefix . ALC_TABLE_NAME;

    // Retrieve all broken links from the database
    $broken_links = $wpdb->get_results("SELECT url FROM $alc_table_name WHERE status != 'resolved'");

    if (!empty($broken_links)) {
        foreach ($broken_links as $link) {
            // Escape the URL for use in a regular expression
            $escaped_url = preg_quote($link->url, '/');
            // Replace the link in the content with a highlighted version
            $content = preg_replace('/<a href="' . $escaped_url . '"(.*?)>(.*?)<\/a>/', '<a href="' . $escaped_url . '"$1 class="alc-broken-link">$2</a>', $content);
        }
    }

    return $content;
}

add_filter('the_content', 'alc_highlight_broken_links');

/**
 * Print custom CSS in the head for broken link highlighting
 * This allows users to customize the appearance of broken links directly from the plugin settings.
 */
function alc_custom_highlight_styles() {
    ?>
    <style type="text/css">
        .alc-broken-link {
            color: #ff0000; /* Default broken link color */
            text-decoration: underline wavy red;
        }
        /* Additional custom styles can be added here based on user settings */
    </style>
    <?php
}

add_action('wp_head', 'alc_custom_highlight_styles');
