<?php

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiLog;
use Inc\Geslib\Api\GeslibApiReadFiles;

    $geslibApiDbManager = new GeslibApiDbManager;
    $geslibApiReadFiles = new GeslibApiReadFiles;
    $geslibApiLog = new GeslibApiLog;
    $geslibApiLines = new GeslibApiLines;
    $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
    $geslibApiDbLogManager = new GeslibApiDbLogManager;
    $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
    $geslibApiDbProductsManager = new GeslibApiDbProductsManager;

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
        <li>Archivos geslib en la carpeta:<br />
            <strong data-target="total-files">
                <?php echo $geslibApiReadFiles->countFilesInFolder(); ?>
            </strong>
        </li>
         <li>Archivos registrados en la tabla logs:<br />
            <strong data-target="total-logs">
                <?php echo $geslibApiDbLogManager->countGeslibLog(); ?>
            </strong>
            <br />
            Logged:
            <strong data-target="geslib-log-logged">
                <?php echo $geslibApiDbLogManager->countGeslibLogStatus('logged'); ?>
            </strong>
            <br />
            Queued:
            <strong data-target="geslib-log-queued">
                <?php echo $geslibApiDbLogManager->countGeslibLogStatus('queued'); ?>
            </strong>
            <br />
            Processed:
            <strong data-target="geslib-log-processed">
                <?php echo $geslibApiDbLogManager->countGeslibLogStatus('processed'); ?>
            </strong>
        </li>
        <li>Archivo en la cola de procesamiento:<br />
            <strong data-target="queued-filename"><?php echo $geslibApiDbLogManager->getLogQueuedFilename(); ?></strong>
        </li>
        <li>Tareas en la cola "store_lines":<br />
            <strong data-target="total-lines-queue"><?php echo $geslibApiDbQueueManager->countGeslibQueue('store_lines'); ?></strong>
        </li>
        <li>Tareas esperando a ser procesadas:<br />
            <strong data-target="total-lines"><?php echo $geslibApiDbLinesManager->countGeslibLines(); ?></strong>
        </li>

        <li>Tareas en la cola "store_products":<br />
            <strong data-target="total-products-queue"><?php echo $geslibApiDbQueueManager->countGeslibQueue('store_products'); ?></strong>
        </li>
        <li>Tareas en la cola "store_authors":<br />
            <strong data-target="total-authors-queue"><?php echo $geslibApiDbQueueManager->countGeslibQueue('store_autors'); ?></strong>
        </li>
        <li>Tareas en la cola "store_editorials":<br />
            <strong data-target="total-editorials-queue"><?php echo $geslibApiDbQueueManager->countGeslibQueue('store_editorials'); ?></strong>
        </li>
        <li>Tareas en la cola "store_categories":<br />
            <strong data-target="total-categories-queue"><?php echo $geslibApiDbQueueManager->countGeslibQueue('store_categories'); ?></strong>
        </li>
        <li>Productos almacenados:<br />
            <strong data-target="total-products"><?php echo $geslibApiDbProductsManager->getTotalNumberOfProducts(); ?></strong>
        </li>
    </ul>
    <form method="post" action="#tab-1" id="geslibProcess">
        <?php wp_nonce_field('geslib_store_products_form', 'geslib_nonce'); ?>
        <?php
            $buttons = [
                ['0. Ajax Funciona', 'primary', 'hello_world', ''],
                ['1. Escanear directorio', 'primary', 'check_file', 'En este paso vamos a escanear el directorio y compararlo con los archivos guardados en la base de datos en la tabla de registro llamada geslib_log'],
                ['1. Registrar archivos', 'primary', 'store_log',''],
                ['2. Preparar datos del archivo', 'primary', 'store_lines', 'Creada la cola de Lines. Puedes verlo en la pestaña "Queues".'],
                ['2B. Procesar datos del archivo','primary', 'process_lines_queue', 'Procesar la cola de lines'],
                ['3. Preparar datos de productos', 'primary', 'store_products',''],
                ['3B. Crear productos','primary', 'process_products_queue', 'Procesar la cola de lines'],
                ['4. Guardar Categories -> queue categories ', 'primary', 'store_categories',''],
                ['5. Guardar Editorials', 'primary', 'store_editorials',''],
                ['6. Guardar Authors', 'primary', 'store_autors',''],
                ['7. Proceso total', 'primary', 'process_all', ''],
                ['8. Proceso Dilve', 'primary', 'process_dilve', ''],
                ['9. Reinicializar Registro', 'primary', 'set_to_logged', ''],
                ['X1. Vaciar la tabla de registro', 'delete', 'truncate_log', 'Esta acción borra todos los registros de los archivos procesados o puestos en cola.'],
                ['X2. Vaciar la tabla de los datos', 'delete', 'truncate_lines', 'Esta acción borra todas las lineas del último archivo procesado de la cola.'],
                ['X3. Borrar los productos', 'delete', 'delete_products',''],
                ['X4. Borrar las colas de procesamiento', 'delete', 'empty_queue','']
            ];

            array_map(function($button) {
                list($label, $type, $name, $alert) = $button;
                submit_button($label, $type, $name, false, ['data-swal'=>$alert]);
            }, $buttons);
        ?>
    </form>
    <div data-container="geslib" class="terminal"></div>
</div>