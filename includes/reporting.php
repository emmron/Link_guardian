<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

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
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $query = "SELECT * FROM $table_name ORDER BY detection_time DESC";
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
    $table_name = $wpdb->prefix . ALC_TABLE_NAME;

    $total_broken_links = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $status_code_counts = $wpdb->get_results("SELECT status_code, COUNT(*) as count FROM $table_name GROUP BY status_code ORDER BY count DESC", ARRAY_A);
    $post_broken_links = $wpdb->get_results("SELECT post_id, COUNT(*) as count FROM $table_name GROUP BY post_id ORDER BY count DESC LIMIT 10", ARRAY_A);

    return [
        'total_broken_links' => $total_broken_links ? $total_broken_links : 0,
        'status_code_counts' => $status_code_counts ? $status_code_counts : array(),
        'post_broken_links'  => $post_broken_links ? $post_broken_links : array(),
    ];
}

/**
 * Exports broken link report to CSV.
 *
 * @param array $report_data The report data to be exported.
 */
function alc_export_report_to_csv($report_data) {
    $filename = 'broken-links-report-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, ['URL', 'Status Code', 'Post ID', 'Post Title', 'Detection Time']);

    foreach ($report_data as $row) {
        $post_title = '';
        if (!empty($row['post_id'])) {
            $post = get_post($row['post_id']);
            $post_title = $post ? $post->post_title : 'Unknown';
        }

        fputcsv($output, [
            $row['url'],
            $row['status_code'],
            $row['post_id'],
            $post_title,
            $row['detection_time'],
        ]);
    }

    fclose($output);
    exit;
}

/**
 * Renders an HTML visualization of broken link statistics.
 *
 * @param array $statistics The statistics data for visualization.
 * @return string HTML output for the visualization.
 */
function alc_visualize_data($statistics) {
    if (empty($statistics) || empty($statistics['status_code_counts'])) {
        return '<p>' . __('No data available for visualization.', 'advanced-link-checker') . '</p>';
    }

    $output = '<div class="alc-chart-container">';
    $output .= '<h3>' . __('Status Code Distribution', 'advanced-link-checker') . '</h3>';

    $max_count = 0;
    foreach ($statistics['status_code_counts'] as $status) {
        if ($status['count'] > $max_count) {
            $max_count = $status['count'];
        }
    }

    foreach ($statistics['status_code_counts'] as $status) {
        $percentage = $max_count > 0 ? ($status['count'] / $max_count) * 100 : 0;
        $output .= '<div class="alc-chart-bar">';
        $output .= '<span class="alc-chart-label">' . esc_html($status['status_code']) . '</span>';
        $output .= '<div class="alc-chart-fill" style="width: ' . esc_attr($percentage) . '%;"></div>';
        $output .= '<span class="alc-chart-count">' . esc_html($status['count']) . '</span>';
        $output .= '</div>';
    }

    $output .= '</div>';

    // Posts with most broken links
    if (!empty($statistics['post_broken_links'])) {
        $output .= '<div class="alc-chart-container">';
        $output .= '<h3>' . __('Posts with Most Broken Links', 'advanced-link-checker') . '</h3>';

        $max_post_count = 0;
        foreach ($statistics['post_broken_links'] as $post_data) {
            if ($post_data['count'] > $max_post_count) {
                $max_post_count = $post_data['count'];
            }
        }

        foreach ($statistics['post_broken_links'] as $post_data) {
            $post = get_post($post_data['post_id']);
            $post_title = $post ? $post->post_title : __('Unknown Post', 'advanced-link-checker') . ' #' . $post_data['post_id'];
            $percentage = $max_post_count > 0 ? ($post_data['count'] / $max_post_count) * 100 : 0;

            $output .= '<div class="alc-chart-bar">';
            $output .= '<span class="alc-chart-label" title="' . esc_attr($post_title) . '">' . esc_html(wp_trim_words($post_title, 3, '...')) . '</span>';
            $output .= '<div class="alc-chart-fill" style="width: ' . esc_attr($percentage) . '%; background: #826eb4;"></div>';
            $output .= '<span class="alc-chart-count">' . esc_html($post_data['count']) . '</span>';
            $output .= '</div>';
        }

        $output .= '</div>';
    }

    return $output;
}
