<?php
namespace ExtensionManager;

class System {
	protected $ci;
	protected $settingsKeyPrefix = 'config_manager_ext_';

	public function __construct() {
		$this->ci =& get_instance();

		// Load settings model
		$this->ci->load->model('settings_model', 'settings');
	}

	/**
	 * Verify that the required settings are available, and if not,
	 * add them into the system. These settings should not be changed
	 * through the settings UI; they're controlled by the manager.
	 */
	public function verifySettingsKeysExist() {
		$name = $this->settingsKeyPrefix . 'extensions';
		$value = $this->ci->settings->get_setting( $name );
		if ( $value === false ) {
			// Add this setting
			$this->ci->settings->add_new_setting( [
				'setting_key' => $name,
				'setting_label' => 'ExtensionManager setting key. ** DO NOT CHANGE THIS VALUE DIRECTLY **',
				// Minimal value includes the ExtensionManager itself
				'setting_value' => json_encode( [ 'ExtensionManager' ] )
			] );
		}
	}

	/**
	 * Update the setting value and store
	 *
	 * @param string $value Settings value
	 */
	public function updateSettings( $value ) {
		if ( !is_string( $value ) ) {
			$value = json_encode( $value );
		}

		return $this->ci->settings->update_setting(
			$this->settingsKeyPrefix . 'extensions',
			[ 'setting_value'=> $value ]
		);
	}

	/**
	 * Verify that the required settings are available, and if not,
	 * add them into the system. These settings should not be changed
	 * through the settings UI; they're controlled by the manager.
	 */
	public function getValueFromSettings( $default = [] ) {
		$value = $this->ci->settings->get_setting(
			$this->settingsKeyPrefix . 'extensions'
		);

		if ( !$value ) {
			$value = $default;
		} else {
			$value = json_decode( $value );
		}

		return $value;
	}


	public function getExtensionDetails( $extName ) {
		$extDetailsFilePath = APPPATH.'extensions/'.$extName.'/details.json';
		if ( !file_exists( $extDetailsFilePath ) ) {
			return [];
		}

		// Go over the extensions folder
		$file = file_get_contents( $extDetailsFilePath );
		$details = json_decode( $file, true );

		return $details;
	}

	/**
	 * Check the extensions/ folder for available extensions
	 *
	 * @return array An array of directory names, representing
	 *  the extension folders
	 */
	public function getExtensionsOnDisk() {
		$extDirPath = APPPATH.'extensions/';
		// Go over the extensions folder
		if ( !is_dir( $extDirPath ) ) {
			// Bail out if 'extensions' directory isn't available
			// at all.
			return;
		}

		$dirs = array_map(
			function ( $dir ) {
				return basename( $dir );
			},
			glob( $extDirPath . '/**', GLOB_ONLYDIR )
		);

		return $dirs;
	}

	/**
	 * Install the necessary menu items, if they don't exist yet
	 */
	public function install() {
		$this->ci->load->model('menu_model');
		$expectedLink = 'extensions/ExtensionManager/Manage/';
		$cat = $this->ci->menu_model->get_menu_category( 'manageext' );

		if ( $cat === false ) {
			// Add the category and the menu items
			$insertCat = $this->ci->menu_model->add_menu_category( [
				'menucat_menu_cat' => 'manageext',
				'menucat_name' => 'Manage Extensions',
				'menucat_type' => 'adminsub',
				'menucat_order' => 7
			] );
		}

		$query = $this->ci->db->get_where('menu_items', array('menu_name' => 'Manage Extensions'));
    $item = ($query->num_rows() > 0) ? $query->row() : false;   
      if($item==false){

			// Add item
			$insertItem = $this->ci->menu_model->add_menu_item( [
				'menu_name' => 'Manage Extensions',
				'menu_group' => 0,
				'menu_order' => 0,
				'menu_sim_type' => 1,
				'menu_link' => $expectedLink . 'manage',
				'menu_link_type' => 'onsite',
				'menu_need_login' => 'none',
				'menu_use_access' => 'y',
				'menu_access' => 'site/settings',
				'menu_access_level' => 0,
				'menu_display' => 'y',
				'menu_type' => 'adminsub',
				'menu_cat' => 'manageext',
			] );
		}
	}

}
