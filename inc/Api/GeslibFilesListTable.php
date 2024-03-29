<?php
namespace Inc\Geslib\Api;
use WP_List_Table;

class GeslibFilesListTable extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => 'geslib_file',  // Singular label of the table
            'plural'   => 'geslib_filess', // Plural label of the table
            'ajax'     => true             // Does this table support ajax?
        ]);
    }

    public function prepare_items() {

        // Set how many records per page to show
        $per_page = 20;

        // Calculate the total number of pages

        $this->items = $this->wp_list_table_data($per_page)['items'];
        $total_items = $this->wp_list_table_data($per_page)['total'];
        $total_pages = ceil($total_items / $per_page);
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages  // Total number of pages
        ]);
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
    }

    public function wp_list_table_data($per_page = 20,  $search_term = '') {
        $geslibApiReadFiles = new GeslibApiReadFiles;
        $mainFolderPath = WP_CONTENT_DIR . '/uploads/' . get_option('geslib_settings')['geslib_folder_index'] .'/';
        $files = glob($mainFolderPath . 'INTER*');
        $data = [];
        foreach ($files as $file) {
            if( !isset( $file ) || $file === '' ) continue;
            // Get file modification time
            $modTime = filemtime($file);
            // Format the date and time
            $formattedModTime = date('d/m/Y H:i', $modTime);
            // Get file size and format it
            $formattedSize = $this->formatSize(filesize($file));

            $countLines = $geslibApiReadFiles->countLines($file);
            $lineCounts = $geslibApiReadFiles->countLinesWithGP4($file);

            $data[] = [
                'file' => basename($file),
                'date' => $formattedModTime,
                'size' => $formattedSize,
                'count_lines' => $countLines,
                'gp4a' => $lineCounts['GP4A'],
                'gp4m' => $lineCounts['GP4M'],
                'gp4b' => $lineCounts['GP4B'],
                '1la' => $lineCounts['1LA'],
                '1lm' => $lineCounts['1LM'],
                '1lb' => $lineCounts['1LB'],
                '3a' => $lineCounts['3A'],
                '3m' => $lineCounts['3M'],
                '3b' => $lineCounts['3B'],
            ];
        }
        // Determine what page the user is currently looking at
        $offset = ($this->get_pagenum() - 1) * $per_page;
        return [
            'items' => array_slice($data, $offset, $per_page),
            'total' => count($data),
        ];
    }

    public function get_columns() {
        $columns = [
            'file' => 'Archivo',
            'date' => 'Fecha creación',
            'size' => 'Memoria',
            'count_lines' => 'Número de lineas',
            'gp4a' => 'Producto Creado',
            'gp4m' => 'Modificado',
            'gp4b' => 'Borrado',
            '1la' => 'Editorial Creada',
            '1lm' => 'Modificada',
            '1lb' => 'Borrada',
            '3a' => 'Categoría Creada',
            '3m' => 'Modificada',
            '3b' => 'Borrada',
        ];
        return $columns;
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_sortable_columns() {
        return [
            'file' => ['Archivo', false],
            'date' => ['Fecha creación', false],
            'size' => ['Memoria', false],
            'count_lines' => ['Número de lineas', false],
            'gp4a' => ['Producto Creado', false],
            'gp4m' => ['Modificado', false],
            'gp4b' => ['Borrado', false],
            '1la' => ['Editorial Creada', false],
            '1lm' => ['Modificada', false],
            '1lb' => ['Borrada', false],
            '3a' => ['Categoría Creada', false],
            '3m' => ['Modificada', false],
            '3b' => ['Borrada', false],
        ];
    }
    public function column_default( $item, $column_name ) {
        return match($column_name) {
            'file' => $item[$column_name],
            'date' => $item[$column_name],
            'size' => $item[$column_name],
            'count_lines' => $item[$column_name],
            'gp4a' => $item[$column_name],
            'gp4m' => $item[$column_name],
            'gp4b' => $item[$column_name],
            '1la' => $item[$column_name],
            '1lm' => $item[$column_name],
            '1lb' => $item[$column_name],
            '3a' => $item[$column_name],
            '3m' => $item[$column_name],
            '3b' => $item[$column_name],
            default => 'no value',
        };
    }


    private function formatSize($bytes) {
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);
        return( round($bytes, 2) . " " . $types[$i] );
    }
}