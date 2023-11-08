<?php
    global $wpdb;
    $queueTable = $wpdb->prefix . 'geslib_queues'; // Replace with your actual table name
    // Prepare SQL to count the number of each type of task
    $sql = "SELECT type, COUNT(*) as count FROM {$queueTable} GROUP BY type";
    $results = $wpdb->get_results($sql, ARRAY_A);

    $summary = [];
    if( !empty( $results ) ) {
        // Count the number of each type of task
        foreach ($results as $row) {
            $summary[$row['type']] = $row['count'];
        }
    } ?>
    <div class="table-container">
    <h2>Geslib Queue Summary</h2>

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
