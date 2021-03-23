<?php
namespace ExtensionManager;

require_once( dirname( dirname(__FILE__) ) . '/includes/System.php' );

class ExtensionManager {
	protected $ci;
	protected $sys;
	protected $mandatoryExtensions = [ 'ExtensionManager' ];

	function __construct() {
		$this->ci =& get_instance();
		$this->sys = new System();
		$this->sys->install(); // No-op or install

		// Load settings model
		$this->ci->load->model('settings_model', 'settings');

		// Verify settings
		$this->sys->verifySettingsKeysExist();
	}

	/**
	 * Get the full definition from the settings and adjust it
	 * so that it represents extensions that are unavailable/available
	 * on the disk
	 */
	public function getFullDefinition() {
		$newDefinition = [];
		$saved = $this->sys->getValueFromSettings( $this->mandatoryExtensions );
		$available = $this->sys->getExtensionsOnDisk();

		foreach ( $saved as $extName ) {
			$newDefinition[$extName] = [
				'name' => $extName,
				'exists' => in_array( $extName, $available ),
				'enabled' => true,
				'mandatory' => in_array( $extName, $this->mandatoryExtensions ),
				'details' => $this->sys->getExtensionDetails( $extName ),
			];
		}

		// Go over available extensions and see if there's any
		// available that is missing from the list
		// Validate with extensions on the server/disk
		foreach ( $available as $extName ) {
			if ( !isset( $newDefinition[$extName] ) ) {
				$newDefinition[$extName] = [
					'name' => $extName,
					'exists' => true,
					'enabled' => false,
					'mandatory' => in_array( $extName, $this->mandatoryExtensions ),
					'details' => $this->sys->getExtensionDetails( $extName ),
				];
			}
		}

		// Sort by
		// - Alphabetical
		ksort( $newDefinition );
		// - Bring mandatory to top
		uasort( $newDefinition, function ( $a, $b ) {
			return (
				(int)( $b['mandatory'] ) - (int)( $a['mandatory'] )
			);
		} );

		return $newDefinition;
	}

	public function getCurrentValue() {
		return $this->sys->getValueFromSettings();
	}
	/**
	 * Update the setting value and store
	 *
	 * @param string $value Settings value
	 */
	public function updateSettings( $value ) {
		return $this->sys->updateSettings( $value );
	}

    // check if we can disable the extension 
	public function checkRequiredDisableExtension($enabledExtensions,$extensionName)
	{    
		$inRequired['status']='NOK';
       if (!empty($enabledExtensions)) {

       	foreach ($enabledExtensions as $extension)
       	{
       	  $extData= $this->sys->getExtensionDetails( $extension );
          if(!empty($extData))
          {
             $requiredExtensions = isset($extData['requiredExtensions'])?$extData['requiredExtensions']:[];
             
             if(!empty($requiredExtensions))
             {
                if(in_array($extensionName, $requiredExtensions)){
                   $inRequired['status']='OK';
                   $inRequired['data'][]=$extension;
                }
             }
          }


       }


   }
    return $inRequired;
 }


 public function checkRequiredEnableExtension($enabledExtensions,$extensionName)
 {
     $inRequired['status']='NOK';

     if (!empty($extensionName)) {
          $extData= $this->sys->getExtensionDetails( $extensionName );
        
        if(!empty($extData))
        {

        	$requiredExtensions = isset($extData['requiredExtensions'])?$extData['requiredExtensions']:[];
        	 if(!empty($requiredExtensions))
             {
                foreach ($requiredExtensions as $extension)
       	        { 
                  
                  if(!in_array($extension, $enabledExtensions)){
                   $inRequired['status']='OK';
                   $inRequired['data'][]=$extension;
                }

       	        }
             }

          
        }
     }
    
     return $inRequired;
 }


 public function enableUpdateExtension($extension)
 {   

 	$this->ci->db->select('*');
    $this->ci->db->from('menu_items');
    $this->ci->db->like('menu_link', $extension);
    $query = $this->ci->db->get();
    $item = ($query->num_rows() > 0) ? $query->row() : false;   
      if(!empty($item)){
          if($item->menu_display=='n')
          {
             
        $this->ci->db->where('menu_id', $item->menu_id);
        $this->ci->db->update('menu_items', ['menu_display'=>'y']);
        $this->ci->dbutil->optimize_table('menu_items');


          }
      }
 }



  public function disableUpdateExtension($extension)
 {   

 	$this->ci->db->select('*');
    $this->ci->db->from('menu_items');
    $this->ci->db->like('menu_link', $extension);
    $query = $this->ci->db->get();
    $item = ($query->num_rows() > 0) ? $query->row() : false;
 
      if(!empty($item)){
          if($item->menu_display==='y')
          {
             
        $this->ci->db->where('menu_id', $item->menu_id);
        $this->ci->db->update('menu_items', ['menu_display'=>'n']);
        $this->ci->dbutil->optimize_table('menu_items');


          }
      }
 }
}
