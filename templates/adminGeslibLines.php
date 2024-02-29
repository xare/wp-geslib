<?php
use Inc\Geslib\Api\GeslibLinesListTable;
?>

<div class="wrap">
    <h1>Geslib LINES</h1>
    <?php settings_errors(); ?>
    <!-- FILTERS -->
    <?php
        global $wpdb;
        $logTable = $wpdb->prefix . 'geslib_lines';

        // Fetch distinct types
        $log_id_sql = "SELECT DISTINCT log_id FROM {$logTable}";
        $log_ids = $wpdb->get_col($log_id_sql);

        $geslib_id_sql = "SELECT DISTINCT geslib_id FROM {$logTable}";
        $geslib_ids = $wpdb->get_col($geslib_id_sql);

        $entity_sql = "SELECT DISTINCT entity FROM {$logTable}";
        $entities = $wpdb->get_col($entity_sql);

        $action_sql = "SELECT DISTINCT action FROM {$logTable}";
        $factions = $wpdb->get_col($action_sql);

        $content_sql = "SELECT DISTINCT content FROM {$logTable}";
        $content_count = $wpdb->get_col($content_sql);

        $queued_sql = "SELECT DISTINCT queued FROM {$logTable}";
        $queued = $wpdb->get_col($queued_sql);
        ?>
    <form method="post">
        <select name="filter_log_id"></select>
        <select name="filter_geslib_id"></select>
        <select name="filter_entity"></select>
        <select name="filter_action"></select>
        <select name="filter_content"></select>
        <select name="filter_queued"></select>
    </form>
    <!-- Page display -->
    <?php
        $wp_list_table = new GeslibLinesListTable;
        $wp_list_table->prepare_items();
        // Render the table
        echo "<form method='post' name='geslib_lines_search' action='".$_SERVER['PHP_SELF']."?page=geslib_lines'>";
        $wp_list_table->search_box("Geslib Lines Search", "search_geslib_lines");
        echo "</form>";
        $wp_list_table->display();
    ?>

</div>