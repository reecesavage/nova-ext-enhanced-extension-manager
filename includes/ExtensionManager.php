<?php
namespace ext_nova_enhanced_extension_manager;

require_once( dirname( dirname(__FILE__) ) . '/includes/System.php' );

class ExtensionManager {
	protected $ci;
	protected $sys;
	protected $mandatoryExtensions = [ 'ext_nova_enhanced_extension_manager' ];

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
			$details=$this->sys->getExtensionDetails( $extName );
		
            if(!empty($details))
            {
			$newDefinition[$extName] = [
				'name' => isset($details['name'])?$details['name']:$extName,
				'exists' => in_array( $extName, $available ),
				'enabled' => true,
				'mandatory' => in_array( $extName, $this->mandatoryExtensions ),
				'details' => $details,
			];
			}
		}

		// Go over available extensions and see if there's any
		// available that is missing from the list
		// Validate with extensions on the server/disk
		foreach ( $available as $extName ) {
			if ( !isset( $newDefinition[$extName] ) ) {
				$details=$this->sys->getExtensionDetails( $extName );
                 if(!empty($details))
            {
				$newDefinition[$extName] = [
					'name' => isset($details['name'])?$details['name']:$extName,
					'exists' => true,
					'enabled' => false,
					'mandatory' => in_array( $extName, $this->mandatoryExtensions ),
					'details' => $details,
				];
			}
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


 public function getExtensionDetail( $extName ) {
		$extDetailsFilePath = APPPATH.'extensions/ext_nova_enhanced_extension_manager/upload/'.$extName.'/details.json';
		if ( !file_exists( $extDetailsFilePath ) ) {
			return [];
		}

		// Go over the extensions folder
		$file = file_get_contents( $extDetailsFilePath );
		$details = json_decode( $file, true );

		return $details;
	}


	public function moveExtension($extName)
	{


       $details= $this->sys->getExtensionDetails( $extName );
       $time= time();
       $moveFileName=$extensions.'-'.$time;
       if(!empty($details))
       {
       	$version= isset($details['version'])?$details['version']:'';
       	$moveFileName = $extName.'-'.$version.'-'.$time;
       }

       $src=  APPPATH.'extensions/'.$extName.'/';
       $backupFolder=  APPPATH.'extensions/backup/';

       if (!file_exists($backupFolder)) {
             mkdir($backupFolder, 0777, true);
		}

       $dst= APPPATH.'extensions/backup/'.$moveFileName.'/';
      
       $this->sys->rcopy($src, $dst);
       $this->sys->rrmdir($src);

       $dst=  APPPATH.'extensions/'.$extName.'/';
       $src= APPPATH.'extensions/ext_nova_enhanced_extension_manager/upload/'.$extName.'/';
       $this->sys->rcopy($src, $dst);
       $this->sys->rrmdir($src);
	}


	public function GetFilesAndFolder($del=false) {
    /*Which file want to be escaped, Just add to this array*/

    $Directory= dirname(__FILE__).'/../upload/';
    $EscapedFiles = [
        '.',
        '..',

    ]; 

    $FilesAndFolders = [];
    /*Scan Files and Directory*/
    $FilesAndDirectoryList = scandir($Directory);
    foreach ($FilesAndDirectoryList as $SingleFile) {
        if (in_array($SingleFile, $EscapedFiles)){
            continue;
        }


        $ext = pathinfo($SingleFile, PATHINFO_EXTENSION);

        /*Store the Files with Modification Time to an Array*/

        if($ext!='zip')
        {   

        	if($del==false)
        	{

        	 $FilesAndFolders[$SingleFile] = filemtime($Directory . '/' . $SingleFile);
        	}else {
        		$src= $Directory.$SingleFile;
        		 $this->sys->rrmdir($src);
        	}
        }
        
    }

    
    /*Sort the result as your needs*/
    arsort($FilesAndFolders);
    $FilesAndFolders = array_keys($FilesAndFolders);

    return ($FilesAndFolders) ? $FilesAndFolders : false;
}


}
