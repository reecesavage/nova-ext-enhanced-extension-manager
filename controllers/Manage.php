<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once MODPATH.'core/libraries/Nova_controller_main.php';
require_once __DIR__ . '/../includes/ExtensionManager.php';

class __extensions__ExtensionManager__Manage extends Nova_controller_main {
	protected $manager;
	protected $mandatoryExtensions;

	public function __construct()
	{
		parent::__construct();
		$this->manager = new \ExtensionManager\ExtensionManager();
		$this->mandatoryExtensions = [ 'ExtensionManager' ];

		$this->_regions['nav_sub'] = Menu::build('adminsub', 'manageext');
	}

	public function toggle( $extensionName ) {
		$extensionName = urldecode( $extensionName );
		if ( isset( $_POST['submit'] ) ) {
			$action = $_POST['action'];
			$enabledExtensions = $this->manager->getCurrentValue();
			if (
				$action === 'disable' ||
				$action === 'remove'
			) {
				// Already in; disable it
				array_splice(
					$enabledExtensions,
					array_search( $extensionName, $enabledExtensions ),
					1
				);
			} else if ( $action === 'enable' ) {
				// Add it in
				$enabledExtensions[] = $extensionName;
			}
			// Save the new setting
			$this->manager->updateSettings( $enabledExtensions );
		}

		// We are doing this through its own entrypoint and a redirect
		// so that when the user finishes the "reload" after update, the system
		// already loaded or disabled the extension through the config.
		redirect( 'extensions/ExtensionManager/Manage/manage' );
	}

	/**
	 * Displays a list of available extensions with their details
	 * and the enable/disable states, allowing the user to change the
	 * enabled extensions.
	 */
	public function manage() {
		$data = [
			// TODO: SRSLY, i18n... !!!!
			'header' => 'Manage Extensions',
			'labels' => [
				'status_missing' => 'Missing',
				'status_inactive' => 'Inactive',
				'status_active' => 'Active',
			],
			'buttons' => [
				'apply' => [
					'type' => 'submit',
					'class' => 'button-main',
					'name' => 'submit',
					'value' => 'submit',
					'id' => 'extensions-apply',
					// TODO: SRSLY, i18n... !!!!
					'content' => 'Apply'
				],
				'enable' => [
					'type' => 'submit',
					'class' => 'button-sec',
					'name' => 'submit',
					'value' => 'enable',
					'id' => 'extensions-enable',
					// TODO: SRSLY, i18n... !!!!
					'content' => 'Enable extension'
				],
				'disable' => [
					'type' => 'submit',
					'class' => 'button-main',
					'name' => 'submit',
					'value' => 'disable',
					'id' => 'extensions-disable',
					// TODO: SRSLY, i18n... !!!!
					'content' => 'Disable extension'
				],
				'remove' => [
					'type' => 'submit',
					'class' => 'button-sec',
					'name' => 'submit',
					'value' => 'remove',
					'id' => 'extensions-remove',
					// TODO: SRSLY, i18n... !!!!
					'content' => 'Remove from list'
				]
			]
		];

		$definition = $this->manager->getFullDefinition();

		$data['extensions'] = [];
		foreach ( $definition as $extName => $extData ) {
			$classes = [];
			$isMandatory = $extData['mandatory'];
			$enabled = ( $isMandatory || $extData['enabled'] ) && $extData['exists'];

			$classes = [];
			if ( $isMandatory ) {
				$classes[] = 'ext-extensionManager-mandatory';
			}
			$classes[] = $enabled ? 'ext-extensionManager-enabled' : 'ext-extensionManager-disabled';
			$classes[] = $extData['exists'] ? 'ext-extensionManager-available' : 'ext-extensionManager-missing';

			$data['extensions'][$extName] = [
				'title' => $extName,
				'description' => $extData['details']['description'],
				'note' => $isMandatory ? 'This extension cannot be disabled.' : '',
				'details' => $this->buildDetailsLine( $extData['details'] ),
				'status' => [
					'available' => $extData['exists'] ? 'Available' : 'Missing',
					'enabled' => $enabled ? 'Enabled' : 'Disabled',
				],
				'mandatory' => (int)$isMandatory,
				'action' => $extData['exists'] ?
					( $enabled ? 'disable' : 'enable' ) : 'remove',
				'button' => $extData['exists'] ?
					( $enabled ? $data['buttons']['disable'] : $data['buttons']['enable'] ) :
					$data['buttons']['remove'],
				'classes' => join( ' ', $classes ),
			];
		}

		// Render the template
		$this->_regions['title'] = 'Manage Extensions';
		$this->_regions['content'] = $this->extension['ExtensionManager']->view('manage', $this->skin, 'admin', $data);
		$this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_css('manage', 'admin', $data);
		// $this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_js('manage' 'admin', $data);
		Template::assign($this->_regions);
		Template::render();
	}

	/**
	 * Entrypoint for saving the extension state that was chosen.
	 * Redirects to manage() page
	 */
	public function save() {
		// On submit, save the enabled extensions
		if (isset($_POST['submit'])) {
			$enabled = isset( $_POST[ 'enabled_extension' ] ) ?
				$_POST[ 'enabled_extension' ] : [];
			$enabled = array_unique( array_merge( $enabled, $this->mandatoryExtensions ) );

			$success = $this->manager->updateSettings($enabled);
			if ( $success ) {
				$message = sprintf(
					lang('flash_success'),
					// TODO: i18n...
					'Enabled extensions',
					lang('actions_updated'),
					''
				);

				$flash['status'] = 'success';
				$flash['message'] = text_output($message);
			} else {
				$message = sprintf(
					lang('flash_failure'),
					// TODO: i18n...
					'Enabled extensions',
					lang('actions_updated'),
					''
				);

				$flash['status'] = 'error';
				$flash['message'] = text_output($message);
			}
			// set the flash message
			$this->_regions['flash_message'] = Location::view('flash', $this->skin, 'admin', $flash);
		}

		// We are doing this through its own entrypoint and a redirect
		// so that when the user finishes the "reload" after update, the system
		// already loaded or disabled the extension through the config.
		redirect( 'extensions/ExtensionManager/Manage/manage' );
	}


	/**
	 * Get a property from an array, if it exists
	 * fail gracefully otherwise
	 *
	 * @param  string $prop The name of the key/property
	 * @param  array $arr The array to search in
	 * @return Mixed The value of the key in the array.
	 */
	protected function getPropValue( $prop, $arr ) {
		if ( isset( $arr[$prop] ) && $arr[$prop] ) {
			return $arr[$prop];
		}
		return '';
	}

	/**
	 * Build the details line based on available data
	 * from the details.php file of the given extension.
	 *
	 * @param  array $details Details object
	 * @return string Styled details line
	 */
	protected function buildDetailsLine( $details ) {
		$out = [];
		if ( !$details ) {
			return '';
		}

		$val = $this->getPropValue( 'version', $details );
		if ( $val ) {
			$out[] = $val;
		}

		$author = $this->getPropValue( 'author', $details );
		if ( $author && $this->getPropValue( 'name', $author ) ) {
			$url = $this->getPropValue( 'url', $author );
			if ( $url ) {
				$out[] = '<a href="'.$url.'" target="_blank">' . $author['name'] . '</a>';
			} else {
				$out[] = $author['name'];
			}
		}

		$url = $this->getPropValue( 'url', $details );
		if ( $url ) {
			$out[] = '<a href="'.$url.'" target="_blank">Site</a>';
		}

		return join( ' | ', $out );
	}
}
