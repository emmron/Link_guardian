<?php
// Prevent direct access to the file
defined('ABSPATH') or die('No script kiddies please!');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ALC_Link_Table extends WP_List_Table {

    function __construct() {
        parent::__construct([
            'singular' => __('Broken Link', 'advanced-link-checker'),
            'plural'   => __('Broken Links', 'advanced-link-checker'),
            'ajax'     => false
        ]);
    }

    /**
     * Retrieve broken links from the database.
     */
    public static function get_broken_links($per_page = 20, $page_number = 1) {
        global $wpdb;
        $table_name = $wpdb->prefix . ALC_TABLE_NAME;

        $sql = "SELECT * FROM $table_name";

        if (!empty($_REQUEST['orderby'])) {
            $allowed = array('url', 'status_code', 'post_id', 'detection_time');
            $orderby = in_array($_REQUEST['orderby'], $allowed) ? $_REQUEST['orderby'] : 'detection_time';
            $order = (!empty($_REQUEST['order']) && strtoupper($_REQUEST['order']) === 'ASC') ? 'ASC' : 'DESC';
            $sql .= " ORDER BY $orderby $order";
        } else {
            $sql .= " ORDER BY detection_time DESC";
        }

        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, ($page_number - 1) * $per_page);

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * Delete a broken link record.
     */
    public static function delete_broken_link($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . ALC_TABLE_NAME;

        $wpdb->delete($table_name, ['id' => $id], ['%d']);
    }

    /**
     * Returns the count of records in the database.
     */
    public static function record_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . ALC_TABLE_NAME;

        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Text displayed when no broken link data is available.
     */
    public function no_items() {
        _e('No broken links detected. Your site looks great!', 'advanced-link-checker');
    }

    /**
     * Render a column when no column specific method exists.
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'status_code':
                $code = esc_html($item['status_code']);
                $class = 'status-' . $code;
                return '<span class="alc-status-badge ' . $class . '">' . $code . '</span>';
            case 'post_id':
                $post = get_post($item['post_id']);
                if ($post) {
                    return '<a href="' . esc_url(get_edit_post_link($item['post_id'])) . '">' . esc_html($post->post_title) . '</a>';
                }
                return __('Unknown Post', 'advanced-link-checker') . ' (#' . esc_html($item['post_id']) . ')';
            case 'detection_time':
                return esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['detection_time'])));
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    /**
     * Render the URL column with row actions.
     */
    public function column_url($item) {
        $url_display = '<a href="' . esc_url($item['url']) . '" target="_blank" rel="noopener">' . esc_html($item['url']) . '</a>';

        $actions = array(
            'recheck' => '<a href="#" class="alc-recheck-link" data-link-id="' . esc_attr($item['id']) . '">' . __('Recheck', 'advanced-link-checker') . '</a>',
            'resolve' => '<a href="#" class="alc-resolve-link" data-link-id="' . esc_attr($item['id']) . '">' . __('Resolve', 'advanced-link-checker') . '</a>',
            'delete'  => sprintf(
                '<a href="%s">%s</a>',
                wp_nonce_url(
                    add_query_arg(array('action' => 'delete', 'link' => $item['id']), admin_url('admin.php?page=advanced-link-checker')),
                    'alc_delete_broken_link'
                ),
                __('Delete', 'advanced-link-checker')
            ),
        );

        return $url_display . $this->row_actions($actions);
    }

    /**
     * Columns to make sortable.
     */
    public function get_sortable_columns() {
        return [
            'url'            => ['url', true],
            'status_code'    => ['status_code', false],
            'detection_time' => ['detection_time', false]
        ];
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('links_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = self::get_broken_links($per_page, $current_page);
    }

    /**
     * Columns to show in the table.
     */
    function get_columns() {
        return [
            'cb'             => '<input type="checkbox" />',
            'url'            => __('URL', 'advanced-link-checker'),
            'status_code'    => __('Status Code', 'advanced-link-checker'),
            'post_id'        => __('Associated Post', 'advanced-link-checker'),
            'detection_time' => __('Detection Date', 'advanced-link-checker')
        ];
    }

    /**
     * Render the bulk edit checkbox.
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     * Define bulk actions.
     */
    public function get_bulk_actions() {
        return [
            'bulk-delete' => __('Delete', 'advanced-link-checker'),
        ];
    }

    /**
     * Handles the bulk actions: delete.
     */
    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'alc_delete_broken_link')) {
                wp_die(__('Security check failed.', 'advanced-link-checker'));
            }

            if (isset($_GET['link'])) {
                self::delete_broken_link(absint($_GET['link']));
                wp_redirect(esc_url(remove_query_arg(array('action', 'link', '_wpnonce'))));
                exit;
            }
        }

        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
             || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            if (isset($_POST['bulk-delete'])) {
                $delete_ids = array_map('absint', $_POST['bulk-delete']);

                foreach ($delete_ids as $id) {
                    self::delete_broken_link($id);
                }

                wp_redirect(esc_url(remove_query_arg(array('action', 'action2'))));
                exit;
            }
        }
    }
}
