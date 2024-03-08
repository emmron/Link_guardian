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
    public static function get_broken_links($per_page = 5, $page_number = 1) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alc_broken_links';

        $sql = "SELECT * FROM $table_name";

        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * Delete a broken link record.
     */
    public static function delete_broken_link($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alc_broken_links';

        $wpdb->delete($table_name, ['id' => $id], ['%d']);
    }

    /**
     * Returns the count of records in the database.
     */
    public static function record_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alc_broken_links';

        $sql = "SELECT COUNT(*) FROM $table_name";

        return $wpdb->get_var($sql);
    }

    /**
     * Text displayed when no broken link data is available
     */
    public function no_items() {
        _e('No broken links detected.', 'advanced-link-checker');
    }

    /**
     * Render a column when no column specific method exists.
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'url':
            case 'status_code':
            case 'associated_post':
            case 'detection_date':
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Columns to make sortable.
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'url'            => ['url', true],
            'status_code'    => ['status_code', false],
            'detection_date' => ['detection_date', false]
        ];

        return $sortable_columns;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('links_per_page', 5);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $this->items = self::get_broken_links($per_page, $current_page);
    }

    /**
     * Columns to show in the table
     */
    function get_columns() {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'url'             => __('URL', 'advanced-link-checker'),
            'status_code'     => __('Status Code', 'advanced-link-checker'),
            'associated_post' => __('Associated Post', 'advanced-link-checker'),
            'detection_date'  => __('Detection Date', 'advanced-link-checker')
        ];

        return $columns;
    }

    /**
     * Render the bulk edit checkbox
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     * Handles the bulk actions: delete.
     */
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (!wp_verify_nonce($nonce, 'alc_delete_broken_link')) {
                die('Go get a life script kiddies');
            }
            else {
                self::delete_broken_link(absint($_GET['link']));

                // redirect after deleting
                wp_redirect(esc_url(add_query_arg()));
                exit;
            }
        }

        // If the delete_bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
             || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            $delete_ids = esc_sql($_POST['bulk-delete']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_broken_link($id);
            }

            // redirect after deleting
            wp_redirect(esc_url(add_query_arg()));
            exit;
        }
    }
}

function alc_render_link_table() {
    $linkTable = new ALC_Link_Table();
    $linkTable->prepare_items();
    ?>
    <div class="wrap">
        <h2><?php _e('Broken Links', 'advanced-link-checker'); ?></h2>
        <form method="post">
            <?php
            $linkTable->display();
            ?>
        </form>
    </div>
    <?php
}

alc_render_link_table();
