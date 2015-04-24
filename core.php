<?php

require_once dirname(__FILE__) . '/wp.php';
require_once dirname(__FILE__) . '/helper.php';
require_once dirname(__FILE__) . '/../Plugin.php';
require_once dirname(__FILE__) . '/../Config.php';

if(defined('SETCOOKI_WP_AUTOLOAD') && (bool)constant('SETCOOKI_WP_AUTOLOAD'))
{
    @spl_autoload_register(array('\Setcooki\Wp\Plugin', 'autoload'), false);
}
if(!defined('SETCOOKI_NS'))
{
    define('SETCOOKI_NS', 'SETCOOKI_WP');
}
define('SETCOOKI_WP_CONFIG', 'CONFIG');
define('SETCOOKI_WP_CHARSET', 'CHARSET');
define('SETCOOKI_WP_ERROR_HANDLER', 'ERROR_HANDLER');
define('SETCOOKI_WP_EXCEPTION_HANDLER', 'EXCEPTION_HANDLER');

//global var init
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
 * @param null $conf
 * @return array
 */
function setcooki_init($conf = null)
{
    $default = array
    (
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
 * @param $key
 * @param string $value
 * @param null $default
 * @return mixed|string
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
 * @param $class
 */
function setcooki_import($class)
{
    Setcooki\Wp\Plugin::autoload($class);
}

/**
 * @param $type
 * @param bool $relative
 * @param bool $url
 * @return string
 */
function setcooki_path($type, $relative = false, $url =false)
{
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
            $path = get_theme_root();
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
 * @param $ns
 * @param null $key
 * @param null $default
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
 * @param $key
 * @param string $value
 * @param null $lifetime
 * @param null $ns
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
 * @param $message
 * @param int $type
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