### Wordpress RAD Framework (DEV/Beta Mode)

**PLEASE NOTE: THIS FRAMEWORK IS PUBLIC BETA**

Rapid Developing Framework for Wordpress Plugins\Themes includes basis functionality for:

* Easy plugin boot-strapping
* Multi-site plugin support
* Loose coupling of components
* Plugin configuration handling with custom user overwriting possibilities
* Plugin MVC architecture with controller, view and template classes
* Wordpress class wrapper for wp option handling
* Cache Library with apc/memcache/file drivers
* Shortcut function for fast access
* Error/exception handling and logging
* ... and more

### Install

either by composer install with:

```javascript
"repositories": [{
    "type": "vcs",
    "url": "https://github.com/setcooki/wp"
}]
,
"require": {
    "php": ">=5.3.3",
    "setcooki/wp": "@dev",
}
```

or download manual zip and use framework without composer support.

### Bootstrap

To initialize framework you need to include the /core.php file. if you use composer the most likely way of bootstrapping
and loading the framework would be like:

```php
define('SETCOOKI_WP_AUTOLOAD', 0); //optional
require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/lib/vendor/setcooki/wp/core.php';
```

If you dont use composer install and composer autoloader it looks like:

```php
define('SETCOOKI_WP_AUTOLOAD', 1); //optional
require_once dirname(__FILE__) . '/lib/setcooki/wp/core.php';
```

setting `SETCOOKI_WP_AUTOLOAD` will take care that everything in `setcooki/wp` ns will be loaded. to use the autoloader
for external class loading pass an array of absolute include dirs with `SETCOOKI_WP_AUTOLOAD_DIRS` option. each dir can be
passed as string path or array where at index 0 the dir path is stored and in index 1 optional ns value for psr-0 loading

Once you have included composer autoloader or `core.php` from wp package your ready to boot the the wp rad framework by 
calling:

```php
setcooki_boot();
```
which will initialize the framework and set default global variables like log/debug, charset etc. to start the wp framework
with custom config options/values call:

```php
setcooki_boot('/path/you/your/config.php', 'your_ns_name');
```
This will initialize the wp framework with your config values in `/path/you/your/config.php` under your plugin/theme name
or namespace in second argument. you can also pass an array of config files to first argument which results in the key =>
values being merged to one config store available throughout the framework with `Setcooki\Wp\Config` class or `setcooki_config`
shortcut function. passing an array of configs allows to define a global config for values that will not change per environment
and should not be overwritten by plugin/theme user, and allows to define a user customizable custom config file location. the
config file is a php file which is expected to return an array. e.g. the following example show global wp framework options
defined in config file:

```php
<?php

if(!defined('ABSPATH')) die();

return array
(
    'wp' => array
    (
        'LOG' => true,
        'DEBUG => true,
        'HANDLE_ERROR => true,
        'HANDLE_EXCEPTION => true,
        ...
    )
);

?>
```

PLEASE refer to `core.php` file for all configurable wp framework options


### Usage

#### Plugins

The most likely scenario developing wordpress plugins/themes with this framework is to extend from the `Setcooki\Wp\Wp` class
and init the plugin/theme from your plugin/theme class that gets bootstrapped/loaded from wordpress once plugin is activated. 
The base plugin/theme class comes with minimum needed functionality to get you kick-started. Extend from that class and
implement your basic plugin/theme functionality

```php
class MyPlugin extends \Setcooki\Wp\Plugin
{
    public $options = array();

    public function __construct($options = null)
    {
        parent::__construct($options)
    }
    
    public function init()
    {
        //init your plugin logic
    }

    public function activate()
    {
        //your plugin activation logic
    }
    
    public function deactivate()
    {
        //your plugin deactivation logic
    }
    
    public function uninstall()
    {
        //your plugin uninstall logic
    }
}
```

Bootstrap your plugin in your `/wp-content/plugins/my-plugin/my-plugin.php` plugin file forwarding wp´s 'init' hook to your
plugin class which needs to extend from `\Setcooki\Wp\Plugin` and which needs to call `parent::__construct($options)` in
class constructor. Your plugin logic starts when wp calls your plugins 'init' method.  

```php
if(function_exists('add_action'))
{
    $options = array
    (
        //your plugin options
    );
    add_action('init', array(new MyPlugin($options), 'init'));
}
```

to bootstrap multiple instances of same plugin in a multi-site wp environment modify bootstrapping to:

```php
if(function_exists('add_action'))
{
    $id = get_current_blog_id(); //or whatever id you want the instance to be created under
    $options = array
    (
        //plugin options (which can be different for each instance) 
    );
    add_action('init', array(MyNamespace\MyPlugin::instance($id, $options), 'init'));
}
```

#### Themes

The same applies for themes. extend from:

```php
class MyTheme extends \Setcooki\Wp\Theme
{
    public $options = array();

    public function __construct($options = null)
    {
        parent::__construct($options)
    }
    
    public function init()
    {
        //init your theme logic
    }

    public function switchTheme()
    {
        //switch theme hook
    }

    public function afterSetup()
    {
        //after setup hook
    }
    
    public function afterSwitch()
    {
        //after switch hook
    }
}
```

#### Hints/Tipps

If your are locally developing with symlinks to /wp-content folders like /plugins etc to separate content from wordpress core you
may find that plugins fail to load correctly due to certain php functions/constants like __FILE__ returning the symlink
dir value thus provoking loading errors. to fix issues related with symlinks try the following:

1)
in your /wp-config.php before /wp-settings.php is loaded set the following constants

```php
define('WP_PLUGIN_DIR', realpath(dirname(__FILE__) . '/../your_path_to_plugin_folder'));
define('PLUGINDIR', realpath(dirname(__FILE__) . '/../your_path_to_plugin_folder'));
```

this will prevent wordpress setting the plugin path from WP_CONTENT_DIR which when having symlinked /plugins folder outside
wordpress root will return a wrong value