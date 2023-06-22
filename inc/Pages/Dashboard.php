<?php

namespace Inc\Geslib\Pages;

use Inc\Geslib\Api\SettingsApi;
use Inc\Geslib\Base\BaseController;
use Inc\Geslib\Api\Callbacks\AdminCallbacks;

class Dashboard extends BaseController {
    public $settings;
    public $pages = [];
    public $callbacks;


    public function register() {
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
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
				'option_name' => 'geslib_settings',
				'callback' => [$this->callbacks, 'textSanitize']
            ]
		];

		$this->settings->setSettings( $args );

		// Save the default option if it doesn't exist
		if ( !get_option('geslib_settings') ) {
			$default_settings = [
				'geslib_folder_index' => ''
			];
			update_option('geslib_settings', $default_settings);
		}
	}

    public function setSections()
	{
		$args = [
			[
				'id'=> 'geslib_admin_index',
				'title' => 'Settings Manager',
				'callback' => [$this->callbacks , 'adminSectionManager'],
				'page' => 'geslib' //From menu_slug
				]
		];
		$this->settings->setSections( $args );
	}

    public function setFields()
	{
		$args = [
                    [
						'id'=> 'geslib_folder_index',
						'title' => 'Geslib folder Name',
						'callback' => [$this->callbacks, 'textField'],
						'page' => 'geslib', //From menu_slug
						'section' => 'geslib_admin_index',
						'args' => [
								'option_name' => 'geslib_settings',
								'label_for' => 'geslib_folder_index',
								'class' => 'regular-text'
							]
		            ]
                ];
		$this->settings->setFields( $args );
	}
}