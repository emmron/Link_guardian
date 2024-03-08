<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'db-manager.php';

/**
 * Reporting functionality for Advanced Link Checker Plugin
 *
 * This file handles the generation and management of reports regarding broken links detected on the website.
 */

/**
 * Generates a report of broken links.
 *
 * @return array An array of broken link data including URL, status code, associated post ID, and detection time.
 */
function alc_generate_report() {
    global $wpdb;
    global $alc_table_name;

    $query = "SELECT * FROM $alc_table_name ORDER BY detection_time DESC";
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}

/**
 * Provides statistics on broken links.
 *
 * @return array An array of statistics including total broken links, most common status codes, and posts with the most broken links.
 */
function alc_get_statistics() {
    global $wpdb;
    global $alc_table_name;

    $total_broken_links = $wpdb->get_var("SELECT COUNT(*) FROM $alc_table_name");
    $status_code_counts = $wpdb->get_results("SELECT status_code, COUNT(*) as count FROM $alc_table_name GROUP BY status_code ORDER BY count DESC", ARRAY_A);
    $post_broken_links = $wpdb->get_results("SELECT post_id, COUNT(*) as count FROM $alc_table_name GROUP BY post_id ORDER BY count DESC LIMIT 10", ARRAY_A);

    return [
        'total_broken_links' => $total_broken_links,
        'status_code_counts' => $status_code_counts,
        'post_broken_links' => $post_broken_links,
    ];
}

/**
 * Exports broken link report to CSV.
 *
 * @param array $report_data The report data to be exported.
 */
function alc_export_report_to_csv($report_data) {
    $filename = 'broken-links-report-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['URL', 'Status Code', 'Post ID', 'Detection Time']);

    foreach ($report_data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/**
 * Visualizes broken link data.
 *
 * @param array $statistics The statistics data for visualization.
 */
function alc_visualize_data($statistics) {
    // This function would integrate with a JavaScript library like Chart.js to visualize the data
    // For simplicity, this is a placeholder to indicate where such integration would occur.
}

// Example usage
// $report_data = alc_generate_report();
// alc_export_report_to_csv($report_data);

// $statistics = alc_get_statistics();
// alc_visualize_data($statistics);

