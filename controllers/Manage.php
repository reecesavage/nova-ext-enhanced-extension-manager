<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once MODPATH.'core/libraries/Nova_controller_admin.php';
require_once __DIR__ . '/../includes/ExtensionManager.php';

class __extensions__ExtensionManager__Manage extends Nova_controller_admin {
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

				$disableExtension= $this->manager->checkRequiredDisableExtension($enabledExtensions,$extensionName);


				if($disableExtension['status']=='NOK')
				{
                     array_splice(
					$enabledExtensions,
					array_search( $extensionName, $enabledExtensions ),
					1
				);
                    $this->manager->updateSettings( $enabledExtensions );
                    $this->manager->disableUpdateExtension( $extensionName );
                    
                    $this->session->set_flashdata('success', "$extensionName extension was successfully disabled.");

				}else {
                    $message= implode(',', $disableExtension['data']);
                    $this->session->set_flashdata('error', "$extensionName extension is used in $message extensions. You can't disable it.");
				}


				
			} else if ( $action === 'enable' ) {
				// Add it in

              $enableExtension= $this->manager->checkRequiredEnableExtension($enabledExtensions,$extensionName);

              if($enableExtension['status']=='NOK')
				{
                  

                   $enabledExtensions[] = $extensionName;
                   // Save the new setting
				$this->manager->updateSettings( $enabledExtensions );
				$this->manager->enableUpdateExtension( $extensionName );
                


				$this->session->set_flashdata('success', "$extensionName extension was successfully enabled.");

				}else {
                      $message= implode(',', $enableExtension['data']);
                    $this->session->set_flashdata('error', "$message extension need to enable before $extensionName extension.");
				}
               

				
			}
			
			
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
				],
				'upload' => [
					'type' => 'submit',
					'class' => 'button-main',
					'name' => 'submit',
					'value' => 'upload',
					'id' => 'extensions-upload',
					// TODO: SRSLY, i18n... !!!!
					'content' => 'Upload Zip'
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
				'version'=>isset($extData['details']['version'])?$extData['details']['version']:'',
				'required_extension'=>(isset($extData['details']['requiredExtensions']) && is_array($extData['details']['requiredExtensions']))?implode(',', $extData['details']['requiredExtensions']):'',

				'repository'=>isset($extData['details']['url'])?$extData['details']['url']:'',
				'mandatory' => (int)$isMandatory,
				'action' => $extData['exists'] ?
					( $enabled ? 'disable' : 'enable' ) : 'remove',
				'button' => $extData['exists'] ?
					( $enabled ? $data['buttons']['disable'] : $data['buttons']['enable'] ) :
					$data['buttons']['remove'],

					'upload'=>$data['buttons']['upload'],

				'classes' => join( ' ', $classes ),
			];
           

		}

		

		// Render the template
		$this->_regions['title'] = 'Manage Extensions';
		$this->_regions['content'] = $this->extension['ExtensionManager']->view('manage', $this->skin, 'admin', $data);
		$this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_css('manage', 'admin', $data);
		 $this->_regions['javascript'] .= $this->extension['ExtensionManager']->inline_js('manage', 'admin', $data);
		Template::assign($this->_regions);
		Template::render();
	}


	public function upload($extensionName)
	{
		$extensionName = urldecode( $extensionName );

		if ( isset( $_POST['submit'] ) ) {
         
           $uploadPath=   dirname(__FILE__).'/../upload/';
           $tmp_file = $_FILES['upload_file']['tmp_name'];
          $name= $_FILES['upload_file']['name'];
          if (move_uploaded_file($tmp_file, $uploadPath.$name)) {
           $uploadFile =  dirname(__FILE__).'/../upload/'.$name;
            if (file_exists( $uploadFile ) ) { 
                $zip = new ZipArchive;
				$res = $zip->open($uploadFile);
				if ($res === TRUE) {
  				$zip->extractTo($uploadPath);
  				$zip->close();
  				
                $extensionFile= dirname(__FILE__)."/../upload/$extensionName";
                 if (file_exists( $extensionFile ) ) { 
                    
                   $details= $this->manager->getExtensionDetail( $extensionName );

                   if(!empty($details))
                   	{

                   		
                   		$folder = isset($details['folder'])?$details['folder']:$extensionName;
                      $upgradeable= isset($details['upgradeable'])?$details['upgradeable']:'no';
                      if($upgradeable=='no')
                      {
                         
                         $this->manager->moveExtension( $folder );


                      }else {

                      	  $configFiles= isset($details['configFiles'])?$details['configFiles']:[];
                      	  if($configFiles)
                      	  { 
                      	  	 
                      	  	foreach ($configFiles as $configFile)
                      	  	{   
                                $src=  APPPATH.'extensions/'.$folder.'/'.$configFile;
                                $des= APPPATH.'extensions/ExtensionManager/upload/'.$folder.'/'.$configFile;
                                if ( file_exists( $src) ) {
									
                                  copy($src,$desc);

								}

                      	  	}
                      	  }
                          $this->manager->moveExtension( $folder );
                      }
                   	}else {
                     $error='detail.json not found';
                   	} 

                 }else {
                 	 $error='Upload the zip file in correct extension';
                 }
                
                } else {
                  $error='Unzip is not successful';
                }

              }else {
              	$error='Zip file does not exists';
              }

		  }
         else {
         $error="File not uploaded";

        
       }

		   
        if(isset($error))
        {
          $this->session->set_flashdata('error', "$error");
        }else {
        	$this->session->set_flashdata('success', "Extension updated successfully");
        }
    }

        
		redirect( 'extensions/ExtensionManager/Manage/manage' );
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

		

		$author = $this->getPropValue( 'author', $details );



		if ( $author && $this->getPropValue( 'name', $author ) ) {


			$url = $this->getPropValue( 'url', $author );
			if ( $url ) {
				$out[] = '<a href="'.$url.'" target="_blank">' . $author['name'] . '</a>';
			} else {
				$out[] = $author['name'];
			}
		}

			$val = $this->getPropValue( 'email', $author );
		if ( $val ) {
			$out[] = $val;
		}
		

		return join( ' | ', $out );
	}
}
