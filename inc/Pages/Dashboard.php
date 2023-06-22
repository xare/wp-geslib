<?php

namespace Inc\Geslib\Pages;

use Inc\Geslib\Api\SettingsApi;
use Inc\Geslib\Base\BaseController;
use Inc\Geslib\Api\Callbacks\AdminCallbacks;
use Inc\Geslib\Api\Callbacks\ManagerCallbacks;

class Dashboard extends BaseController {
    public $settings;
    public $pages = [];
    public $callbacks;
    public $callbacks_mngr;

    public function register() {
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->callbacks_mngr = new ManagerCallbacks();
        $this->setPages();
        $this->setSettings();
		$this->setSections();
		$this->setFields();
        $this->settings
			->addPages( $this->pages )
			->withSubPage( 'Dashboard' )
			//->addSubPages( $this->subpages )
			->register();
        /* $this->storeGeslib(); */

    }

    /* public function storeGeslib() {
        $option = get_option('geslib') ?: '';

    } */

    public function setPages(){
		$this->pages = [
			[
				'page_title' => __('Geslib','geslib'),
				'menu_title' =>  __('Geslib','geslib'),
				'capability' => 'manage_options',
				'menu_slug' => 'geslib',
				'callback' => [$this->callbacks, 'adminDashboard'] ,
				'icon_url' => 'dashicons-admin-plugins',
				'position' => 110
			]
		];
	}

    public function setSettings()
	{
		$args = [
			[
				'option_group'=> 'geslib_settings',
				'option_name' => 'geslib',
				'callback' => [$this->callbacks_mngr, 'textSanitize']
            ]
		];

		$this->settings->setSettings( $args );
	}

    public function setSections()
	{
		$args = [
			[
				'id'=> 'geslib_admin_index',
				'title' => 'Settings Manager',
				'callback' => [$this->callbacks_mngr , 'adminSectionManager'],
				'page' => 'geslib' //From menu_slug
				]
		];
		$this->settings->setSections( $args );
	}

    public function setFields()
	{
		/* foreach($this->managers as $key => $value) { */
		$args = [
                    [
						'id'=> 'geslib_folder_index',
						'title' => 'Geslib folder Name',
						'callback' => [$this->callbacks_mngr, 'textField'],
						'page' => 'geslib', //From menu_slug
						'section' => 'geslib_admin_index',
						'args' => [
								'option_name' => 'geslib',
								'label_for' => 'geslib_folder_index',
								'class' => 'regular-text'
							]
		            ]
                ];
		/* } */
		$this->settings->setFields( $args );
	}
}