<?php
    /**
     * @package geslib
     */

    namespace Inc\Geslib\Api\Callbacks;
    use Inc\Geslib\Base\BaseController;

    class AdminCallbacks extends BaseController {

    public function adminDashboard() {
        
        return require_once("{$this->plugin_templates_path}/adminDashboard.php");
    }
}