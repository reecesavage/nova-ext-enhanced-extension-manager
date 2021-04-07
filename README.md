# Nova ExtensionManager
An extension for Nova2 that adds a graphical interface to manage other extensions.

** This is still under development. Use at your own risk **

## Installation
To install this extension, follow these steps:

1. Download the ExtensionManager files from the [latest release](https://github.com/mooeypoo/Nova-ExtensionManager/releases)
2. Extract the files into the folder `nova/application/extensions/ext_nova_enhanced_extension_manager`
3. Find the file `nova/application/config/extensions.php` and add this at the end:

```
require_once( dirname( dirname(__FILE__) ) . '/extensions/ext_nova_enhanced_extension_manager/includes/ConfigManager.php' );
$manager = ( new \ext_nova_enhanced_extension_manager\ConfigManager() )->redefineExtensionConfig( $config['extensions']['enabled'] );
```

You should now have a menu item in your admin control panel for "Manage Extensions". 

## Usage
This extension is intended to override the 'normal' way other nova2 extensions are installed and are enabled.

### For site managers
When you install extensions, the instructions tell you to add an activation line to `nova/application/config/extensions.php`. You **Do not need to do that** if you have the ExtensionManager.

If you do add anything to this file, please make sure that the above code (in Installation step #3) is **always at the end of the file.**

#### To install other extensions
1. Place the extension folder in `nova/application/extensions/`
2. Inside nova Control Panel, click on "Manage Extensions".

**Do not add activation code to `nova/application/config/extensions.php`**

## For extension developers
ExtensionManager is meant to display available extensions for site managers. All extensions will appear, but as a developer, you have the option of adding more information about your extension so the user can see it.

This information lives in a file called `details.json` that is expected to be in the root directory of your extension directory.

The file has to include a JSON object. For example:
```
{
	"name": "ExtensionManager",
	"version": "1.0.0",
	"description": "An extension that can manage other extensions with a visual interface.",
	"url": "https://github.com/mooeypoo/Nova-ExtensionManager",
	"author": {
		"name": "Moriel Schottlender",
		"email": "mooeypoo@gmail.com",
		"url": "http://moriel.smarterthanthat.com/"
	}
}
```

The parameters in the `details.json` file are optional:
* `name` Extension name.
* `version` The current version of your extension.
* `description` A short description about your extension. This description will appear in the extension management screen for the site administrators.
* `url` A link to a site or page about your extension. This link will appear in the extension management screen for the site administrators.
* `author` An object describing the extension author, with the following details:
** `name` Author name
** `email` Author email
** `url` A link to the author's website

This file is optional but is highly encouraged.

## Bugs and feature requests
Please submit bugs or feature requests as issues to this repository.
