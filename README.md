### Wordpress RAD Framework (Dev/Beta Mode)

Rapid Developing Framework for Wordpress Plugins includes basis functionality for:

* Easy plugin boot-strapping
* Loose coupling of components
* Plugin configuration handling with custom user overwriting possibilities
* Plugin MVC architecture with controller, view and template classes
* Wordpress class wrapper for wp option handling
* Cache Library with different drivers
* Shortcut function for fast access
* Error and exception handling and logging
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

or download manual zip and use framwork without composer support.

### Bootstrap

to initialize framwork you need to include the /core.php file. if you use composer the most likely way of bootstrapping
and loading the framework would be like:

```php
define('SETCOOKI_WP_AUTOLOAD', 0);
require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/lib/vendor/setcooki/wp/core.php';
```

if you dont use composer install and composer autoloader it probably would look like:

```php
define('SETCOOKI_WP_AUTOLOAD', 1);
require_once dirname(__FILE__) . '/lib/setcooki/wp/core.php';
```

... tbd

