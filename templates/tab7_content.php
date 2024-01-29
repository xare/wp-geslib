<?php

use Inc\Geslib\Api\GeslibLoggerListTable;

    global $wpdb;
    $loggerTable = $wpdb->prefix . 'geslib_logger'; // Replace with your actual table name
    // Prepare SQL to count the number of each type of task
    $sql = "SELECT type, COUNT(*) as count FROM {$loggerTable} GROUP BY type";
    $results = $wpdb->get_results($sql, ARRAY_A);

    $summary = [];
    if( !empty( $results ) ) {
        // Count the number of each type of task
        foreach ($results as $row) {
            $summary[$row['type']] = $row['count'];
        }
    } ?>
    <div class="table-container">
        <h2>Geslib Logger Summary</h2>

        <?php
        if ( !empty( $summary ) ) { ?>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Type</th><th>Count</th></tr></thead>
                <tbody>
                <?php foreach ( $summary as $type => $count ) {
                    echo "<tr><td>{$type}</td><td>{$count}</td></tr>";
                } ?>
                </tbody>
            </table>
        <?php } else {
            echo '<p>The queue is empty.</p>';
        } ?>
    </div>
    <div class="table-container">
        <h2>Geslib Logger Summary</h2>
        <?php


            global $wpdb;
            // Prepare SQL to count the number of each type of task
            $sql2 =  $wpdb->prepare( "SELECT * FROM {$loggerTable}" );
            $results2 = $wpdb->get_results( $sql2, ARRAY_A );

            if( !empty( $results2 ) ) { ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>date</th>
                            <th>Id</th>
                            <th>log_id</th>
                            <th>geslib_id</th>
                            <th>type</th>
                            <th>action</th>
                            <th>entity</th>
                            <th>message</th>
                            <th>class</th>
                            <th>function</th>
                            <th>line</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $results_per_page = 30; // Number of results to display per page
                    $total_results = count($results2); // Total number of results

                    // Calculate the number of pages
                    $total_pages = ceil($total_results / $results_per_page);

                    // Get the current page number
                    $current_page = isset($_GET['page']) ? absint($_GET['page']) : 1;

                    // Calculate the offset for the query
                    $offset = ($current_page - 1) * $results_per_page;

                    // Get a subset of results for the current page
                    $paged_results = array_slice($results2, $offset, $results_per_page);
                        foreach ( $paged_results as $row ) {
                            $metadata = json_decode( $row['metadata'] );
                            ?>
                            <?php echo "<tr>
                                            <td>{$row['date']}</td>
                                            <td>{$row['id']}</td>
                                            <td>{$row['log_id']}</td>
                                            <td>{$row['geslib_id']}</td>
                                            <td>{$row['type']}</td>
                                            <td>{$row['action']}</td>
                                            <td>{$row['entity']}</td>
                                            <td>{$metadata->message}</td>
                                            <td>{$metadata->file}</td>
                                            <td>{$metadata->class}</td>
                                            <td>{$metadata->function}</td>
                                            <td>{$metadata->line}</td>
                                        </tr>"; ?>
                        <?php } ?>
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
        } ?>
    </div>
