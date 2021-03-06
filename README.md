### Wordpress RAD Framework

Rapid developing framework for Wordpress Plugins\Themes includes basis functionality for:

* Easy theme and plugin boot-strapping
* Multi-site plugin support
* Loose coupling of components
* Configuration package for plugin/theme customization
* Controller package with extensive routing possibilities
* Wordpress wrapper´s class for option handling
* Cache Library with apc/memcache/file drivers
* Shortcut function for fast and easy access
* Error/exception handling and logging
* ... and more

### How to (Manual)
1. [Install](#install)
2. [Bootstrap](#bootstrap)
3. [Plugins](#plugins)
4. [Themes](#themes)
5. [Controller/Router](#controller-router)
6. [Scope/Reference](#scope-reference)
7. [Debug/Develop](#debug-develop)
8. [Hints/Tips](#hints-tips)

### Initialization

<a name="install"></a>
#### Install

either by composer install with:

```javascript
"repositories": [{
    "type": "git",
    "url": "https://github.com/setcooki/wp"
}]
,
"require": {
    "php": ">=5.4.4",
    "setcooki/wp": "dev-master",
}
```

or download manual zip and use framework without composer support as you like

<a name="bootstrap"></a>
#### Bootstrap

To initialize framework you need to include either /core.php or /boot.php file. if you use composer the most likely way of bootstrapping
and loading the framework would be like:

```php
require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/lib/vendor/setcooki/wp/core.php';
```

If you do not use composer install and composer autoloader it would look like this:

```php
define('SETCOOKI_WP_AUTOLOAD', 1); //optional
require_once dirname(__FILE__) . '/lib/setcooki/wp/boot.php';
```

if you use multiple instances of the framework the preferred way is to include /boot.php since it prevents multiple inclusions
of /core.php

setting `SETCOOKI_WP_AUTOLOAD` will take care that everything in `\Setcooki\Wp` namespace will be loaded. to use the autoloader
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
setcooki_boot('/path/you/your/config.php');
```
This will initialize the wp framework with your config values in `/path/you/your/config.php` under your plugin/theme name which is the plugin/theme folder name. 
you can also pass an array of config files to first argument which results in the key => values being merged to one config store available throughout the 
framework with `Setcooki\Wp\Config` class or `setcooki_config` shortcut function. 
passing an array of configs allows to define a global config for values that will not change per environment
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
        'LOGGER' => null,
        'DEBUG => true,
        'HANDLE_ERROR => true,
        'HANDLE_EXCEPTION => true,
        ...
    )
);

?>
```

IMPORTANT Bootstrap options:

* LOG               = boolean value to enable/disable logging
* LOGGER            = logger instance compatible with `\Setcooki\Wp\Interfaces\Logable` Interface to enable file or any other type of logging
* DEBUG             = boolean value to enable/disable debugging which will output log messages to screen
* CHARSET           = string default charset
* ERROR_HANDLER     = boolean value if enabled will redirect all errors to framework build-in error handling first
* EXCEPTION_HANDLER = boolean value if enabled will redirect all exceptions to framework build-in exception handling first
* AUTOLOAD_DIRS     = array of dirs to autoload

PLEASE refer to `core.php` file for all configurable wp framework options

### Usage

<a name="plugins"></a>
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
    add_action('init', array(new \My\Namespace\Plugin($options), 'init'));
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
    add_action('init', array(\My\Namespace\Plugin::instance($id, $options), 'init'));
}
```

<a name="themes"></a>
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

<a name="controller-router"></a>
#### Controller/Router

Instead of creating classic wp style templates.php (header.php, footer.php, search.php ...) you can use the controller 
package and have a router handle all the request by simply placing a single funtion in your themes index.php. to init
your controller do the following. in your themes initialization:

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
        \Setcooki\Wp\Controller\Resolver::create($this)
            ->register(new \My\Controller\Post($this);
            ->register(new \My\Controller\Header($this);
            ->register(new \My\Controller\Footer($this);
        
        \Setcooki\Wp\Routing\Router::create($this)
            ->add(new Route('url:*', 'Post::get'));
    }
}
```

This example registers your custom controller classes with the resolver instance which is stored inside wp base class to 
be used from any other location in theme/plugin folder. next initialize a router and add routes to be executed. see 
Resolver and Router class for how to. execution of router should only be done in themes index.php file since any usage before
means wordpress may not have initializes post loops etc. in your themes index.php place this single line:

```php
setcooki_router();
```

that's it. nor more templates and partials. everything will be handle by the router and the resolver. subsequently if you
want to not use a router and only the resolver you can execute any registered controller action from anywhere in your code.
best use would be inside your templates like:

```php
<?= setcooki_handle('Header::get', ['foo' => 1]); ?>

<article>
...
</article>

<?= setcooki_handle('Footer::get', ['foo' => 1]); ?>
```

this would execute the header and footer get method

<a name="scope-reference"></a>
#### Scope/Reference

Your theme or plugin need to comply to the minimum wordpress requirements. sometimes this means that you do not have
access to classes instantiated in bootstrapping. for example if you use wordpress custom template files you do not have
access to your themes instance (see above example for theme creation) simply because you don't have a reference variable
available. the usually messy way would be storing your theme class instance (or any other) in `$GLOBALS`. however there is 
a slick way to get your instance - just use:

```php
setcooki_wp();
```

this will return your theme/plugin class instance as long as this function is called inside your theme or plugin folder.
you can even reference other plugins/themes by passing the id:

```php
setcooki_wp('plugin:foo');
```

where id is the combination of the scope (theme|plugin) and the folder name of the plugin (not the style.css name!).
the id or ns value for you plugin/theme can also be retrieved inside the scope of your plugin/theme with:

```php
setcooki_ns();
```

ok fine - but what if i want to have my own classes/objects theme or plugin wide available without using `$GLOBALS` or a sort
of registry?! the `\Setcooki\Wp\Wp` base class has a simple object store implemented - use it in the following ways depending
on where you are in your code you can:

```php
//set
$this->wp->store('foo', $foo);
setcooki_wp()->store('foo', $foo);
setcooki_store('foo', $foo);

//get
$this->wp->store('foo');
setcooki_wp()->store('foo');
setcooki_store('foo');

```

simple as that!

<a name="debug-develop"></a>
#### Debug/Develop

While developing you should enable debug mode which can be enabled with either enabling wordpress debug mode with `define('WP_DEBUG', true);`
or setting `define('SETCOOKI_DEV', true);` before any bootstrapping. this will output all logged errors at the end of the screen. in 
productive mode you can switch from debug to log mode by setting `define('WP_DEBUG_LOG', true);`.
its not necessary to touch `define('WP_DEBUG_DISPLAY', false);` as this is has no affect on setcooki/wp framework.

<a name="hints-tips"></a>
#### Hints/Tips

If your are locally developing with symlinks to /wp-content folders like /plugins etc to separate content from wordpress core you
may find that plugins fail to load correctly due to certain php functions/constants like `__FILE__` returning the symlink
dir value thus provoking loading errors. to fix issues related with symlinks try the following:

1)
in your /wp-config.php before /wp-settings.php is loaded set the following constants

```php
define('WP_PLUGIN_DIR', realpath(dirname(__FILE__) . '/../your_path_to_plugin_folder'));
define('PLUGINDIR', realpath(dirname(__FILE__) . '/../your_path_to_plugin_folder'));
```

this will prevent wordpress setting the plugin path from `WP_CONTENT_DIR` which when having symlinked /plugins folder outside
wordpress root will return a wrong value.