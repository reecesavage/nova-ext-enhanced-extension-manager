<?php
namespace ExtensionManager;

class ConfigManager {
	protected $extensionConfig;
	protected $settingsKeyPrefix = 'config_manager_ext_';
	protected $availableExtensions = [];
	protected $enabledExtensions = [];
	protected $ci;

	function __construct() {
		$this->ci =& get_instance();

		// Load settings model
		$this->ci->load->model('settings_model', 'settings');
	}

	/**
	 * Take the $config['extensions']['enabled'][] variable
	 * and populate it based on the saved settings that the
	 * ExtensionManager provided.
	 *
	 * @param  array $extensions A reference to the config
	 *  variable $config['extensions']['enabled'] from
	 *  `nova/config/extensions.php`
	 */
	public function redefineExtensionConfig( &$extensions ) {
		$enabled = $this->getDefinitionFromSettings();
		$available = $this->getAvailableExtensions();

		// From the enabled extensions, make sure they're all still available
		$enabled = array_filter(
			$enabled,
			function ( $enabled_ext ) use ( $available ) {
				return in_array( $enabled_ext, $available );
			}
		);

		// Enable the extensions
		$extensions = $enabled;
	}

	/**
	 * Verify that the required settings are available, and if not,
	 * add them into the system. These settings should not be changed
	 * through the settings UI; they're controlled by the manager.
	 */
	protected function getDefinitionFromSettings() {
		$minimalValue = [ 'ExtensionManager' ];

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
	 * @return array An array of directory names, representing
	 *  the extension folders
	 */
	protected function getAvailableExtensions() {
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

}
