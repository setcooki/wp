<?php

/**
 * set start init timestamp
 */
define('SETCOOKI_WP_START', microtime(true));

/**
 * define global config constants
 */
if(!defined('SETCOOKI_NS'))
{
    define('SETCOOKI_NS', 'SETCOOKI_WP');
}
define('SETCOOKI_WP_PHP_VERSION',                           '5.3.3');
define('SETCOOKI_WP_LOG',                                   'LOG');
define('SETCOOKI_WP_DEBUG',                                 'DEBUG');
define('SETCOOKI_WP_CHARSET',                               'CHARSET');
define('SETCOOKI_WP_ERROR_HANDLER',                         'ERROR_HANDLER');
define('SETCOOKI_WP_EXCEPTION_HANDLER',                     'EXCEPTION_HANDLER');
define('SETCOOKI_WP_AUTOLOAD_DIRS',                         'AUTOLOAD_DIRS');

/**
 * set global constants
 */
if(!defined('DIRECTORY_SEPARATOR'))
{
    define('DIRECTORY_SEPARATOR', ((isset($_ENV['OS']) && strpos('win', $_ENV["OS"]) !== false) ? '\\' : '/'));
}
if(!defined('NAMESPACE_SEPARATOR'))
{
    define('NAMESPACE_SEPARATOR', '\\');
}
if(!defined('PATH_SEPARATOR'))
{
    define('PATH_SEPARATOR', ':');
}

/**
 * test php version
 */
if(version_compare(PHP_VERSION, SETCOOKI_WP_PHP_VERSION, '<'))
{
    die("setcooki/wp needs php version > ".SETCOOKI_WP_PHP_VERSION." to run - your version is: " .PHP_VERSION . PHP_EOL);
}

/**
 * stripslashes on input variables
 */
if(function_exists('get_magic_quotes_gpc') && call_user_func('get_magic_quotes_gpc') === 1)
{
    $_GET = stripslashes_deep($_GET);
    $_POST = stripslashes_deep($_POST);
    $_COOKIE = stripslashes_deep($_COOKIE);
    $_REQUEST = stripslashes_deep($_REQUEST);
}

/**
 * load core files
 */
require_once dirname(__FILE__) . '/wp.php';
require_once dirname(__FILE__) . '/helper.php';
require_once dirname(__FILE__) . '/src/Wp.php';
require_once dirname(__FILE__) . '/src/Config.php';

/**
 * register autoloader
 */
if(defined('SETCOOKI_WP_AUTOLOAD') && (bool)constant('SETCOOKI_WP_AUTOLOAD'))
{
    @spl_autoload_register(array('\Setcooki\Wp\Wp', 'autoload'), false);
}

/**
 * init wp framework with config which can be one or multiple config files as array. the init function will registered
 * the config values and set global values in $GLOBALS namespace
 *
 * @param string|array $config expects options config file(s) absolute path as single value or array
 * @param string $ns expects the namespace identifier
 * @return array
 */
function setcooki_boot($config, $ns)
{
    $ns = trim((string)$ns);
    $wp = array
    (
        SETCOOKI_WP_LOG                 => false,
        SETCOOKI_WP_DEBUG               => false,
        SETCOOKI_WP_CHARSET             => 'utf-8',
        SETCOOKI_WP_ERROR_HANDLER       => true,
        SETCOOKI_WP_EXCEPTION_HANDLER   => true,
        SETCOOKI_WP_AUTOLOAD_DIRS       => null
    );
    $config = \Setcooki\Wp\Config::init($config, $ns);
    if(($w = $config->get('wp', false)) !== false)
    {
        $wp = (array)$w + $wp;
    }
    if(defined('WP_DEBUG') && (bool)WP_DEBUG)
    {
        $wp[SETCOOKI_WP_DEBUG] = true;
    }
    if(defined('WP_DEBUG_LOG') && (bool)WP_DEBUG_LOG)
    {
        $wp[SETCOOKI_WP_LOG] = true;
    }
    $config->set('wp', $wp);
    foreach($wp as $k => $v)
    {
        $GLOBALS[SETCOOKI_NS][strtoupper(trim($k))] = $v;
    }
    if(!empty($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_ERROR_HANDLER]) && (bool)$GLOBALS[SETCOOKI_NS][SETCOOKI_WP_ERROR_HANDLER])
    {
        set_error_handler('\Setcooki\Wp\Error::handler');
    }
    if(!empty($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_EXCEPTION_HANDLER]) && (bool)$GLOBALS[SETCOOKI_NS][SETCOOKI_WP_EXCEPTION_HANDLER])
    {
        set_exception_handler('\Setcooki\Wp\Exception::handler');
    }
    return $GLOBALS[SETCOOKI_NS];
}


