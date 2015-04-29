### Wordpress RAD Framework (DEV/Beta Mode)

**PLEASE NOTE: THIS FRAMEWORK IS PUBLIC BETA**

Rapid Developing Framework for Wordpress Plugins includes basis functionality for:

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

If you dont use composer install and composer autoloader it probably would look like:

```php
define('SETCOOKI_WP_AUTOLOAD', 1); //optional
require_once dirname(__FILE__) . '/lib/setcooki/wp/core.php';
```

Once you have included composer autoloader or `core.php` from wp package your ready to boot the the wp rad framework by 
calling:

```
setcooki_boot();
```
which will initialize the framework and set default global variables like log/debug, charset etc. to start the wp framework
with custom config options/values call:

```
setcooki_boot('/path/you/your/config.php', 'your_plugin_name');
```
This will initialize the wp framework with your config values in `/path/you/your/config.php` under your plugin name
or namespace in second argument. you can also pass an array of config files to first argument which results in the key =>
values being merged to one config store available throughout the framework with `Setcooki\Wp\Config` class or `setcooki_config`
shortcut function. passing an array of configs allows to define a global config for values that will not change per environment
and should not be overwritten by plugin user, and allows to define a user customizable custom config file location. the
config file is a php file which is expected to return an array. e.g. the following example show global wp framework options
defined in config file:

```
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

The most likely szenario developing wordpress plugins with this framework is to extend from the `Setcooki\Wp\Plugin` class
and init the plugin from your plugin file that gets bootstrapped/loaded from wordpress once plugin is activated. The base
plugin class comes with minimum needed plugin functionality to get you kick-started. Extend from that class and implement
your basic plugin functionality

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




