<?php
namespace Inc\Geslib\Api;

use WP_List_Table;
use Inc\Geslib\Api\GeslibApiDbManager;

class GeslibQueuesListTable extends WP_List_Table {

    private $geslibApiDbLinesManager;

    public function __construct() {
        parent::__construct([
            'singular' => 'geslib_queues',  // Singular label of the table
            'plural'   => 'geslib_queues', // Plural label of the table
            'ajax'     => true             // Does this table support ajax?
        ]);

    }
    public function prepare_items() {
        global $wpdb;
        $cegalApiDbLinesManager = new GeslibApiDbQueueManager;
        $linesTable = $wpdb->prefix . $cegalApiDbLinesManager::GESLIB_QUEUES_TABLE;
        // define data set for WP_List_Table => data
        $where = ''; // Initialize where clause
        $filters = [
            'log_id',
            'geslib_id',
            'type',
            'entity',
            'action',
        ];
        foreach($filters as $filtername){
            $postdata = isset($_POST[ 'filter_'.$filtername ]) ? $_POST[ 'filter_'.$filtername ] : '';
            if ( !empty( $postdata )) {
                $value = (is_integer($postdata)) ? (int) $postdata : sanitize_text_field($postdata);
                $where = $wpdb->prepare(' WHERE '.$filtername.' = %s', $value);
            }
        }

        $orderby = isset( $_GET['orderby'] ) ? trim( $_GET['orderby'] ): "id";
        $order = isset( $_GET['order'] ) ? trim( $_GET['order'] ): "ASC";

        $search_term = isset($_POST['s'])? trim($_POST['s']) : "";
        if ($search_term) {
            $search_term = sanitize_text_field($search_term);
            $where = " WHERE log_id = {$search_term}
                        OR geslib_id = {$search_term}
                        OR type LIKE '%{$search_term}%'
                        OR entity LIKE '%{$search_term}%'
                        OR action LIKE '%{$search_term}%'
                        OR data LIKE '%{$search_term}%'
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
        $geslibApiDbManager = new GeslibApiDbManager;
        // Determine what page the user is currently looking at
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $table = $wpdb->prefix . $geslibApiDbManager::GESLIB_QUEUES_TABLE;
        $sql_data = $wpdb->prepare("SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}");
        return  $wpdb->get_results($sql_data, ARRAY_A);
    }

    public function get_columns() {
        $columns = [
            'id' => 'ID',
            'geslib_id' => 'Geslib ID',
            'log_id' => 'Log ID',
            'entity' => 'Entity',
            'type' => 'Type',
            'action' => 'Action',
            'data' => 'data',
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
            'type' => ['type', false],
            'entity' => ['entity', false],
            'action' => ['action', false],
            'data' => ['data', false],
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
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
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
            'type' => $item[$column_name],
            'action' => $item[$column_name],
            'data' => $item[$column_name],
            default => 'no value',
        };
    }
}