/**
 * setter/getter for global configs. setter if second argument is not _NIL_. if no argument is set will return complete
 * config object
 *
 * @param null|string $key expects the config value key
 * @param string|mixed $value expects the config value
 * @param null|mixed $default expects optional default return value
 * @return mixed
 * @throws Exception
 */
function setcooki_conf($key = null, $value = '_NIL_', $default = null)
{
    if(is_null($key) && $value === '_NIL_')
    {
        return (isset($GLOBALS[SETCOOKI_NS])) ? $GLOBALS[SETCOOKI_NS] : array();
    }
    $key = strtoupper(trim($key));
    if($value !== '_NIL_')
    {
        if(!isset($GLOBALS[SETCOOKI_NS]))
        {
            $GLOBALS[SETCOOKI_NS] = array();
        }
        return $GLOBALS[SETCOOKI_NS][$key] = $value;
    }else{
        if(isset($GLOBALS[SETCOOKI_NS]) && array_key_exists($key, $GLOBALS[SETCOOKI_NS]))
        {
            return $GLOBALS[SETCOOKI_NS][$key];
        }else{
            return setcooki_default($default);
        }
    }
}


/**
 * imports a class be class name using a build in autoloader
 *
 * @param string $class expects the class name with ns or without
 * @return void
 */
function setcooki_import($class)
{
    if(class_exists('Setcooki\Wp\Wp'))
    {
        Setcooki\Wp\Wp::autoload($class);
    }
}


/**
 * get the current path or uri by type which can be "root", "theme", "plugin". the path can be returned as absolute, relative
 * path or even as uri with site url prepended
 *
 * @param string|null $type expects the path to get which defaults to "root"
 * @param bool $relative expects boolean true|false to either get path absolute or relative
 * @param bool $url expects boolean true|false to either get the path as uri or path
 * @return mixed|null|string
 */
function setcooki_path($type = null, $relative = false, $url = false)
{
    if($type === null)
    {
        $type = 'root';
    }

    $path = null;

    if(defined('ABSPATH'))
    {
        $root = rtrim(ABSPATH, '/');
    }else if(isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])){
        $root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
    }else{
        $root = realpath(rtrim(__DIR__, '/') . '/../../../../../../../');
    }

    switch(strtolower($type))
    {
        case 'root':
            $path = $root;
            break;
        case 'theme':
            $path = get_stylesheet_directory();
            break;
        case 'themes':
            $path = (function_exists('get_theme_root')) ? get_theme_root() : ABSPATH . 'wp-content/themes';
            break;
        case 'plugin':
            //TODO: better to use plugins_url()
            $path = preg_replace('/(.*)\/(plugins)\/([^\/]{1,}).*/i', '$1/$2/$3', dirname(__FILE__));
            break;
        case 'plugins':
            $path = (defined('WP_PLUGIN_DIR')) ? WP_PLUGIN_DIR : ABSPATH . 'wp-content/plugins';
            break;
        default;
            return '';
    }

    $path = DIRECTORY_SEPARATOR . trim($path, ' ' . DIRECTORY_SEPARATOR);
    if((bool)$relative)
    {
        $path = preg_replace('=^\/?'.addslashes($root).'=i', '', $path);
        if((bool)$url)
        {
            if(function_exists('get_site_url'))
            {
                $url = get_site_url();
            }else if(strtolower(php_sapi_name()) !== 'cli'){
                $url = ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (int)$_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://' . $_SERVER['SERVER_NAME'];
            }else{
                $url = '';
            }
            return trim($url, DIRECTORY_SEPARATOR) . $path;
        }else{
            return $path;
        }
    }else{
        return $path;
    }
}


