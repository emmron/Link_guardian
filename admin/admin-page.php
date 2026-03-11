<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Admin Page for Advanced Link Checker Plugin
 *
 * This file is responsible for creating and managing the admin page for the Advanced Link Checker plugin.
 * It includes functions to register the admin menu, display the admin page content, and handle user interactions.
 */

/**
 * Register the admin menu for the Advanced Link Checker plugin.
 */
function alc_register_admin_menu() {
    add_menu_page(
        __('Advanced Link Checker', 'advanced-link-checker'),
        __('Link Checker', 'advanced-link-checker'),
        'manage_options',
        'advanced-link-checker',
        'alc_admin_page_display',
        'dashicons-admin-links',
        100
    );
}
add_action('admin_menu', 'alc_register_admin_menu');

/**
 * Display the admin page content.
 */
function alc_admin_page_display() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'advanced-link-checker'));
    }

    // Handle form submission
    if (isset($_POST['alc_action'])) {
        check_admin_referer('alc_manage_links');

        $action = sanitize_text_field($_POST['alc_action']);
        switch ($action) {
            case 'recheck_all':
                alc_recheck_all_links();
                echo '<div class="notice notice-success"><p>' . __('All links have been rechecked.', 'advanced-link-checker') . '</p></div>';
                break;
            case 'scan_now':
                alc_perform_scan();
                echo '<div class="notice notice-success"><p>' . __('Scan completed successfully.', 'advanced-link-checker') . '</p></div>';
                break;
        }
    }

    // Get statistics
    $statistics = alc_get_statistics();

    // Prepare the list table
    $linkTable = new ALC_Link_Table();
    $linkTable->prepare_items();

    ?>
    <div class="wrap alc-admin-wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <!-- Statistics -->
        <div class="alc-stats-row">
            <div class="alc-stat-box <?php echo $statistics['total_broken_links'] > 0 ? 'error' : 'success'; ?>">
                <h3><?php _e('Total Broken Links', 'advanced-link-checker'); ?></h3>
                <div class="alc-stat-number"><?php echo esc_html($statistics['total_broken_links']); ?></div>
            </div>
            <?php if (!empty($statistics['status_code_counts'])) : ?>
                <?php foreach (array_slice($statistics['status_code_counts'], 0, 3) as $status) : ?>
                <div class="alc-stat-box warning">
                    <h3><?php printf(__('Status %s', 'advanced-link-checker'), esc_html($status['status_code'])); ?></h3>
                    <div class="alc-stat-number"><?php echo esc_html($status['count']); ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Status Code Distribution Chart -->
        <?php if (!empty($statistics['status_code_counts'])) : ?>
        <div class="alc-chart-container">
            <h3><?php _e('Status Code Distribution', 'advanced-link-checker'); ?></h3>
            <?php
            $max_count = max(array_column($statistics['status_code_counts'], 'count'));
            foreach ($statistics['status_code_counts'] as $status) :
                $percentage = $max_count > 0 ? ($status['count'] / $max_count) * 100 : 0;
            ?>
            <div class="alc-chart-bar">
                <span class="alc-chart-label"><?php echo esc_html($status['status_code']); ?></span>
                <div class="alc-chart-fill" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                <span class="alc-chart-count"><?php echo esc_html($status['count']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Actions Bar -->
        <div class="alc-actions-bar">
            <form method="post" style="display:inline;">
                <?php wp_nonce_field('alc_manage_links'); ?>
                <input type="hidden" name="alc_action" value="scan_now" />
                <button type="submit" class="button button-primary"><?php _e('Scan Now', 'advanced-link-checker'); ?></button>
            </form>
            <form method="post" style="display:inline;">
                <?php wp_nonce_field('alc_manage_links'); ?>
                <input type="hidden" name="alc_action" value="recheck_all" />
                <button type="submit" class="button"><?php _e('Recheck All Links', 'advanced-link-checker'); ?></button>
            </form>
            <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=alc_export_csv&security=' . wp_create_nonce('alc_export_nonce'))); ?>" class="button"><?php _e('Export CSV', 'advanced-link-checker'); ?></a>
        </div>

        <!-- Links Table -->
        <form method="post">
            <?php
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
    if ($hook != 'toplevel_page_advanced-link-checker') {
        return;
    }

    wp_enqueue_style('alc-admin-css', ALC_PLUGIN_URL . 'admin/css/admin-style.css', array(), ALC_VERSION);
    wp_enqueue_script('alc-admin-js', ALC_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), ALC_VERSION, true);

    wp_localize_script('alc-admin-js', 'alc_admin', array(
        'ajax_url'        => admin_url('admin-ajax.php'),
        'recheck_nonce'   => wp_create_nonce('alc_recheck_nonce'),
        'resolve_nonce'   => wp_create_nonce('alc_resolve_nonce'),
        'export_nonce'    => wp_create_nonce('alc_export_nonce'),
        'rechecking_text' => __('Rechecking...', 'advanced-link-checker'),
    ));
}
add_action('admin_enqueue_scripts', 'alc_admin_enqueue_scripts');
