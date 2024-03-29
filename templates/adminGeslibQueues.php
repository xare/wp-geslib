<?php
use Inc\Geslib\Api\GeslibQueuesListTable;
?>

<div class="wrap">
    <h1>Geslib QUEUES</h1>
    <?php settings_errors(); ?>
    <!-- FILTERS -->
    <?php
        global $wpdb;
        $table = $wpdb->prefix . 'geslib_queues';

        // Fetch distinct types
        $log_id_sql = "SELECT DISTINCT log_id FROM {$table}";
        $log_ids = $wpdb->get_col($log_id_sql);

        $geslib_id_sql = "SELECT DISTINCT geslib_id FROM {$table}";
        $geslib_ids = $wpdb->get_col($geslib_id_sql);

        $entity_sql = "SELECT DISTINCT entity FROM {$table}";
        $entities = $wpdb->get_col($entity_sql);

        $type_sql = "SELECT DISTINCT type FROM {$table}";
        $types = $wpdb->get_col($type_sql);

        $action_sql = "SELECT DISTINCT action FROM {$table}";
        $actions = $wpdb->get_col($action_sql);

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
        <select name="filter_type">
            <option value="">All Types</option>
            <?php foreach ($types as $type): ?>
                <option value="<?php echo esc_attr($type); ?>" <?php selected(isset($_POST['filter_type']) && $_POST['filter_type'] === $type); ?>>
                    <?php echo esc_html($type); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="filter_action">
            <option value="">All Actions</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?php echo esc_attr($action); ?>" <?php selected(isset($_POST['filter_action']) && $_POST['filter_action'] === $action); ?>>
                    <?php echo esc_html($action); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter"/>
    </form>
    <!-- Page display -->
    <?php
        $wp_list_table = new GeslibQueuesListTable;
        $wp_list_table->prepare_items();
        // Render the table
        echo "<form method='post' name='geslib_queues_search' action='".$_SERVER['PHP_SELF']."?page=geslib_queues'>";
        $wp_list_table->search_box("Geslib Queues Search", "search_geslib_queues");
        echo "</form>";
        $wp_list_table->display();
    ?>

</div>