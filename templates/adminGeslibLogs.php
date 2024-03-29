<?php
use Inc\Geslib\Api\GeslibLogListTable;
?>

<div class="wrap">
    <h1>Geslib LOGS</h1>
    <?php settings_errors(); ?>
    <!-- FILTERS -->
    <?php
        global $wpdb;
        $logTable = $wpdb->prefix . 'geslib_log';

        // Fetch distinct types
        $start_date_sql = "SELECT DISTINCT start_date FROM {$logTable}";
        $start_dates = $wpdb->get_col($start_date_sql);

        $end_date_sql = "SELECT DISTINCT end_date FROM {$logTable}";
        $end_dates = $wpdb->get_col($end_date_sql);

        $status_sql = "SELECT DISTINCT status FROM {$logTable}";
        $statuses = $wpdb->get_col($status_sql);

        $filenames_sql = "SELECT DISTINCT filename FROM {$logTable}";
        $filenames = $wpdb->get_col($filenames_sql);

        $lines_count_sql = "SELECT DISTINCT lines_count FROM {$logTable}";
        $lines_count = $wpdb->get_col($lines_count_sql);
        ?>
    <form method="post">
        <select name="filter_status">
            <option value="">All Status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo esc_attr($status); ?>" <?php selected(isset($_POST['filter_status']) && $_POST['filter_status'] === $status); ?>>
                    <?php echo esc_html($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter"/>
    </form>
    <!-- Page display -->
    <?php
        $wp_list_table = new GeslibLogListTable;
        $wp_list_table->prepare_items();
        // Render the table
        echo "<form method='post' name='geslib_log_search' action='".$_SERVER['PHP_SELF']."?page=geslib_log'>";
        $wp_list_table->search_box("Geslib Log Search", "search_geslib_log");
        echo "</form>";
        $wp_list_table->display();
    ?>

</div>