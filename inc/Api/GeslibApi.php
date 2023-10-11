<?php
class GeslibApi {

    public function messageThis(string $message, string $type = "success", array $placeholders = []) {
        $type = ($type == "notice") ? "info" : $type;
        $allowed_types = ['success', 'warning', 'error', 'info'];

        if (!in_array($type, $allowed_types)) {
            wp_die('Invalid message type provided: ' . $type);
            return;
        }

        if (!empty($placeholders)) {
            $message = strtr($message, $placeholders);
        }

        add_action('admin_notices', function() use ($message, $type) {
            echo '<div class="notice notice-'. $type.' is-dismissible">
                <p>'.$message.'</p>
            </div>';
        });
    }

    public function fileThis(string $message, string $type = "Notice", array $placeholders = []) {
        $logPath = ABSPATH . 'wp-content/plugins/geslib/logs/geslibError.log';

        if (!empty($placeholders)) {
            $message = strtr($message, $placeholders);
        }

        file_put_contents($logPath, '[' . $type . '] ' . $message . PHP_EOL, FILE_APPEND);
    }

    public function logThis(string $message, string $type = "info", array $placeholders = []) {
        $allowed_types = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        if (!in_array($type, $allowed_types)) {
            error_log('Invalid log type provided: ' . $type);
            return;
        }

        if (!empty($placeholders)) {
            $message = strtr($message, $placeholders);
        }

        $context = ['source' => 'GeslibApi'];

        switch ($type) {
            case 'emergency':
                wp_die($message);
                break;
            case 'alert':
            case 'critical':
            case 'error':
                error_log($message, 0, '', '', $context);
                break;
            case 'warning':
            case 'notice':
            case 'info':
            case 'debug':
            default:
                error_log($message, 0, '', '', $context);
                break;
        }
    }
}
