<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Admin Page for Advanced Link Checker Plugin
 *
 * This file is responsible for creating and managing the admin page for the Advanced Link Checker plugin.
 * It includes functions to register the admin menu, display the admin page content, and handle user interactions.
 */

// Include necessary files
require_once ALC_PLUGIN_PATH . 'admin/link-table.php';
require_once ALC_PLUGIN_PATH . 'includes/db-manager.php';

/**
 * Register the admin menu for the Advanced Link Checker plugin.
 */
function alc_register_admin_menu() {
    add_menu_page(
        __('Advanced Link Checker', 'advanced-link-checker'), // Page title
        __('Link Checker', 'advanced-link-checker'), // Menu title
        'manage_options', // Capability
        'advanced-link-checker', // Menu slug
        'alc_admin_page_display', // Function to display the admin page
        'dashicons-admin-links', // Icon URL
        100 // Position
    );
}
add_action('admin_menu', 'alc_register_admin_menu');

/**
 * Display the admin page content.
 */
function alc_admin_page_display() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'advanced-link-checker'));
    }

    // Handle form submission, if any
    if (isset($_POST['alc_action'])) {
        // Verify nonce for security
        check_admin_referer('alc_manage_links');

        // Handle the action (e.g., recheck, dismiss, etc.)
        // This is a placeholder for action handling logic
        // Actions would be processed here based on 'alc_action' value
    }

    // Prepare the list table
    $linkTable = new ALC_Link_Table();
    $linkTable->prepare_items();

    // Display the admin page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post">
            <?php
            // Output nonce, action, and option_page fields for a settings page.
            wp_nonce_field('alc_manage_links');
            $linkTable->display();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register the styles and scripts for the admin page.
 */
function alc_admin_enqueue_scripts($hook) {
    // Load only on our specific admin page
    if ($hook != 'toplevel_page_advanced-link-checker') {
        return;
    }

    // Enqueue styles and scripts here
    // wp_enqueue_style('alc_admin_css', ALC_PLUGIN_URL . 'path/to/css');
    // wp_enqueue_script('alc_admin_js', ALC_PLUGIN_URL . 'path/to/js', array('jquery'), '', true);
}
add_action('admin_enqueue_scripts', 'alc_admin_enqueue_scripts');
