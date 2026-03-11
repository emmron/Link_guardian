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
}

/**
 * Sets up caching mechanisms for frequently accessed data.
 */
function alc_setup_caching() {
    if (!wp_using_ext_object_cache() && ALC_USE_CACHING) {
        add_action('alc_after_scan', 'alc_clear_broken_links_cache');
    }
}

/**
 * Gets broken links data with caching.
 *
 * @return array|false Cached broken link data or false if not cached.
 */
function alc_get_cached_broken_links() {
    $cache_key = 'alc_broken_links_data';
    $cached_data = wp_cache_get($cache_key, 'alc');

    if (false === $cached_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . ALC_TABLE_NAME;

        $results = $wpdb->get_results("SELECT url, status_code, post_id FROM $table_name", ARRAY_A);
        wp_cache_set($cache_key, $results, 'alc', ALC_CACHE_LIFETIME);
        return $results;
    }

    return $cached_data;
}

/**
 * Clears the broken links cache after a scan.
 */
function alc_clear_broken_links_cache() {
    wp_cache_delete('alc_broken_links_data', 'alc');
}

/**
 * Optimizes memory usage during large scan operations.
 */
function alc_optimize_memory_for_scan() {
    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('admin');
    }
}

/**
 * Optimizes database queries by adding indexes if needed.
 * Called on plugin activation.
 */
function alc_optimize_database_queries() {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    // Check if url index exists, add if not
    $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name WHERE Key_name = 'url_index'");
    if (empty($indexes)) {
        $wpdb->query("ALTER TABLE $table_name ADD INDEX url_index (url(191))");
    }
}

// Initialize performance optimizations
alc_init_performance_optimizations();
