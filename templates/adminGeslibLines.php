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
        <select name="filter_log_id">
            <option value="">All Logs</option>
            <?php foreach ($log_ids as $log_id): ?>
                <option value="<?php echo esc_attr($log_id); ?>" <?php selected(isset($_POST['filter_log_id']) && $_POST['filter_log_id'] === $log_id); ?>>
                    <?php echo esc_html($log_id); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="filter_geslib_id">
            <option value="">All Geslib</option>
            <?php foreach ($geslib_ids as $geslib_id): ?>
                <option value="<?php echo esc_attr($geslib_id); ?>" <?php selected(isset($_POST['filter_geslib_id']) && $_POST['filter_geslib_id'] === $geslib_id); ?>>
                    <?php echo esc_html($geslib_id); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="filter_entity">
            <option value="">All Entities</option>
            <?php foreach ($entities as $entity): ?>
                <option value="<?php echo esc_attr($entity); ?>" <?php selected(isset($_POST['filter_entity']) && $_POST['filter_entity'] === $entity); ?>>
                    <?php echo esc_html($entity); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="filter_action">
            <option value="">All Actions</option>
            <?php foreach ($factions as $action): ?>
                <option value="<?php echo esc_attr($action); ?>" <?php selected(isset($_POST['filter_action']) && $_POST['filter_action'] === $action); ?>>
                    <?php echo esc_html($action); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="filter_content">
        </select>
        <select name="filter_queued">
            <option value="">All Queued</option>
            <?php foreach ($queued as $queued): ?>
                <option value="<?php echo esc_attr($queued); ?>" <?php selected(isset($_POST['filter_queued']) && $_POST['filter_queued'] === $queued); ?>>
                    <?php echo esc_html($queued); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter"/>
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