/**
 * getter for configs registered with Setcooki\Wp\Config class. see Setcooki\Wp\Config::get for more
 *
 * @see Setcooki\Wp\Config::get
 * @param null|string $key expects a config key or path
 * @param null|mixed $default expects optional return value
 * @param null|string $ns expects the optional namespace prior set with config class
 * @return mixed
 * @throws Exception
 */
function setcooki_config($key = null, $default = null, $ns = null)
{
    if(class_exists('Setcooki\Wp\Config') && Setcooki\Wp\Config::hasInstance($ns))
    {
        return Setcooki\Wp\Config::g($key, $default, $ns);
    }else{
        return setcooki_default($default);
    }
}


/**
 * wordpress option handling shortcut function to set/get option with option name with or without path "." syntax
 *
 * @see Setcooki\Wp\Option
 * @param string $name expects option name
 * @param mixed $value expects option value
 * @param bool $default expects optional default return value for get
 * @return bool|mixed
 */
function setcooki_option($name, $value = '_NIL_', $default = false)
{
    if($value !== '_NIL_')
    {
        return Setcooki\Wp\Option::save($name, $value);
    }else{
        return Setcooki\Wp\Option::get($name, $default);
    }
}


/**
 * get the setcooki plugin/theme base class instance from any context by calling this function. as long as this function
 * is called inside plugin or theme directory will return the correct instance governing the whole theme or plugin. if you
 * pass a instance id in first argument will try to lookup existing instance initialized under the id value which is a
 * combination of "($type):{$name} e.g. "plugin:foo" which is the id of a plugin named foo where foo is the folder name
 * of the plugin/theme! if no instance is found will throw exception which can be overriden by passing any other default
 * value in second argument
 *
 * @see \Setcooki\Wp\Wp::wp
 * @param null|string $id expects optional instance id hint
 * @param null|mixed $default expects optional default value
 * @return \Setcooki\Wp\Wp
 * @throws Exception
 */
function setcooki_wp($id = null, $default = null)
{
    if(is_null($default))
    {
        $default = new \Exception("sorry! no clue who i am!");
    }
    if(($id = \Setcooki\Wp\Wp::wp($id, false)) !== false)
    {
        return $id;
    }else{
        return setcooki_default($default);
    }
}


/**
 * setter/getter shortcut method for \Setcooki\Wp\Wp::store function
 *
 * @see \Setcooki\Wp\Wp::store
 * @param null|string $name expects the object name in setter/getter mode
 * @param null|mixed $value expects the value to set in setter mode
 * @param null|mixed $default expects the default return value in getter mode
 * @return mixed
 */
function setcooki_store($name, $value = null, $default = null)
{
    return setcooki_wp(null, $default)->store($name, $value, $default);
}


