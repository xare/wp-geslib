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
    public function register()
    {
        //add_action('admin_init', [$this, 'handleFormSubmission']);
        $actions = [
            'hello_world',
            'check_file',
            'store_log',
            'store_lines',
            'geslib_log_queue',
            'truncate_log',
            'truncate_lines',
            'store_categories',
            'store_editorials',
            'store_authors',
            'store_products',
            'delete_products'
        ];
        add_action('wp_ajax_geslib_log_queue', [$this, 'ajaxHandleGeslibLogQueue']);
        foreach ( $actions as $action ) {
            $camelCase = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $action ) ) );
            add_action( 'wp_ajax_geslib_' . $action, [ $this, 'ajaxHandle' . $camelCase ] );
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
        $geslibApiReadFiles = new GeslibApiReadFiles;
        wp_send_json_success([
            'message' => 'Files in folder',
            'items' => $geslibApiReadFiles->listFilesInFolder()
        ]);
    }

    /**
     * ajaxHandleStoreLog
     *
     * @return void
     */
    public function ajaxHandleStoreLog() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibReadFiles = new GeslibApiReadFiles();
        $geslibReadFiles->readFolder();
        update_option('geslib_admin_notice', 'File Logged');
        wp_send_json_success(['message' => 'File Logged']);
    }

    /**
     * ajaxHandleStoreLines
     *
     * @return void
     */
    public function ajaxHandleStoreLines()
    {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiLines = new GeslibApiLines;
        $geslibApiLines->storeToLines();
        update_option('geslib_admin_notice', 'Geslib Lines Saved');
        wp_send_json_success(['message' => 'Geslib Lines Saved']);
    }

    /**
     * ajaxHandleGeslibLogQueue
     *
     * @return void
     */
    public function ajaxHandleGeslibLogQueue() {
        check_ajax_referer('geslib_log_queue', 'geslib_log_queue_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        if( $geslibApiDbManager->setLogStatus($_POST['log_id'], 'queued') === false ) {
            update_option('geslib_admin_notice', 'Geslib Log NOT queued');
            wp_send_json_success(['message' => 'Failed to queue the log']);
            return;
        }
        update_option('geslib_admin_notice', 'Geslib Log queued');
        wp_send_json_success(['message' => 'Geslib Log queued']);
    }

    public function ajaxHandleTruncateLog() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager->truncateGeslibLog();
        update_option('geslib_admin_notice', 'Geslib Log Truncated');
        wp_send_json_success(['message' => 'Geslib Log Truncated']);
    }
    public function ajaxHandleStoreCategories() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        //$geslibApiStoreData->storeProductCategories();
        update_option('geslib_admin_notice', 'Geslib Categories Stored');
        wp_send_json_success(['message' => 'Geslib Categories Stored']);
    }

    public function ajaxHandleStoreEditorials() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiStoreData = new GeslibApiStoreData;
        //$geslibApiStoreData->storeEditorials();
        update_option('geslib_admin_notice', 'Geslib Editorials Stored');
        wp_send_json_success(['message' => 'Geslib Editorials Stored']);
    }

    public function ajaxHandleStoreProducts() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        $log_id = $geslibApiDbManager->getQueuedLogId();
        error_log('log_id: '.$log_id);
        $response = $geslibApiDbManager->storeProducts();
        $hasMore = json_decode($response, true)['hasMore'] ?? null;
        if ($hasMore === null || $hasMore === false) {
            // Run these only when all products have been processed
            $geslibApiDbManager->truncateGeslibLines();
            if ($geslibApiDbManager->setLogStatus($log_id, 'processed') === false) {
                update_option('geslib_admin_notice', 'Geslib Log NOT processed');
                wp_send_json_success(['message' => 'Failed to process the log']);
                return;
            }
            update_option('geslib_admin_notice', 'Geslib Products Stored');
        }
        $progress = get_option('geslib_product_progress', 0);
        wp_send_json_success($response);
    }
    public function ajaxHandleDeleteProducts() {
        check_ajax_referer('geslib_store_products_form', 'geslib_nonce');
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbManager->deleteAllProducts();
        $progress = get_option('geslib_product_progress', 0);
        $hasMore = get_option('geslib_hasmore');
        update_option('geslib_admin_notice', 'Geslib Products Deleted');
        wp_send_json_success([
            'message' => 'Geslib Products Deleted' ,
            'progress' => $progress,
            'hasMore' => $hasMore]);
    }


    public function handleFormSubmission()
    {
        error_log("handleFormSubmission called");
        if (isset($_POST['hello_world'])
        && check_admin_referer('geslib_store_products_form', 'geslib_nonce')) {
            update_option('geslib_admin_notice', 'Hello world!');
            error_log("Updated geslib_admin_notice: " . get_option('geslib_admin_notice', ''));
        }
        if (isset($_POST['store_log'])
            && check_admin_referer('geslib_store_products_form', 'geslib_nonce')) {
                $geslibReadFiles = new GeslibApiReadFiles();
                $geslibReadFiles->readFolder();
                update_option('geslib_admin_notice', 'File Logged');
            // Your logic for "store_log" here
        }
        if (isset($_POST['store_lines'])
            && check_admin_referer('geslib_store_products_form', 'geslib_nonce')) {
                $geslibApiLines = new GeslibApiLines();
                $geslibApiLines->storeToLines();
                update_option('geslib_admin_notice', 'Geslib Lines Saved');
            // Your logic for "store_log" here
        }

        if (isset($_POST['truncate_geslib_log'])
            && check_admin_referer('geslib_store_products_form', 'geslib_nonce')) {
                global $wpdb;
                $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'geslib_log');
                update_option('geslib_admin_notice', 'Geslib Log Deleted');
            }
            if (isset($_POST['truncate_geslib_lines'])
            && check_admin_referer('geslib_store_products_form', 'geslib_nonce')) {
                global $wpdb;
                $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'geslib_lines');
                update_option('geslib_admin_notice', 'Geslib Lines Deleted');
            }
        // Handle other buttons similarly
    }

    public function displayAdminNotice() {
        if ($this->adminNotice !== '') {
            echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . $this->adminNotice . '</p>';
            echo '</div>';
        }
    }
}
