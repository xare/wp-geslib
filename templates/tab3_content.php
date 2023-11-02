<?php
    global $wpdb;

    $table_name = $wpdb->prefix .'geslib_log';
    $query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
    $results = $wpdb->get_results($query, ARRAY_A);
?>

<div class="table-container">
    <?php if (!empty($results)) : ?>
        <form method="post" action="#tab-3" id="geslibLogQueueProcess">
        <?php wp_nonce_field('geslib_log_queue', 'geslib_log_queue_nonce'); ?>
        <table class="geslib-table">
            <thead>
                <tr>
                    <?php foreach ($results[0] as $column => $value) : ?>
                        <th><?php echo esc_html($column); ?></th>
                    <?php endforeach; ?>
                    <th> Acciones </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) : ?>
                <tr>
                    <?php foreach ($row as $value) : ?>
                        <?php
                            $logged = ( $row['status'] !== 'logged' ) ? false : true;
                        ?>
                        <td><?php echo esc_html($value); ?></td>
                    <?php endforeach; ?>
                    <td>
                        <?php if ($logged === true) : ?>
                            <button class="button delete" name="queue" data-action="queue" data-log-id="<?php echo $row["id"]; ?>">Queue</button>
                        <?php else: ?>
                            <button class="button delete" name="unqueue" data-action="unqueue" data-log-id="<?php echo $row["id"]; ?>">Unqueue</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </form>
    <?php else : ?>
        <p>No se han encontrado datos.</p>
    <?php endif; ?>
</div>