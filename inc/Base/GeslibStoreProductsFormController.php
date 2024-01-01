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
            'store_authors',
            'store_products',
            'process_products_queue',
            'process_all',
            'process_dilve',
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

        if (!wp_next_scheduled('geslib_process_queue')) {
            wp_schedule_event(time(), 'daily', 'geslib_process_queue');
        }
        add_action('geslib_process_queue', [$this, 'processAll']);
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
        $geslibApiReadFiles = new GeslibApiReadFiles;
        $loggedFiles = $geslibApiReadFiles->listFilesInFolder();
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
        $geslibApiDb = new GeslibApiDbManager();
        $geslibApiDb->processFromQueue('store_lines');
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
        $geslibApiDb = new GeslibApiDbManager;
        $geslibApiDb->setLogStatus( $_POST['log_id'], 'queued' );
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
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->setLogStatus( $_POST['log_id'], 'logged' );
        update_option( 'geslib_admin_notice', 'Geslib Log unqueued');
        wp_send_json_success( [ 'message' => 'Geslib Log unqueued' ]);
    }
    public function ajaxHandleTruncateLog() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        if( !$geslibApiDbManager->truncateGeslibLogs()) {
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
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->reorganizeProductCategories();
        update_option('geslib_admin_notice', 'Geslib Categories Stored');
        wp_send_json_success(['message' => 'Geslib Categories Stored', 'task_id' => $task_id]);
    }

    public function ajaxHandleStoreEditorials() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiStoreData->storeEditorials();
        update_option('geslib_admin_notice', 'Geslib Editorials Stored');
        wp_send_json_success(['message' => 'Geslib Editorials Stored']);
    }
    public function ajaxHandleStoreAuthors() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiStoreData->storeAuthors();
        update_option('geslib_admin_notice', 'Geslib Authors Stored');
        wp_send_json_success(['message' => 'Geslib authors Stored']);
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

        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->processFromQueue('store_products');
        update_option('geslib_admin_notice', 'Procesando la cola PRODUCTOS');
        wp_send_json_success(['message' => 'Procesada la cola PRODUCTOS.']);
    }

    public function ajaxHandleProcessAll() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $dilveApi = new DilveApi();
        while( $geslibApiDbManager->checkLoggedStatus() ) {
            $geslibApiReadFiles->readFolder();
            $log_id = $geslibApiLines->storeToLines();
            $geslibApiDbManager->processFromQueue('store_lines');
            $geslibApiDbManager->storeProducts();
            $geslibApiDbManager->processFromQueue('store_products');
            $geslibApiDbManager->truncateGeslibLines();
            $geslibApiDbManager->setLogStatus( $log_id, 'processed');
            //
        }
        //$dilveApi->scanProducts();
        update_option('geslib_admin_notice', 'Procesando todos los archivos.');
        wp_send_json_success(['message' => 'Procesando todos los archivos.']);
    }

    public function ajaxHandleSetToLogged() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDb = new GeslibApiDbManager;
        $geslibApiDb->setLogTableToLogged();
        update_option('geslib_admin_notice', 'El registro ha sido reinicializado.');
        wp_send_json_success(['message' => 'El registro ha sido reinicializado.']);
    }
    public function ajaxHandleProcessDilve() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');

        $dilveApi = new DilveApi;
        $dilveApi->scanProducts();
        update_option( 'geslib_admin_notice', 'Dilve Portadas' );
        wp_send_json_success( [ 'message' => 'Importando las portadas' ] );
    }

    public function ajaxHandleDeleteProducts() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');

        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->deleteAllProducts();
        update_option( 'geslib_admin_notice', 'Geslib Products Deleted' );
        wp_send_json_success( ['message' => 'Deletion task has been queued', 'task_id' => $task_id] );
    }
    public function ajaxHandleTruncateLines(){
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        if ( !$geslibApiDbManager->truncateGeslibLines()) {
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

    public function processAll() {
        $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $dilveApi = new DilveApi();
        while( $geslibApiDbManager->checkLoggedStatus() ) {
            $geslibApiReadFiles->readFolder();
            $log_id = $geslibApiLines->storeToLines();
            $geslibApiDbManager->processFromQueue('store_lines');
            $geslibApiDbManager->storeProducts();
            $geslibApiDbManager->processFromQueue('store_products');
            $geslibApiDbManager->truncateGeslibLines();
            $geslibApiDbManager->setLogStatus( $log_id, 'processed');
        }
    }
}