if(!function_exists('setcooki_cache'))
{
    /**
     * cache setter/getter function. depending on the arguments passed will either get or set from cache. if all arguments are
     * null will purge = clear cache entirely. if the first argument, the cache key, is set and the second argument is _NIL_
     * will act as cache getter. if the cache key = first argument is set and the second argument is not _NIL_ will act as
     * cache setter setting value to cache for x seconds as passed in third argument lifetime. if more then one cache instances
     * are globally set use the namespace identifier in fourth argument
     *
     * @param null|string $key expects optional cache key value
     * @param mixed $value expects optional cache value in setter mode
     * @param null|int $lifetime expects optional cache lifetime in seconds in setter mode
     * @param null|string $ns expects optional cache instance namespace string
     * @return bool|null|string
     */
    function setcooki_cache($key = null, $value = '_NIL_', $lifetime = null, $ns = null)
    {
        if(class_exists('Setcooki\\Wp\\Cache\\Cache', true))
        {
            if(Setcooki\Wp\Cache\Cache::hasInstance())
            {
                try
                {
                    $class = Setcooki\Wp\Cache\Cache::instance($ns);
                    if(func_num_args() > 0)
                    {
                        if(func_num_args() >= 2 && $value !== '_NIL_')
                        {
                            $class->set($key, $value, $lifetime);
                            return $value;
                        }else{
                            return $class->get($key, ((func_num_args() === 1) ? false : null));
                        }
                    }else{
                        return $class->purge(false);
                    }
                }
                catch(\Exception $e){};
            }
        }
        return ((func_num_args() === 1) ? false : null);
    }
}


if(!function_exists('setcooki_log'))
{
    /**
     * logger shortcut function. will send logging message to logger class if class is instantiated. if not will use php´s
     * default trigger error function to redirect logging message. the first argument can be either a string, array or instance
     * of Exception
     *
     * @param mixed $message expects log message
     * @param int $type expects the log type
     * @return bool
     */
    function setcooki_log($message, $type = LOG_ERR)
    {
        if(class_exists('Setcooki\\Wp\\Logger', true))
        {
            if(Setcooki\Wp\Logger::hasInstance())
            {
                return call_user_func_array(array('Setcooki\\Wp\\Logger', 'l'), func_get_args());
            }
        }
        if($message instanceof \Exception)
        {
            $message = $message->getMessage() . ' in ' . $message->getFile() . ':' . $message->getLine();
        }else if(is_array($message)){
            $message = setcooki_sprintf((string)$message[0], ((sizeof($message) > 1) ? array_slice($message, 1, sizeof($message)) : null));
        }else{
            $message = trim((string)$message);
        }

        ob_start();
        trigger_error($message);
        ob_end_clean();

        return $message;
    }
}


if(!function_exists('setcooki_event'))
{
    /**
     * shortcut function to wp themes/plugins global event dispatcher with listen or trigger capabilities. see
     * concrete implementation for trigger and listen interface here \Setcooki\Wp\Events\Dispatcher
     *
     * @since 1.1.2
     * @see \Setcooki\Wp\Events\Dispatcher::listen
     * @see \Setcooki\Wp\Events\Dispatcher::trigger
     * @param mixed $event expects event name(s) or mixed value depending on trigger or listen mode
     * @param mixed $mixed expects listener object or event object/params depending on trigger or listen mode
     * @param null|bool|int $flag expects int for priority in listen mode and boolean halt in trigger mode
     * @return bool|mixed
     */
    function setcooki_event($event, $mixed = null, $flag = null)
    {
        $wp = setcooki_wp();
        if($wp->stored('dispatcher'))
        {
            if(!is_array($event))
            {
                $event = array($event);
            }
            if(!empty($event))
            {
                if(stripos($event[0], 'trigger:') !== false)
                {
                    return $wp->store('dispatcher')->trigger((string)$event[0], $mixed, (bool)$flag);
                }else{
                    return $wp->store('dispatcher')->listen($event, $mixed, (int)$flag);
                }
            }
        }
        return false;
    }
}


