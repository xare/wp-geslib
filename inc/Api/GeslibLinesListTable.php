<?php
namespace Inc\Geslib\Api;

use WP_List_Table;
use Inc\Geslib\Api\GeslibApiDbManager;

class GeslibLinesListTable extends WP_List_Table {

    private $geslibApiDbLinesManager;

    public function __construct() {
        parent::__construct([
            'singular' => 'geslib_lines',  // Singular label of the table
            'plural'   => 'geslib_lines', // Plural label of the table
            'ajax'     => true             // Does this table support ajax?
        ]);

    }
    public function prepare_items() {
        global $wpdb;
        $cegalApiDbLinesManager = new GeslibApiDbLinesManager;
        $linesTable = $wpdb->prefix . $cegalApiDbLinesManager::GESLIB_LINES_TABLE;
        // define data set for WP_List_Table => data
        $where = ''; // Initialize where clause
        if ( isset( $_POST['filter_log_id'] ) && !empty( $_POST['filter_log_id'] )) {
            $log_id = (int) $_POST['filter_log_id'];
            $where = $wpdb->prepare(' WHERE log_id = %d', $log_id );
        }
        if ( isset( $_POST['filter_geslib_id'] ) && !empty( $_POST['filter_geslib_id'] )) {
            $geslib_id = (int)  $_POST['filter_geslib_id'] ;
            $where = $wpdb->prepare(' WHERE geslib_id = %d', $geslib_id);
        }
        if ( isset( $_POST['filter_entity'] ) && !empty( $_POST['filter_entity'] )) {
            $entity = sanitize_text_field($_POST['filter_entity']);
            $where = $wpdb->prepare(' WHERE entity = %s', $entity);
        }
        if ( isset( $_POST['filter_action']) && !empty( $_POST['filter_action'] )) {
            $action = sanitize_text_field($_POST['filter_action']);
            $where = $wpdb->prepare(' WHERE action = %s', $action);
        }
        if ( isset( $_POST['filter_queued'] ) && !empty( $_POST['filter_queued'] )) {
            $queued = sanitize_text_field( $_POST['filter_queued'] );
            $where = $wpdb->prepare( ' WHERE queued = %s', $queued );
        }

        $orderby = isset( $_GET['orderby'] ) ? trim( $_GET['orderby'] ): "id";
        $order = isset( $_GET['order'] ) ? trim( $_GET['order'] ): "ASC";

        $search_term = isset($_POST['s'])? trim($_POST['s']) : "";
        if ($search_term) {
            $search_term = sanitize_text_field($search_term);
            $where = " WHERE log_id = %{$search_term}%
                        OR geslib_id = {$search_term}
                        OR entity LIKE '%{$search_term}%'
                        OR action LIKE '%{$search_term}%'
                        OR content LIKE '%{$search_term}%'
                        OR queued LIKE '%{$search_term}%'";
        }
        // First, get the total count of items
        $sql_count = "SELECT COUNT(*) FROM {$linesTable} {$where}";
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

        $table = $wpdb->prefix . $cegalApiDbManager::GESLIB_LINES_TABLE;
        $sql_data = $wpdb->prepare("SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}");
        return  $wpdb->get_results($sql_data, ARRAY_A);
    }

    public function get_columns() {
        $columns = [
            'id' => 'ID',
            'geslib_id' => 'Geslib ID',
            'log_id' => 'Log ID',
            'entity' => 'Entity',
            'action' => 'Action',
            'content' => 'Content',
            'queued' => 'Queued',
        ];
        return $columns;
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_sortable_columns() {
        return [
            'id' => ['id', false],
            'log_id' => ['log_id', false],
            'geslib_id' => ['geslib_id', false],
            'entity' => ['entity', false],
            'content' => ['content', false],
            'queued' => ['queued', false],
        ];
    }

    /**
     * A description of the entire PHP function.
     *
     * @param $item description
     * @param $column_name description
     * @return Some_Return_Value
     */
    public function column_default( $item, $column_name ) {
        $cegalApiDbLinesManager = new GeslibApiDbLinesManager;
        /* if ( $item['book_id'] == null || !$item['book_id'] ) {
            $book_id = 0;
        } else {
            $book_id = (int) $item['book_id'];
        }
        $assignButton = "<button
                            type='submit'
                            data-action='assign-to-product'
                            data-isbn='" . $item['isbn'] . "'
                            class='button button-primary'>
                                Asignar
                            </button />"; */
        return match($column_name) {
            'id' => $item[$column_name],
            'log_id' => $item[$column_name],
            'geslib_id' => $item[$column_name],
            'entity' => $item[$column_name],
            'action' => $item[$column_name],
            'content' => $item[$column_name],
            'queued' => $item[$column_name],
            default => 'no value',
        };
    }
}