<?php
    use Inc\Geslib\Api\GeslibApiDbManager;
    use Inc\Geslib\Api\GeslibApiLines;
    use Inc\Geslib\Api\GeslibApiLog;
    use Inc\Geslib\Api\GeslibApiReadFiles;

    $geslibDbManager = new GeslibApiDbManager;
    $geslibApiReadFiles = new GeslibApiReadFiles;
    $geslibApiLog = new GeslibApiLog;
    $geslibApiLines = new GeslibApiLines;

    $geslib_admin_notice = get_option('geslib_admin_notice', '');
    if ( !empty( $geslib_admin_notice ) ) {
        echo '<div class="notice">' . $geslib_admin_notice . '</div>';
        delete_option('geslib_admin_notice');  // Clear the notice
    }
?>
<div class="wrap geslib-wrap">
    <h1>Acciones</h1>
    <p>¡Ojo! En principio no hay que tocar estos botones ya que las acciones que disparan han sido introducidas en una tarea programada que ejecutará cada una por separado, en orden y de modo automático. No obstante, en caso de fallo se puede hacer manualmente.</p>
    <p>Esta es la secuencia de acciones y sus explicaciones.</p>
    <ol>
        <li><strong>Lectura del directorio geslib:</strong> El sistema escaneará los archivos contenidos en el directorio "geslib" y mirará la tabla de registro (geslib_log), si el archivo ya ha sido leido no hará nada, si este archivo no está presente en la tabla añadirá una fila a dicha tabla y lo dejará en estatus de "logged" registrado. Si todo ha funcionado correctamente los archivos presentes en la tabla deberían tener el estatus de "processed" procesados.</li>
        <li><strong>Lectura de los registros "geslib_log":</strong> Se lée la tabla de registros en busca de la fila que contiene el archivo en estatus "logged" que está sin procesar, si todo ha ido bien solo debería de haber uno si hay más de uno el sistema elegirá el primero en haber entrado.</li>
        <li><strong>Cola de procesamiento de geslib_lines</strong> Un tercer proceso lee todo el archivo pendiente de procesar, coge todos los datos que encuentra lo envía a una 1a cola de procesamiento.</li>
        <ol>
            <li><strong>CLI</strong>: wp geslib</li>
        </ol>
        <li><strong>Archivo en la tabla geslib_lines</strong> Un cuarto proceso lee la cola de procesamiento y lo guarda en la tabla de "lineas" geslib_lines, dicha tabla se resetea de modo a que contenga lineas sólo de un solo archivo</li>
        <li><strong>Cola de procesamiento previa a guardar los objetos en la base de datos</strong> Un quinto proceso lee esta tabla y la envía a otra cola de procesamiento.</li>
        <ol>
            <li><strong>CLI</strong>: wp geslib</li>
        </ol>
        <li><strong>Almacenaje de los datos en su destino final</strong> Se lee la cola de procesamiento y se guardan los contenidos en el lugar finald de la tabla de datos donde ya se pueden acceder.</li>
    </ol>

    <ul class="geslib-statistics">
        <li>Productos almacenados:<br />
            <strong><?php echo $geslibDbManager->get_total_number_of_products(); ?></strong>
        </li>
        <li>Archivos geslib en la carpeta:<br />
            <strong><?php echo $geslibApiReadFiles->countFilesInFolder(); ?></strong>
        </li>
         <li>Archivos registrados en la tabla logs:<br />
            <strong><?php echo $geslibDbManager->countGeslibLog(); ?></strong>
            <br />
            Logged: <strong><?php echo $geslibDbManager->countGeslibLogStatus('logged'); ?></strong>
            <br />
            Queued: <strong><?php echo $geslibDbManager->countGeslibLogStatus('queued'); ?></strong>
            <br />
            Processed: <strong><?php echo $geslibDbManager->countGeslibLogStatus('processed'); ?></strong>

        </li>
        <li>Archivos registrados en la tabla lines:<br />
            <strong><?php echo $geslibDbManager->countGeslibLines(); ?></strong>
        </li>
        <li>Tareas en la cola "store_lines":<br />
            <strong><?php echo $geslibDbManager->countGeslibQueue('store_lines'); ?></strong>
        </li>
        <li>Tareas en la cola "store_products":<br />
            <strong><?php echo $geslibDbManager->countGeslibQueue('store_products'); ?></strong>
        </li>
    </ul>
    <form method="post" action="#tab-1" id="geslibProcess">
        <?php wp_nonce_field('geslib_store_products_form', 'geslib_nonce'); ?>
        <?php
            $buttons = [
                ['0. Hello World', 'primary', 'hello_world',''],
                ['0. Check File', 'primary', 'check_file','En este paso vamos a escanear el directorio y compararlo con los archivos guardados en la base de datos en la tabla de registro llamada geslib_log'],
                ['1. Geslib Folder -> geslib_log', 'primary', 'store_log',''],
                ['2. Geslig File -> queue geslib lines', 'primary', 'store_lines','Creada la cola de Lines. Puedes verlo en la pestaña "Queues".'],
                ['3. Store Categories -> queue categories ', 'primary', 'store_categories',''],
                ['4. Store Editorials', 'primary', 'store_editorials',''],
                ['5. Store Authors', 'primary', 'store_authors',''],
                ['6. Store Products: geslib_lines -> Queue products', 'primary', 'store_products',''],
                ['X1. Truncate geslib log', 'delete', 'truncate_log','Esta acción borra todos los registros de los archivos procesados o puestos en cola.'],
                ['X2. Truncate geslib lines', 'delete', 'truncate_lines','Esta acción borra todas las lineas del último archivo procesado de la cola.'],
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