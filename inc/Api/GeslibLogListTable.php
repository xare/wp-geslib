<?php
namespace Inc\Geslib\Api;

use WP_List_Table;
use Inc\Geslib\Api\GeslibApiDbManager;

class GeslibLogListTable extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'geslib_log',  // Singular label of the table
            'plural'   => 'geslib_logs', // Plural label of the table
            'ajax'     => true             // Does this table support ajax?
        ]);

    }
    public function prepare_items() {
        $this->process_bulk_action();
        global $wpdb;
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $logTable = $wpdb->prefix . $geslibApiDbLogManager::GESLIB_LOG_TABLE;
        // define data set for WP_List_Table => data
        $where = ''; // Initialize where clause
        if ( isset( $_POST['filter_start_date'] ) && !empty( $_POST['filter_start_date'] )) {
            $start_date = sanitize_text_field( $_POST['filter_start_date'] );
            $where = $wpdb->prepare(' WHERE start_date = %s', $start_date );
        }
        if ( isset( $_POST['filter_end_date'] ) && !empty( $_POST['filter_end_date'] )) {
            $end_date = sanitize_text_field( $_POST['filter_end_date'] );
            $where = $wpdb->prepare(' WHERE end_date = %s', $end_date);
        }
        if ( isset( $_POST['filter_status'] ) && !empty( $_POST['filter_status'] )) {
            $status = sanitize_text_field($_POST['filter_status']);
            $where = $wpdb->prepare(' WHERE status = %s', $status);
        }
        if ( isset( $_POST['filter_filenames']) && !empty( $_POST['filter_filenames'] )) {
            $filenames = sanitize_text_field($_POST['filter_filenames']);
            $where = $wpdb->prepare(' WHERE filename = %d', $filenames);
        }
        if ( isset( $_POST['filter_lines_count'] ) && !empty( $_POST['filter_lines_count'] )) {
            $lines_count = sanitize_text_field( $_POST['filter_lines_count'] );
            $where = $wpdb->prepare( ' WHERE lines_count = %d', $lines_count );
        }

        $orderby = isset( $_GET['orderby'] ) ? trim( $_GET['orderby'] ): "id";
        $order = isset( $_GET['order'] ) ? trim( $_GET['order'] ): "ASC";

        $search_term = isset($_POST['s'])? trim($_POST['s']) : "";
        if ($search_term) {
            $search_term = sanitize_text_field($search_term);
            $where = " WHERE start_date LIKE '%{$search_term}%'
                                        OR end_date LIKE '%{$search_term}%'
                                        OR status LIKE '%{$search_term}%'
                                        OR filename = {$search_term}
                                        OR lines_count = {$search_term}";
        }
        // First, get the total count of items
        $sql_count = "SELECT COUNT(*) FROM {$logTable} {$where}";
        $total_items = $wpdb->get_var($sql_count);

        // Set how many records per page to show
        $per_page = 20;

        // Calculate the total number of pages
        $total_pages = ceil($total_items / $per_page);


        $this->items = $this->wp_list_table_data($where, $per_page,  $orderby, $order);

        // Set the pagination arguments
        $this->set_pagination_args([
            'total_items' => $total_items, // Total number of items
            'per_page'    => $per_page,    // How many items to show on a page
            'total_pages' => $total_pages  // Total number of pages
        ]);
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns(); // Define hidden columns here if any
        $sortable = $this->get_sortable_columns(); // Define sortable columns here if any
        $this->_column_headers = [$columns, $hidden, $sortable];
    }

    public function wp_list_table_data($where = '', $per_page = 20,  $orderby = '', $order = '', $search_term = '') {
        global $wpdb;
        $cegalApiDbManager = new GeslibApiDbManager;
        // Determine what page the user is currently looking at
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $logTable = $wpdb->prefix . $cegalApiDbManager::GESLIB_LOG_TABLE;
        $sql_data = $wpdb->prepare("SELECT * FROM {$logTable} {$where} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}");
        return  $wpdb->get_results($sql_data, ARRAY_A);
    }

    public function get_columns() {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'filename' => 'Filename',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
            'lines_count' => 'Lines Count',
        ];
        return $columns;
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_sortable_columns() {
        return [
            'cb' => ['cb', false],
            'id' => ['id', true],
            'filename' => ['filename', true],
            'start_date' => ['start_date', true],
            'end_date' => ['end_date', false],
            'status' => ['status', false],
            'lines_count' => ['lines_count', false],
        ];
    }

    public function column_default( $item, $column_name ) {
        return match($column_name) {
            'cb' => $item[$column_name],
            'id' => $item[$column_name],
            'filename' => $item[$column_name],
            'start_date' => $item[$column_name],
            'end_date' => $item[$column_name],
            'status' => $item[$column_name],
            'lines_count' => $item[$column_name],
            default => 'no value',
        };
    }

    public function column_filename($item) {
        $action = [
            'update' => sprintf('<a href="?page=%s&action=%s&log_id=%s" class="update-status">Update Status</a>', $_GET['page'], 'update-status', $item['id']),
        ];
        return sprintf('%1$s %2$s', $item['filename'], $this->row_actions($action));
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
        );
    }

   /**
    * Returns an associative array containing the bulk action
    *
    * @return array
    */
    public function get_bulk_actions() {
        $actions = [
            'bulk-update-status' => 'Update Status'
        ];

        return $actions;
    }

    public function update_log(int $log_id){
        global $wpdb;
        $wpdb->update($wpdb->prefix."geslib_log",
                        [ 'status' => 'logged' ],
                        [ 'id' => $log_id ]);
    }

    public function process_bulk_action() {
        //if ( 'bulk-update-status' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            /* $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
                die( 'Go get a life script kiddies' );
            } else {
                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            } */
            // If the delete bulk action is triggered
            if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update-status' )
            || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'update-status' )
            ) {
                $log_id = $_REQUEST['log_id'];
                //foreach ($log_ids as $log_id) {
                $this->update_log($log_id);
                //}
            wp_redirect( esc_url( add_query_arg() ) );
            exit;
            }
        //}
    }
}