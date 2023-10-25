<?php

$queue = get_option('geslib_queue', []);

    $summary = [];
    if(!empty($queue)) {
        // Count the number of each type of task
        foreach ($queue as $task) {
            $type = $task['type'];
            if (!isset($summary[$type])) {
                $summary[$type] = 0;
            }
            $summary[$type]++;
        }
    } ?>
    <div class="table-container">
    <h2>Geslib Queue Summary</h2>

    <?php
    if (!empty($summary)) { ?>
        <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Type</th><th>Count</th></tr></thead>
        <tbody>
        <?php foreach ($summary as $type => $count) {
            echo "<tr><td>{$type}</td><td>{$count}</td></tr>";
        } ?>
        </tbody>
       </table>
    <?php } else {
        echo '<p>The queue is empty.</p>';
    } ?>
    </div>
