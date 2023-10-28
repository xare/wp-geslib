<?php
    $geslib_admin_notice = get_option('geslib_admin_notice', '');
    if ( !empty( $geslib_admin_notice ) ) {
        echo '<div class="notice">' . $geslib_admin_notice . '</div>';
        delete_option('geslib_admin_notice');  // Clear the notice
    }
?>
<div class="wrap">
    <h1>Acciones</h1>
    <form method="post" action="#tab-1" id="geslibProcess">
        <?php wp_nonce_field('geslib_store_products_form', 'geslib_nonce'); ?>
        <?php
            $buttons = [
                ['0. Hello World', 'primary', 'hello_world',''],
                ['0. Check File', 'primary', 'check_file','En este paso vamos a escanear el directorio y compararlo con los archivos guardados en la base de datos en la tabla de registro llamada geslib_log'],
                ['1. Log geslib file', 'primary', 'store_log',''],
                ['2. Lines geslib file', 'primary', 'store_lines','Creada la cola de Lines. Puedes verlo en la pestaña "Queues".'],
                ['3. Store Categories', 'primary', 'store_categories',''],
                ['4. Store Editorials', 'primary', 'store_editorials',''],
                ['5. Store Authors', 'primary', 'store_authors',''],
                ['6. Store Products', 'primary', 'store_products',''],
                ['X1. Truncate geslib log', 'delete', 'truncate_log','Esta acción borra todos los registros de los archivos procesados o puestos en cola.'],
                ['X2. Truncate geslib lines', 'delete', 'truncate_lines',''],
                ['X3. Delete all Products', 'delete', 'delete_products',''],
                ['X4. Empty the queue', 'delete', 'empty_queue','']
            ];

            array_map(function($button) {
                list($label, $type, $name, $alert) = $button;
                submit_button($label, $type, $name, false, ['data-swal'=>$alert]);
            }, $buttons);
        ?>
    </form>
    <div data-container="geslib" class="terminal"></div>
</div>