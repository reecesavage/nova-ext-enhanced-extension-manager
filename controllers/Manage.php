<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

		$this->install(); // No-op or install

		$this->_regions['nav_sub'] = Menu::build('adminsub', 'manageext');
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
	 * Displays a list of available extensions with their details
	 * and the enable/disable states, allowing the user to change the
	 * enabled extensions.
	 */
	public function manage() {
		$data = [
			// TODO: SRSLY, i18n... !!!!
			'header' => 'Manage Extensions',
			'labels' => [
				'extension_name' => 'Extension name',
				'extension_status' => 'Status',
				'extension_availability' => 'Availability',
				'extension_note' => 'Note',
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
				]
			]
		];

		$definition = $this->manager->getFullDefinition();

		$data['extensions'] = [];
		foreach ( $definition as $extName => $extData ) {
			$classes = [];
			$isMandatory = $extData['mandatory'];
			$enabled = ( $isMandatory || $extData['enabled'] ) && $extData['exists'];

			$data['extensions'][$extName] = [
				'labels' => [
					// TODO: Use i18n!!!!!
					'availability' => $extData['exists'] ? 'Available' : 'Unavailable',
					'enabled' => 'Enabled',
					'note' => $isMandatory ? 'This extension cannot be disabled.' : '',
					'details' => $this->buildDetailsLine( $extData['details'] ),
					'description' => $this->getPropValue( 'description', $extData['details'] ),
				],
				'checkbox' => [
					'disabled' => $isMandatory || !$extData['exists'] ? ' disabled="disabled"' : '',
					'value' => $extData['enabled'], // Remember saved value
				],
				'classes' => '',
				'mandatory' => $isMandatory,
			];

			$classes[] = $extData['exists'] ? 'ext-ExtensionManager-exists' : 'ext-ExtensionManager-noexist';
			if ( $isMandatory ) {
				$classes[] = 'ext-ExtensionManager-mandatory';
			}
			$classes[] = $enabled ? 'ext-ExtensionManager-enabled' : 'ext-ExtensionManager-disabled';
			$data['extensions'][$extName]['classes'] = join( ' ', $classes );
		}

		// Render the template
		$this->_regions['title'] = 'Manage Extensions';
		$this->_regions['content'] = $this->extension['ExtensionManager']->view('manage', $this->skin, 'admin', $data);
		$this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_css('manage', 'admin', $data);
		// $this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_js('manage', $this->skin, 'admin', $data);
		Template::assign($this->_regions);
		Template::render();
	}

	protected function getPropValue( $prop, $arr ) {
		if ( isset( $arr[$prop] ) && $arr[$prop] ) {
			return $arr[$prop];
		}
		return '';
	}

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

	protected function install() {
		$this->load->model('menu_model');
		$expectedLink = 'extensions/ExtensionManager/Manage/';
		$cat = $this->menu_model->get_menu_category( 'manageext' );

		if ( $cat === false ) {
			// Add the category and the menu items
			$insertCat = $this->menu_model->add_menu_category( [
				'menucat_menu_cat' => 'manageext',
				'menucat_name' => 'Manage Extensions',
				'menucat_type' => 'adminsub',
				'menucat_order' => 7
			] );

			// Add item
			$insertItem = $this->menu_model->add_menu_item( [
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
