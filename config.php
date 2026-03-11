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
define('ALC_SCAN_FREQUENCY', 24);

// Default limit for links per scan to optimize performance
define('ALC_LINKS_PER_SCAN', 500);

// Email notification defaults
define('ALC_NOTIFY_EMAIL', true);
define('ALC_NOTIFY_FREQUENCY', 'daily');
define('ALC_NOTIFY_THRESHOLD', 10);

// Front-end highlighting defaults
define('ALC_HIGHLIGHT_ENABLE', true);

// Security settings
define('ALC_NONCE_ACTION', 'alc_nonce_action');
define('ALC_NONCE_NAME', 'alc_nonce');

// Performance optimization settings
define('ALC_USE_CACHING', true);
define('ALC_CACHE_LIFETIME', 3600);
