<?php
/**
 * @package geslib
 */
namespace Inc\Geslib\Base;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Api\GeslibApiStoreData;
use Inc\Geslib\Base\BaseController;
use Inc\Dilve\Api\DilveApi;
use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiDbTaxonomyManager;

/**
 * @class GeslibStoreProductsFormController
 */
class GeslibStoreProductsFormController extends BaseController
{
    public $adminNotice = '';
    /**
     * register
     *
     * @return void
     */
    public function register() {
        $actions = [
            'hello_world',
            'check_file',
            'store_log',
            'store_lines',
            'process_lines_queue',
            'log_queue',
            'log_unqueue',
            'truncate_log',
            'truncate_lines',
            'store_categories',
            'store_editorials',
            'store_autors',
            'store_products',
            'process_products_queue',
            'process_all',
            'set_to_logged',
            'delete_products',
            'empty_queue'
        ];
        foreach ( $actions as $action ) {
            $camelCase = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $action ) ) );
            if (method_exists($this, "ajaxHandle{$camelCase}")) {
                add_action( 'wp_ajax_geslib_' . $action, [ $this, "ajaxHandle{$camelCase}" ] );
            } else {
                echo "error with ajaxHandle{$camelCase}";
            }
        }

        add_action('admin_notices', [ $this, 'displayAdminNotice' ]);

    }

    /**
     * ajaxHandleHelloWorld
     *
     * @return void
     */
    public function ajaxHandleHelloWorld() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        update_option('geslib_admin_notice', 'Hello world!');
        wp_send_json_success(['message' => 'Hello world!']);
    }

    /**
     * ajaxHandleCheckFile
     *
     * @return void
     */
    public function ajaxHandleCheckFile() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        update_option('geslib_admin_notice', 'File Checked!');
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
		$loggedFiles = $geslibApiDbLogManager->fetchLoggedFilesFromDb();
        wp_send_json_success([
            'message' => 'Archivos en la carpeta geslib y su status en la tabla geslib_log',
            'loggedFiles' => json_encode($loggedFiles, true),
        ]);
    }

    /**
     * ajaxHandleStoreLog
     *
     * @return void
     */
    public function ajaxHandleStoreLog() {
        check_ajax_referer( 'geslib_store_products_form', 'geslib_nonce' );
        $geslibReadFiles = new GeslibApiReadFiles;
        $filenames = $geslibReadFiles->readFolder();
        update_option( 'geslib_admin_notice', 'File Logged' );
        wp_send_json_success( [ 'message' => 'File Logged', 'files' => $filenames ] );
    }

    /**
     * ajaxHandleStoreLines
     *
     * @return void
     */
    public function ajaxHandleStoreLines(){
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiLines = new GeslibApiLines;
        $geslibApiLines->storeToLines();
        update_option('geslib_admin_notice', 'Creada la cola de Lines');
        wp_send_json_success(['message' => 'Creada la cola de Lines. Puedes verlo en la pestaña "Queues".']);
    }

    public function ajaxHandleProcessLinesQueue() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbQueueManager->processFromQueue('store_lines');
        update_option('geslib_admin_notice', 'Creada la cola de Lines');
        wp_send_json_success(['message' => 'Creada la cola de Lines. Puedes verlo en la pestaña "Queues".']);
    }

    /**
     * ajaxHandleGeslibLogQueue
     *
     * @return void
     */
    public function ajaxHandleLogQueue() {
        $check_ajax_referer = check_ajax_referer( 'geslib_log_queue', 'geslib_log_queue_nonce' );
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLogManager->setLogStatus( $_POST['log_id'], 'queued' );
        update_option( 'geslib_admin_notice', 'Geslib Log queued' );
        wp_send_json_success( [ 'message' => 'Geslib Log queued' ]);
    }

    /**
     * ajaxHandleGeslibLogUnqueue
     *
     * @return void
     */
    public function ajaxHandleLogUnqueue() {
        check_ajax_referer('geslib_log_queue', 'geslib_log_queue_nonce');
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLogManager->setLogStatus( $_POST['log_id'], 'logged' );
        update_option( 'geslib_admin_notice', 'Geslib Log unqueued');
        wp_send_json_success( [ 'message' => 'Geslib Log unqueued' ]);
    }
    public function ajaxHandleTruncateLog() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        if( !$geslibApiDbLogManager->truncateGeslibLogs()) {
            update_option('geslib_admin_notice', 'ERROR: Geslib Log NOT Truncated');
            wp_send_json_success(['message' => 'ERROR: Geslib Log NOT Truncated']);
        }
        update_option('geslib_admin_notice', 'Geslib Log Truncated');
        wp_send_json_success(['message' => 'Geslib Log Truncated']);
    }

    public function ajaxHandleStoreCategories() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiStoreData->storeProductCategories();
        $geslibApiDbTaxonomyManager = new GeslibApiDbTaxonomyManager;
        $term = $geslibApiDbTaxonomyManager->reorganizeProductCategories();
        update_option('geslib_admin_notice', 'Geslib Categories Stored');
        wp_send_json_success(['message' => 'Geslib Categories Stored', 'term' => $term]);
    }

    public function ajaxHandleStoreEditorials() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiStoreData->storeEditorials();
        update_option('geslib_admin_notice', 'Geslib Editorials Stored');
        wp_send_json_success(['message' => 'Geslib Editorials Stored']);
    }
    public function ajaxHandleStoreAutors() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiStoreData->storeAuthors();
        update_option('geslib_admin_notice', 'Geslib Autors Stored');
        wp_send_json_success(['message' => 'Geslib autors Stored']);
    }

    public function ajaxHandleStoreProducts() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->storeProducts();
        //$progress = get_option('geslib_product_progress', 0);
        wp_send_json_success(['message' => 'Product Storing task has been queued', 'task_id' => $task_id]);
    }

    public function ajaxHandleProcessProductsQueue() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');

        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbQueueManager->processFromQueue('store_products');
        update_option('geslib_admin_notice', 'Procesando la cola PRODUCTOS');
        wp_send_json_success(['message' => 'Procesada la cola PRODUCTOS.']);
    }

    public function ajaxHandleProcessAll() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiReadFiles->readFolder();
        // Check if there are queues of type 'store_products' and execute them
        // Check if there are queues of type 'store_authors' and execute them
        $queuetypes = ['store_products', 'build_content', 'store_authors', 'store_categories', 'store_editorials', 'store_lines', ];
        foreach( $queuetypes as $queuetype ) {
            $geslibApiDbQueueManager->processFromQueue( $queuetype );
        }
        while( $geslibApiDbLogManager->checkLoggedStatus() ) {
            $log_id = $geslibApiDbLogManager->getGeslibLoggedId();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Current Log_id', 'geslib_log', [
                'message' => 'Current log_id '.$log_id. ' to be queued.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'START', 'geslib_log', [
                'message' => 'We start the full PROCESS',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            if ( !$geslibApiDbLogManager->isQueued() ){
                $geslibApiDbLogManager->setLogStatus( $log_id, 'queued' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Set log to queued', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' has been queued.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            } else {
                $geslibApiDbQueueManager->deleteItemsFromQueue( 'store_lines' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Reset store_lines', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' is already queued we delete store_lines from queue to start again.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            }
            $geslibApiLines->storeToLines($log_id);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to queue', 'geslib_queue', [
                'message' => 'We are moving data from files to geslib_queued(store_lines).',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue('store_lines');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to lines', 'geslib_lines', [
                'message' => 'We are moving data from geslib_queued(store_lines) to geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue('build_content');
            //$geslibApiStoreData->storeAuthors();
            $geslibApiDbLoggerManager->geslibLogger( $log_id, 0, 'info', 'Store to Terms', 'autors', [
                'message' => 'Saving Authors.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            //$geslibApiStoreData->storeEditorials();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Store to Terms', 'editorials', [
                'message' => 'Saving Editorials.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            //$geslibApiDbProductsManager->storeProducts();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to Products 1', 'geslib_queues', [
                'message' => 'Saving Product data to geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);

            $geslibApiDbQueueManager->processFromQueue( 'store_editorials' );
            $geslibApiDbQueueManager->processFromQueue( 'store_autors' );
            $geslibApiDbQueueManager->processFromQueue( 'store_categories' );
            $geslibApiDbQueueManager->processFromQueue( 'store_products' );
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','Store to Products 2', 'products', [
                'message' => 'Saving to woocommerce Products from geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLinesManager->truncateGeslibLines();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'truncate geslib_lines', 'geslib_lines', [
                'message' => 'Empty the table geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLogManager->setLogStatus( $log_id, 'processed');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','set log to processed', 'geslib_log', [
                'message' => 'Set log '.$log_id.' to processed',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
        }
        update_option('geslib_admin_notice', 'Procesando todos los archivos.');
        wp_send_json_success(['message' => 'Procesando todos los archivos.']);
    }

    public function ajaxHandleSetToLogged() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLogManager->setLogTableToLogged();
        update_option('geslib_admin_notice', 'El registro ha sido reinicializado.');
        wp_send_json_success(['message' => 'El registro ha sido reinicializado.']);
    }

    public function ajaxHandleDeleteProducts() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbProductsManager->deleteAllProducts();
        update_option( 'geslib_admin_notice', 'Geslib Products Deleted' );
        wp_send_json_success( ['message' => 'Deletion task has been queued'] );
    }
    public function ajaxHandleTruncateLines(){
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        if ( !$geslibApiDbLinesManager->truncateGeslibLines()) {
            update_option('geslib_admin_notice', 'ERROR: Geslib Lines NOT Truncated');
            wp_send_json_success(['message' => 'ERROR: Geslib Lines NOT Truncated']);
        }
        update_option( 'geslib_admin_notice', 'Geslib Lines Deleted' );
        wp_send_json_success( ['message' => 'Geslib lines was deleted.'] );
    }

    public function ajaxHandleEmptyQueue(){
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        // Define the queue table name
        global $wpdb;
        $queueTable = $wpdb->prefix . 'geslib_queues';

        // Truncate the table to empty it
        $wpdb->query("TRUNCATE TABLE `{$queueTable}`");
        wp_send_json_success( [ 'message' => 'Remove queue' ] );
    }

    public function displayAdminNotice() {
        if ($this->adminNotice !== '') {
            echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . $this->adminNotice . '</p>';
            echo '</div>';
        }
    }


    public function processQueue() {
        global $wpdb;
        // Define the queue table name
        $queueTable = $wpdb->prefix . 'geslib_queues';
        // Fetch the next task from the queue table
        $taskData = $wpdb->get_row("SELECT * FROM `{$queueTable}` ORDER BY id ASC LIMIT 1");

        if ( $taskData ) {
            switch ( $taskData->type ) {
                case 'check_file':
                    // Handle check_file task
                break;
                case 'store_log':
                    $geslibReadFiles = new GeslibApiReadFiles;
                    $geslibReadFiles->readFolder();
                break;
                case 'store_lines':
                    $geslibApiLines = new GeslibApiLines;
                    $line = $taskData['line'];
                    $log_id = $taskData['log_id'];
                    $geslibApiLines->readLine($line, $log_id);
                break;
                case 'store_author':
                    $geslibApiStoreData = new GeslibApiStoreData;
                    $geslibApiStoreData->storeAuthors();
                break;
            }
            // Delete the fetched task from the queue
            $wpdb->delete($queueTable, ['id' => $taskData->id]);
        }
    }

}
