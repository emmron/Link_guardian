<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Performance Optimization for Advanced Link Checker Plugin
 *
 * This file contains the performance optimization logic for the Advanced Link Checker plugin.
 * It includes caching mechanisms, memory usage optimization, and database query optimization.
 */

/**
 * Initializes performance optimizations.
 */
function alc_init_performance_optimizations() {
    add_action('init', 'alc_setup_caching');
    add_action('wp', 'alc_optimize_memory_usage');
}

/**
 * Sets up caching mechanisms for frequently accessed data.
 */
function alc_setup_caching() {
    // Check if a caching plugin is active and compatible. If not, implement basic caching.
    if (!wp_using_ext_object_cache()) {
        // Implement basic object caching for broken link data
        add_filter('pre_get_posts', 'alc_cache_broken_link_data');
    }
}

/**
 * Caches broken link data to reduce database queries.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function alc_cache_broken_link_data($query) {
    global $wpdb, $alc_table_name;

    // Only cache if it's a backend query for broken links
    if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'alc_broken_links') {
        $cache_key = 'alc_broken_links_data';
        $cached_data = wp_cache_get($cache_key);

        if (false === $cached_data) {
            $query_str = "SELECT * FROM $alc_table_name WHERE status = 'broken'";
            $results = $wpdb->get_results($query_str, ARRAY_A);
            wp_cache_set($cache_key, $results);
            $query->set('alc_cached_data', $results);
        } else {
            $query->set('alc_cached_data', $cached_data);
        }
    }
}

/**
 * Optimizes memory usage throughout the plugin.
 */
function alc_optimize_memory_usage() {
    // Adjust memory limit for large scan operations
    @ini_set('memory_limit', '256M');

    // Unload unnecessary WP components during scanning
    if (doing_action('alc_scheduled_scan')) {
        remove_action('wp_head', 'wp_print_scripts');
        remove_action('wp_head', 'wp_print_head_scripts', 9);
        remove_action('wp_head', 'wp_enqueue_scripts', 1);
    }
}

/**
 * Optimizes database queries used by the plugin.
 */
function alc_optimize_database_queries() {
    global $wpdb, $alc_table_name;

    // Add indexes to the custom table for faster queries
    $sql = "ALTER TABLE $alc_table_name ADD INDEX status_index (status)";
    $wpdb->query($sql);

    // Optimize queries by fetching only required columns
    add_filter('alc_fetch_links_query', function($query) {
        return str_replace("SELECT *", "SELECT url, status_code, post_id", $query);
    });
}

// Initialize performance optimizations
alc_init_performance_optimizations();
