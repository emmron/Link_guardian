<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . '../helpers.php';

/**
 * Email Notifications Handler for Advanced Link Checker Plugin
 *
 * This file is responsible for managing the email notifications sent to administrators
 * or specific user roles when broken links are detected.
 */

/**
 * Schedule email notifications for broken links.
 *
 * This function checks for newly detected broken links and schedules email notifications
 * based on the configured settings (e.g., instantly, daily, weekly).
 */
function alc_schedule_email_notifications() {
    // Check if email notifications are enabled
    if (!ALC_NOTIFY_EMAIL) {
        return;
    }

    // Determine the frequency of email notifications
    $frequency = get_option('alc_email_notification_frequency', 'daily');

    // Schedule the email based on the frequency
    if (!wp_next_scheduled('alc_send_email_notifications')) {
        switch ($frequency) {
            case 'instantly':
                wp_schedule_single_event(time(), 'alc_send_email_notifications');
                break;
            case 'daily':
                wp_schedule_event(strtotime('tomorrow'), 'daily', 'alc_send_email_notifications');
                break;
            case 'weekly':
                wp_schedule_event(strtotime('next Monday'), 'weekly', 'alc_send_email_notifications');
                break;
        }
    }
}

add_action('alc_send_email_notifications', 'alc_send_email_notifications_handler');

/**
 * Handler function to send email notifications.
 *
 * This function retrieves the list of newly detected broken links and sends an email
 * notification to the configured recipients.
 */
function alc_send_email_notifications_handler() {
    global $wpdb;
    $alc_table_name = $wpdb->prefix . ALC_TABLE_NAME;

    // Retrieve broken links detected since the last email was sent
    $query = "SELECT * FROM {$alc_table_name} WHERE notification_sent = 0";
    $brokenLinks = $wpdb->get_results($query);

    // Send email notifications if broken links are found
    if (!empty($brokenLinks)) {
        alc_send_email_notifications($brokenLinks);

        // Mark these broken links as having notifications sent
        $wpdb->query("UPDATE {$alc_table_name} SET notification_sent = 1 WHERE notification_sent = 0");
    }
}

/**
 * Initialize the email notification functionality.
 */
function alc_init_email_notifications() {
    // Hook into WordPress to schedule and send email notifications
    add_action('wp', 'alc_schedule_email_notifications');
}

alc_init_email_notifications();