if(!function_exists('setcooki_include'))
{
    /**
     * include partials, template snippets, etc. from theme location with this function. works with child/parent theme
     * setups where function will search for file first in child theme then in parent theme. the first argument expects
     * a file name/path from active theme root with or without file extension that if not set will default to .php. the second
     * argument allows for passing variables to be available in included file. the third argument when boolean true will
     * also make vars available in global namespace. use the fourth argument when included file output should be buffered
     * and buffer returned
     *
     * @param string $file expects the file name/path relative to theme root with or without extension
     * @param null|mixed $vars expects array with vars (key => value) pairs to make available for included file
     * @param bool $global expects optional boolean flag for whether making vars also available in global namespace
     * @param null|string|true $buffer expects a string or boolean true to return include buffer
     * @return null|string
     */
    function setcooki_include($file, $vars = null, $global = false, &$buffer = null)
    {
        $file = DIRECTORY_SEPARATOR . trim(str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, (string)$file), ' \\/.');
        if(stripos($file, '.') === false)
        {
            $file = $file . '.php';
        }

        if(!empty($vars))
        {
            if(!is_array($vars))
            {
                $vars = (array)$vars;
            }
            extract((array)$vars);
            if((bool)$global)
            {
                while(list($key , $val) = each($vars))
                {
                    $GLOBALS[$key] = $val;
                }
            }
        }

        if($buffer === true || !empty($buffer))
        {
            ob_start();
        }

        if(file_exists(get_stylesheet_directory() . $file))
        {
            require get_stylesheet_directory() . $file;
        }else if(get_template_directory() . $file){
            require get_template_directory() . $file;
        }

        if(!empty($vars) && is_array($vars))
        {
            foreach($vars as $key => $var)
            {
                unset(${$key});
            }
        }

        if($buffer === true || !empty($buffer))
        {
            return (string)$buffer .= trim(ob_get_clean());
        }else{
            return null;
        }
    }
}


if(!function_exists('setcooki_component'))
{
    /**
     * shortcut function to render component by passing component instance in first argument or id previously registered
     * with component register functions. pass options params in second argument. if third argument is null will echo
     * rendered component. if return is boolean true will return the rendered component output. if string will concat
     * the component output to string. the function can also be used to register components when first argument is a component
     * id and second is an instance of component
     *
     * @param string|mixed $component expects component instance or id
     * @param null|mixed $mixed expects optional params to pass or instance of component when using function to register components
     * @param null $return
     * @return null|string
     */
    function setcooki_component($component, $mixed = null, &$return = null)
    {
        if((is_int($component) || is_string($component)) && (is_object($mixed) && is_subclass_of($mixed, 'Setcooki\Wp\Component')))
        {
            return \Setcooki\Wp\Component::register($component, $mixed);
        }
        if(!is_object($component) && !\Setcooki\Wp\Component::isRegistered($component))
        {
            return null;
        }else{
            if($return === true){
                return \Setcooki\Wp\Component::execute($component, $mixed);
            }else if(is_string($return)){
                $return .= \Setcooki\Wp\Component::execute($component, $mixed);
            }else{
                echo \Setcooki\Wp\Component::execute($component, $mixed);
            }
        }
    }
}


if(!function_exists('setcooki_filter'))
{
    /**
     * shortcut function for wp´s add_filter function to use with setcooki/wp filter classes. the function works much like
     * the default add_filter function except that instead of expecting a callable will also except instances of \Setcooki\Wp\Filter
     * and strings that define registered filter chains. also passing a third argument $params allows for passing any type
     * of parameter to the filter action so that its no longer necessary storing needed variables in global namespace. see
     * wp´s add_filter for more
     *
     * @see add_filter
     * @param string $tag expects the filter tag name
     * @param mixed $filter expects filter/chain object, callable or filter chain name
     * @param null|mixed $params expects optional params to pass
     * @param int $priority expects filter priority
     * @return mixed
     */
    function setcooki_filter($tag, $filter, $params = null, $priority = 10)
    {
        return add_filter((string)$tag, function($value) use ($filter, $params)
        {
            //filter chain or bundle
            if(($filter instanceof \Setcooki\Wp\Filter\Chain) || ($filter instanceof \Setcooki\Wp\Filter))
            {
                return $filter->execute(func_get_args(), $params);
            //filter is a callable
            }else if(is_callable($filter)){
                return call_user_func_array($filter, array(func_get_args(), $params));
            //else try filter chain by name
            }else if(is_string($filter) || is_numeric($filter)){
                return \Setcooki\Wp\Filter\Chain::e($filter, func_get_args(), $params);
            //else return value unaltered
            }else{
                return $value;
            }
        }, (int)$priority);
    }
}


