<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Front-end Link Highlighting for Advanced Link Checker Plugin
 *
 * This file is responsible for highlighting broken links on the front-end of the website.
 * It utilizes custom styles and JavaScript to make broken links easily noticeable to visitors.
 */

/**
 * Enqueue the necessary styles and scripts for front-end highlighting.
 */
function alc_enqueue_front_end_scripts() {
    $options = get_option('alc_options', array());
    $highlight_enabled = isset($options['highlight_enable']) ? $options['highlight_enable'] : true;

    if (!$highlight_enabled) {
        return;
    }

    wp_enqueue_style('alc-front-end-styles', plugins_url('/css/front-end-styles.css', __FILE__), array(), ALC_VERSION);
    wp_enqueue_script('alc-front-end-scripts', plugins_url('/js/front-end-scripts.js', __FILE__), array('jquery'), ALC_VERSION, true);
}

add_action('wp_enqueue_scripts', 'alc_enqueue_front_end_scripts');

/**
 * Add custom classes to broken links in the content.
 *
 * @param string $content The content of the post.
 * @return string Modified content with broken links highlighted.
 */
function alc_highlight_broken_links($content) {
    $options = get_option('alc_options', array());
    $highlight_enabled = isset($options['highlight_enable']) ? $options['highlight_enable'] : true;

    if (!$highlight_enabled) {
        return $content;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $broken_links = $wpdb->get_results("SELECT url FROM $table_name");

    if (!empty($broken_links)) {
        foreach ($broken_links as $link) {
            $escaped_url = preg_quote($link->url, '/');
            $content = preg_replace(
                '/<a\s+(.*?)href=["\']' . $escaped_url . '["\'](.*?)>(.*?)<\/a>/is',
                '<a $1href="' . esc_url($link->url) . '"$2 class="alc-broken-link">$3</a>',
                $content
            );
        }
    }

    return $content;
}

add_filter('the_content', 'alc_highlight_broken_links');

/**
 * Print custom CSS in the head for broken link highlighting.
 * Uses settings from the plugin options for customization.
 */
function alc_custom_highlight_styles() {
    $options = get_option('alc_options', array());
    $highlight_enabled = isset($options['highlight_enable']) ? $options['highlight_enable'] : true;

    if (!$highlight_enabled) {
        return;
    }

    $color = isset($options['highlight_color']) ? $options['highlight_color'] : '#ff0000';
    $style = isset($options['highlight_style']) ? $options['highlight_style'] : 'wavy';

    $underline_style = 'underline';
    if ($style === 'wavy') {
        $underline_style = 'underline wavy';
    } elseif ($style === 'dashed') {
        $underline_style = 'underline dashed';
    } elseif ($style === 'dotted') {
        $underline_style = 'underline dotted';
    }
    ?>
    <style type="text/css">
        .alc-broken-link {
            color: <?php echo esc_attr($color); ?> !important;
            text-decoration: <?php echo esc_attr($underline_style); ?> <?php echo esc_attr($color); ?> !important;
        }
        .alc-broken-link:hover {
            opacity: 0.8;
        }
    </style>
    <?php
}

add_action('wp_head', 'alc_custom_highlight_styles');
