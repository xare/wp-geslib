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

        $status_sql = "SELECT DISTINCT status FROM {$logTable}";
        $statuses = $wpdb->get_col($status_sql);

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