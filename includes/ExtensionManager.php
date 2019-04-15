<?php
namespace ExtensionManager;

class ExtensionManager {
	protected $ci;
	protected $settingsKeyPrefix = 'config_manager_ext_';
	protected $mandatoryExtensions = [ 'ExtensionManager' ];

	function __construct() {
		$this->ci =& get_instance();

		// Load settings model
		$this->ci->load->model('settings_model', 'settings');

		// Verify settings
		$this->verifySettings();
	}

	/**
	 * Get the full definition from the settings and adjust it
	 * so that it represents extensions that are unavailable/available
	 * on the disk
	 */
	public function getFullDefinition() {
		$newDefinition = [];
		$saved = $this->getDefinitionFromSettings();
		$available = $this->getAvailableExtensions();

		foreach ( $saved as $extName ) {
			$newDefinition[$extName] = [
				'exists' => in_array( $extName, $available ),
				'enabled' => true,
				'details' => $this->getExtensionDetails( $extName ),
			];
		}

		// Go over available extensions and see if there's any
		// available that is missing from the list
		// Validate with extensions on the server/disk
		foreach ( $available as $extName ) {
			if ( !isset( $newDefinition[$extName] ) ) {
				$newDefinition[$extName] = [
					'exists' => true,
					'enabled' => false,
					'details' => $this->getExtensionDetails( $extName ),
				];
			}
		}

		return $newDefinition;
	}

	protected function getExtensionDetails( $extName ) {
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
	 * Verify that the required settings are available, and if not,
	 * add them into the system. These settings should not be changed
	 * through the settings UI; they're controlled by the manager.
	 */
	protected function verifySettings() {
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

	protected function getDefinitionFromSettings() {
		$minimalValue = [
			'ExtensionManager' => [
				"exists" => true,
				"enabled" => true
			]
		];

		$value = $this->ci->settings->get_setting(
			$this->settingsKeyPrefix . 'extensions'
		);

		if ( !$value ) {
			$value = $minimalValue;
		} else {
			$value = json_decode( $value );
		}

		return $value;
	}

	/**
	 * Check the extensions/ folder for available extensions
	 *
	 * @return [type] [description]
	 */
	protected function getAvailableExtensions() {
		$extDirPath = APPPATH.'extensions/';
		// Go over the extensions folder
		if ( !is_dir( $extDirPath ) ) {
			// Bail out if 'extensions' directory isn't available
			// at all.
			return;
		}

		return array_map(
			function ( $dir ) {
				return basename( $dir );
			},
			glob( $extDirPath . '/**', GLOB_ONLYDIR )
		);
	}

}
