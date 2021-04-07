<?php
namespace ext_nova_enhanced_extension_manager;

require_once( dirname( dirname(__FILE__) ) . '/includes/System.php' );

class ConfigManager {
	protected $extensionConfig;
	protected $mandatoryExtensions = [ 'ext_nova_enhanced_extension_manager' ];
	protected $availableExtensions = [];
	protected $enabledExtensions = [];
	protected $ci;
	protected $sys;

	function __construct() {
		$this->ci =& get_instance();
		$this->sys = new System();
		$this->sys->install(); // No-op or install


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
		$enabled = $this->sys->getValueFromSettings( $this->mandatoryExtensions );
		$available = $this->sys->getExtensionsOnDisk();
            
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
}
