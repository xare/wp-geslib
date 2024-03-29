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
        public function adminGeslibLogger() {
            return require_once("{$this->plugin_templates_path}/adminGeslibLogger.php");
        }
        public function adminLogTable() {
            return require_once("{$this->plugin_templates_path}/adminGeslibLogs.php");
        }
        public function adminLinesTable() {
            return require_once("{$this->plugin_templates_path}/adminGeslibLines.php");
        }
        public function adminQueuesTable() {
            return require_once("{$this->plugin_templates_path}/adminGeslibQueues.php");
        }
        public function adminFilesTable() {
            return require_once("{$this->plugin_templates_path}/adminGeslibFiles.php");
        }

        public function textSanitize( $input ) {
            $output = get_option('geslib_settings');
            $output['geslib_folder_index'] = sanitize_text_field($input['geslib_folder_index']);
            //update_option('geslib_settings', $output); // Save the updated option
            return $output;
        }
        public function adminSectionManager() {
            echo 'manage the Sections and Features of this plugin by activating the checkboxes in the list below';
        }
        public function textField( $args ){
            //return the input
            $name = $args['label_for'];
            $option_name = $args['option_name'];
            $options = get_option($option_name);
            $value = isset($options[$name]) ? $options[$name] : '';
            /* if ( isset($_POST['edit_post'])) {
                $input = get_option( $option_name );
                $value = $input[$_POST['edit_post']][$name];
            } */
            echo '<input
                type="text"
                class="'.$args['class'].'"
                id="'.$name.'"
                name="' . $option_name . '[' . $name . ']"
                value="' . esc_attr($value) . '"
                placeholder="Tell us the name of the geslib folder"
                required>';
        }
}