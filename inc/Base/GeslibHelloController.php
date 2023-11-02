<?php

namespace Inc\Geslib\Base;

use Inc\Geslib\Api\Callbacks\AdminCallbacks;
use Inc\Geslib\Api\GeslibApiDbManager;

class GeslibHelloController extends BaseController {

    private $db;
    private $callbacks;

    public function register() {
        $this->db = new GeslibApiDbManager();
        $this->callbacks = new AdminCallbacks();
        add_shortcode( 'geslib_hello', [ $this, 'render_hello_message' ] );
        add_shortcode( 'geslib_table', [ $this, 'render_table' ] );
        add_action( 'wp_ajax_geslib_pagination', [ $this, 'geslib_pagination_handler' ] );
        add_action( 'wp_ajax_nopriv_geslib_pagination', [ $this, 'geslib_pagination_handler' ] );
        
    }
    /**
     * Render the hello message
     *
     * @param array $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered output
     */
    public function render_hello_message( $atts = null, $content = null ) {
        ob_start();
        echo 'Hello, World!';
        return ob_get_clean();
    }

    public function render_table($atts = null, $content = null ) {
        $atts = shortcode_atts([
            'table' => 'log' // Default table name if not provided
        ], $atts );
    
        $tableName = $atts['table'];
        
        /* ob_start(); */
        return $this->db->getGeslibTable( $tableName ) ;
        /* return ob_get_clean(); */
    }

    // AJAX handler for pagination
    function geslib_pagination_handler() {
        $results_per_page = 30; // Number of results to display per page
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $results = $this->db->getGeslibTable('lines');
        // Calculate the offset for the query
        $offset = ($page - 1) * $results_per_page;

        // Get a subset of results for the current page
        $paged_results = array_slice($results, $offset, $results_per_page);

        ob_start();

        // Render the paginated results
        ?>
        <table class="geslib-table">
            <thead>
                <tr>
                    <?php foreach ($paged_results[0] as $column => $value) : ?>
                        <th><?php echo esc_html($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paged_results as $row) : ?>
                    <tr>
                        <?php foreach ($row as $value) : ?>
                            <td><?php echo esc_html($value); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination-container">
            <?php
            $results_per_page = 30; // Number of results to display per page
            $total_results = count($results); // Total number of results
  
            // Calculate the number of pages
            $total_pages = ceil($total_results / $results_per_page);
  
            // Get the current page number
            $current_page = isset($_GET['page']) ? absint($_GET['page']) : 1;
  
            // Calculate the offset for the query
            $offset = ($current_page - 1) * $results_per_page;
  
            // Get a subset of results for the current page
            $paged_results = array_slice($results, $offset, $results_per_page);
            // Output pagination links
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<a href="' . esc_url(add_query_arg('page', $i)) . '"';
                if ($i === $current_page) {
                    echo ' class="current"';
                }
                echo '>' . $i . '</a>';
            }
            ?>
        </div>
        <?php

        $output = ob_get_clean();

        echo $output;

        wp_die(); // Terminate the script
    }




}