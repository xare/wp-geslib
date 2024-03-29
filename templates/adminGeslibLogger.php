<?php
use Inc\Geslib\Api\GeslibLoggerListTable;
?>

<div class="wrap">
  <h1>Geslib DASHBOARD</h1>
   <?php settings_errors(); ?>
   <?php
      global $wpdb;
      $loggerTable = $wpdb->prefix . 'geslib_logger'; // Replace with your actual table name

      // Fetch distinct types
      $type_sql = "SELECT DISTINCT type FROM {$loggerTable}";
      $types = $wpdb->get_col($type_sql);

      $action_sql = "SELECT DISTINCT action FROM {$loggerTable}";
      $actions = $wpdb->get_col($action_sql);

      $entity_sql = "SELECT DISTINCT entity FROM {$loggerTable}";
      $entities = $wpdb->get_col($entity_sql);

      $log_id_sql = "SELECT DISTINCT log_id FROM {$loggerTable}";
      $log_ids = $wpdb->get_col($log_id_sql);

      $geslib_id_sql = "SELECT DISTINCT geslib_id FROM {$loggerTable}";
      $geslib_ids = $wpdb->get_col($geslib_id_sql);

      $metadata_sql = "SELECT metadata FROM {$loggerTable}";
      $metadatas = $wpdb->get_col($metadata_sql);
      ?>

<form method="post">
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
    <select name="filter_entity">
        <option value="">All Entities</option>
        <?php foreach ($entities as $entity): ?>
            <option value="<?php echo esc_attr($entity); ?>" <?php selected(isset($_POST['filter_entity']) && $_POST['filter_entity'] === $entity); ?>>
                <?php echo esc_html($entity); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="filter_log_id">
        <option value="">All Log id</option>
        <?php foreach ($log_ids as $log_id): ?>
            <option value="<?php echo esc_attr($log_id); ?>" <?php selected(isset($_POST['filter_log_id']) && $_POST['filter_log_id'] === $log_id); ?>>
                <?php echo esc_html($log_id); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="filter_geslib_id">
        <option value="">All Geslib id</option>
        <?php foreach ( $geslib_ids as $geslib_id ): ?>
            <option value="<?php echo esc_attr( $geslib_id ); ?>" <?php selected( isset( $_POST['filter_geslib_id'] ) && $_POST['filter_geslib_id'] === $geslib_id ); ?>>
                <?php echo esc_html( $geslib_id ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Filter"/>
</form>

   <?php
        $wp_list_table = new GeslibLoggerListTable();
        $wp_list_table->prepare_items();
        // Render the table
        echo "<form method='post' name='geslib_logger_search' action='".$_SERVER['PHP_SELF']."?page=geslib_logger'>";
        $wp_list_table->search_box("Geslib Logger Search", "search_geslib_logger");
        echo "</form>";
        $wp_list_table->display();
    ?>

</div>