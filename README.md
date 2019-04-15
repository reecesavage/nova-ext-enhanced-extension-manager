# Nova ExtensionManager
An extension for Nova2 that adds a graphical interface to manage other extensions.

** This is still under development. Use at your own risk **

## Installation
To install this extension, follow these steps:

1. Download the extension files
2. Extract the extension in your `nova/application/extensions/` directory
3. Find the file `nova/application/config/extensions.php` and add this at the end:

```
require_once( dirname( dirname(__FILE__) ) . '/extensions/ExtensionManager/includes/ConfigManager.php' );
$manager = ( new \ExtensionManager\ConfigManager() )->redefineExtensionConfig( $config['extensions']['enabled'] );
```

You should now have a menu item in your admin control panel for "Manage Extensions".

## Bugs and feature requests
Please submit bugs or feature requests as issues to this repository.