if(!function_exists('setcooki_action'))
{
    /**
     * shortcut function for wp´s add_action function extended with the possibility to pass additional parameters to
     * callback function so no need to globalize parameters anymore. also will accept instance of \Setcooki\Wp\Action as
     * callback which will then execute instances execute method passing callback args in first arg and additional parameters
     * in second argument
     *
     * @see add_action
     * @param string $tag expects the action tag name
     * @param callable|\Setcooki\Wp\Action $action expects callable or instance of \Setcooki\Wp\Action
     * @param null|mixed $params expects optional additional parameters
     * @param int $priority expects the action priority value
     * @param int $args expects the argument count
     * @return mixed
     */
    function setcooki_action($tag, $action, $params = null, $priority = 10, $args = 1)
    {
        return add_action($tag, function($arg) use($action, $params, $args)
        {
            if((int)$args === 1 && (is_array($arg) || is_object($arg)))
            {
                $arg = array($arg);
            }else if((int)$args === 1){
                $arg = (array)$arg;
            }else{
                $arg = func_get_args();
            }
            if(is_array($params) || is_object($params))
            {
                $params = array($params);
            }else{
                $params = (array)$params;
            }
            //action instance
            if($action instanceof \Setcooki\Wp\Action)
            {
                return $action->execute(func_get_args(), $params);
            //action is callable
            }else if(is_callable($action)){
                return call_user_func_array($action, array_merge($arg, $params));
            //else return unaltered
            }else{
                return func_get_args();
            }
        }, $priority, $args);
    }
}


if(!function_exists('setcooki_handle'))
{
    /**
     * if theme/plugin uses a controllers and a controller resolver in theme/plugin init context calling/handling controller
     * action can also be done from any location inside the plugin/theme architecture. suppose you want to handle a specific
     * action inside a custom post template you can use this shortcode method to execute/handle the action.
     *
     * @see \Setcooki\Wp\Controller\Resolver::handle
     * @param null|mixed $action expects optional allowed action
   	 * @param null|object|array|\Setcooki\Wp\Util\Params $params expects optional params
     * @param null|mixed $fallback expects optional fallback - see Router::fail
     * @return string
     * @throws \Setcooki\Wp\Exception
     */
    function setcooki_handle($action, $params = null, $fallback = null)
    {
        $wp = setcooki_wp();
        if($wp->stored('resolver'))
        {
            return $wp->store('resolver')->handle($action, $params, null, null, $fallback);
        }
        return false;
    }
}


if(!function_exists('setcooki_router'))
{
    /**
     * if theme/plugin uses a router in theme/plugin init context the router can be also executed/run from any other
     * location within the theme/plugin architecture. e.g. if the router handles all page request it would be placed in
     * a single line in the themes index.php file as the index file is the last template looked up by wordpress. if a
     * resolver has been initialized the resolver will handle the router instance
     *
     * @param null|mixed $fallback expects optional fallback - see Router::fail
     * @return bool|mixed
     * @throws \Setcooki\Wp\Exception
     */
    function setcooki_router($fallback = null)
    {
        $wp = setcooki_wp();
        if($wp->stored('router'))
        {
            if($wp->stored('resolver'))
            {
                return $wp->store('resolver')->handle($wp->store('router'), null, null, null, $fallback);
            }else{
                return $wp->store('router')->run($fallback);
            }
        }
        return false;
    }
}