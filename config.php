<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Advanced Link Checker Plugin Configuration File
 *
 * This file contains the global configurations for the Advanced Link Checker plugin.
 * It includes settings for scanning frequency, performance optimizations, email notifications,
 * and other customizable features to enhance the plugin's functionality and user experience.
 */

// Plugin version
define('ALC_VERSION', '1.0.0');

// Database version for internal use (to manage database changes)
define('ALC_DB_VERSION', '1.0');

// Custom table name for storing broken links
define('ALC_TABLE_NAME', 'alc_broken_links');

// Default scanning frequency (in hours)
define('ALC_SCAN_FREQUENCY', 24); // Every 24 hours

// Default limit for links per scan to optimize performance
define('ALC_LINKS_PER_SCAN', 500);

// Email notification settings
define('ALC_NOTIFY_EMAIL', true); // Enable or disable email notifications
define('ALC_NOTIFY_RECIPIENTS', get_option('admin_email')); // Default to admin email
define('ALC_NOTIFY_FREQUENCY', 'daily'); // Options: 'instantly', 'daily', 'weekly'
define('ALC_NOTIFY_THRESHOLD', 10); // Number of broken links to trigger notification

// Front-end highlighting settings
define('ALC_HIGHLIGHT_ENABLE', true); // Enable or disable front-end link highlighting
define('ALC_HIGHLIGHT_STYLE', 'color: red; text-decoration: underline;'); // Default highlighting style

// Security settings
define('ALC_NONCE_ACTION', 'alc_nonce_action');
define('ALC_NONCE_NAME', 'alc_nonce');

// Performance optimization settings
define('ALC_USE_CACHING', true); // Enable or disable caching
define('ALC_CACHE_LIFETIME', 3600); // Cache lifetime in seconds (default: 1 hour)

// External service integration (placeholders for future use)
define('ALC_URL_SHORTENER_API', '');
define('ALC_ANALYTICS_PLATFORM_API', '');

// Ensure that these configurations can be overridden by the wp-config.php file
foreach (get_defined_constants(true)['user'] as $key => $value) {
    if (defined($key)) {
        continue;
    }
    define($key, $value);
}
