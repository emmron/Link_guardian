<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Email Notifications Handler for Advanced Link Checker Plugin
 *
 * This file is responsible for managing the email notifications sent to administrators
 * or specific user roles when broken links are detected.
 */

/**
 * Schedule email notifications for broken links.
 */
function alc_schedule_email_notifications() {
    $options = get_option('alc_options', array());
    $notify_enabled = isset($options['notify_email']) ? $options['notify_email'] : ALC_NOTIFY_EMAIL;

    if (!$notify_enabled) {
        return;
    }

    $frequency = isset($options['notify_frequency']) ? $options['notify_frequency'] : 'daily';

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
 */
function alc_send_email_notifications_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $options = get_option('alc_options', array());
    $threshold = isset($options['notify_threshold']) ? intval($options['notify_threshold']) : ALC_NOTIFY_THRESHOLD;

    // Retrieve broken links that haven't been notified about yet
    $brokenLinks = $wpdb->get_results("SELECT * FROM {$table_name} WHERE notification_sent = 0");

    // Only send if we meet the threshold
    if (!empty($brokenLinks) && count($brokenLinks) >= $threshold) {
        alc_send_email_notifications($brokenLinks);

        // Mark these broken links as having notifications sent
        $wpdb->query("UPDATE {$table_name} SET notification_sent = 1 WHERE notification_sent = 0");
    }
}

/**
 * Initialize the email notification functionality.
 */
function alc_init_email_notifications() {
    add_action('wp', 'alc_schedule_email_notifications');
}

alc_init_email_notifications();
