<?php

namespace Inc\Geslib\Pages;

use Inc\Geslib\Api\SettingsApi;
use Inc\Geslib\Base\BaseController;
use Inc\Geslib\Api\Callbacks\AdminCallbacks;

class Dashboard extends BaseController {
    public $settings;
    public $pages = [];
	public $subpages = []; // Add this line to define subpages
    public $callbacks;

    public function register() {
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->setPages();
		$this->setSubpages();
        $this->setSettings();
		$this->setSections();
		$this->setFields();
        $this->settings
			->addPages( $this->pages )
			->withSubPage( 'Dashboard' )
			->addSubPages( $this->subpages )
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

	// Define this new method to add your subpages
    public function setSubpages() {
        $this->subpages = [
			[
                'parent_slug' => 'geslib', // Parent menu slug
                'page_title' => 'Geslib Files', // Page title
                'menu_title' => 'Geslib Files', // Menu title
                'capability' => 'manage_options', // Capability
                'menu_slug' => 'geslib_files', // Menu slug
                'callback' => [$this->callbacks, 'adminFilesTable'] // Callback function, define it in AdminCallbacks class
			],
			[
                'parent_slug' => 'geslib', // Parent menu slug
                'page_title' => 'Geslib Log', // Page title
                'menu_title' => 'Geslib Log', // Menu title
                'capability' => 'manage_options', // Capability
                'menu_slug' => 'geslib_log', // Menu slug
                'callback' => [$this->callbacks, 'adminLogTable'] // Callback function, define it in AdminCallbacks class
			],
            [
                'parent_slug' => 'geslib', // Parent menu slug
                'page_title' => 'Geslib Logger', // Page title
                'menu_title' => 'Geslib Logger', // Menu title
                'capability' => 'manage_options', // Capability
                'menu_slug' => 'geslib_logger', // Menu slug
                'callback' => [$this->callbacks, 'adminGeslibLogger'] // Callback function, define it in AdminCallbacks class
			],
			[
                'parent_slug' => 'geslib', // Parent menu slug
                'page_title' => 'Geslib Queues', // Page title
                'menu_title' => 'Geslib Queues', // Menu title
                'capability' => 'manage_options', // Capability
                'menu_slug' => 'geslib_queues', // Menu slug
                'callback' => [$this->callbacks, 'adminQueuesTable'] // Callback function, define it in AdminCallbacks class
			],
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