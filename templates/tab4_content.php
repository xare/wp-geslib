<?php
        global $wpdb;

        $table_name = $wpdb->prefix .'geslib_lines';
        $query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
        $results = $wpdb->get_results($query, ARRAY_A);
      ?>

      <div class="table-container">
          <?php if (!empty($results)) :
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
            ?>
              <table class="geslib-table">
                <thead>
                  <tr>
                    <?php foreach ($paged_results[0] as $column => $value) : ?>
                      <th<?php if( $column === 'content' ){ ?> class="content-width"<?php } ?>><?php echo esc_html($column); ?></th>
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
              <?php if ($total_pages > 1) : ?>
                <div class="pagination-container">
                    <?php
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
              <?php endif;
                else : ?>
                  <p>No data found.</p>
              <?php endif; ?>
        </div>
