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
define('SETCOOKI_WP_PHP_VERSION', '5.3.3');
define('SETCOOKI_WP_DEBUG', 'DEBUG');
define('SETCOOKI_WP_CONFIG', 'CONFIG');
define('SETCOOKI_WP_CHARSET', 'CHARSET');
define('SETCOOKI_WP_ERROR_HANDLER', 'ERROR_HANDLER');
define('SETCOOKI_WP_EXCEPTION_HANDLER', 'EXCEPTION_HANDLER');

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
if(get_magic_quotes_gpc() === 1)
{
    if(function_exists('json_encode'))
    {
        $_GET       = json_decode(stripslashes(json_encode($_GET, JSON_HEX_APOS)), true);
        $_POST      = json_decode(stripslashes(json_encode($_POST, JSON_HEX_APOS)), true);
        $_COOKIE    = json_decode(stripslashes(json_encode($_COOKIE, JSON_HEX_APOS)), true);
        $_REQUEST   = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS)), true);
    }else{
        die("setcooki/wp needs php function: json_encode to run");
    }
}

/**
 * load core files
 */
require_once dirname(__FILE__) . '/wp.php';
require_once dirname(__FILE__) . '/helper.php';
require_once dirname(__FILE__) . '/src/Plugin.php';
require_once dirname(__FILE__) . '/src/Config.php';

/**
 * register autoloader
 */
if(defined('SETCOOKI_WP_AUTOLOAD') && (bool)constant('SETCOOKI_WP_AUTOLOAD'))
{
    @spl_autoload_register(array('\Setcooki\Wp\Plugin', 'autoload'), false);
}

/**
 * set inital config values and register error and/or exception handler
 *
 * @param null $conf expects options config file
 * @return array
 */
function setcooki_init($conf = null)
{
    $default = array
    (
        SETCOOKI_WP_DEBUG               => false,
        SETCOOKI_WP_CONFIG              => null,
        SETCOOKI_WP_CHARSET             => 'utf-8',
        SETCOOKI_WP_ERROR_HANDLER       => false,
        SETCOOKI_WP_EXCEPTION_HANDLER   => false,
    );
    if(!isset($GLOBALS[SETCOOKI_NS]))
    {
        $GLOBALS[SETCOOKI_NS] = $default;
    }
    if(is_array($conf))
    {
        foreach($conf as $k => $v)
        {
            $k = strtoupper(trim($k));
            if(array_key_exists($k, $conf))
            {
                $GLOBALS[SETCOOKI_NS][$k] = $v;
            }
        }
    }
    if(defined('WP_DEBUG') && (bool)WP_DEBUG)
    {
        $GLOBALS[SETCOOKI_NS][SETCOOKI_WP_DEBUG] = true;
    }
    if(!empty($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_CONFIG]) && is_array($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_CONFIG]))
    {
        \Setcooki\Wp\Config::init
        (
            $GLOBALS[SETCOOKI_NS][SETCOOKI_WP_CONFIG][0],
            $GLOBALS[SETCOOKI_NS][SETCOOKI_WP_CONFIG][1]
        );
    }
    if(!empty($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_ERROR_HANDLER]) && (bool)$GLOBALS[SETCOOKI_NS][SETCOOKI_WP_ERROR_HANDLER])
    {
        set_error_handler(array('\Setcooki\Wp\Error', 'handler'));
    }
    if(!empty($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_EXCEPTION_HANDLER]) && (bool)$GLOBALS[SETCOOKI_NS][SETCOOKI_WP_EXCEPTION_HANDLER])
    {
        set_error_handler(array('\Setcooki\Wp\Exception', 'handler'));
    }
    return $GLOBALS[SETCOOKI_NS];
}


/**
 * setter/getter for global configs. setter if second argument is not _NIL_
 *
 * @param string $key expects the config value key
 * @param string|mixed $value expects the config value
 * @param null|mixed $default expects optional default return value
 * @return mixed
 * @throws Exception
 */
function setcooki_conf($key, $value = '_NIL_', $default = null)
{
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
    if(class_exists('Setcooki\Wp\Plugin'))
    {
        Setcooki\Wp\Plugin::autoload($class);
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
            $path = (function_exists('get_theme_root')) ? get_theme_root() : '';
            break;
        case 'plugin':
            $path = preg_replace('/(.*)\/(plugins)\/([^\/]{1,}).*/i', '$1/$2/$3', dirname(__FILE__));
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
 * getter for configs registered with Setcooki\Wp\Config class
 *
 * @see Setcooki\Wp\Config
 * @param string $ns expects the namespace prior set with config class
 * @param null|string $key expects a config key or path
 * @param null|mixed $default expects optional return value
 * @return mixed
 * @throws Exception
 */
function setcooki_config($ns, $key = null, $default = null)
{
    if(class_exists('Setcooki\Wp\Config'))
    {
        return Setcooki\Wp\Config::get($ns, $key, $default);
    }else{
        return setcooki_default($default);
    }
}


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
    if(class_exists('Setcooki\\Wp\\Cache', true))
    {
        if(Setcooki\Wp\Cache::hasInstance())
        {
            try
            {
                $class = Setcooki\Wp\Cache::instance($ns);
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
            call_user_func_array(array('Setcooki\\Wp\\Logger', 'l'), func_get_args());
            return true;
        }
    }
    if($message instanceof \Exception)
    {
        $message = $message->getMessage() . ", " . $message->getCode();
    }else if(is_array($message)){
        $message = setcooki_sprintf((string)$message[0], ((sizeof($message) > 1) ? array_slice($message, 1, sizeof($message)) : null));
    }else{
        $message = trim((string)$message);
    }
    ob_start();
    trigger_error($message);
    ob_end_clean();
    return true;
}