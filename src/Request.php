<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Routing\Route;
use Setcooki\Wp\Traits\Singleton;

/**
 * Class Request
 *
 * @package     Setcooki\Wp
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Request
{
    use Singleton;

    /**
     * global variable constant for params
     *
     * @const PARAMS
     */
    const PARAMS            = 'PARAMS';

    /**
     * global variable constant for post
     *
     * @const POST
     */
    const POST              = 'POST';

    /**
     * global variable constant for get
     *
     * @const GET
     */
    const GET               = 'GET';

    /**
     * global variable constant for cookie
     *
     * @const COOKIE
     */
    const COOKIE            = 'COOKIE';

    /**
     * global variable constant for server
     *
     * @const SERVER
     */
    const SERVER            = 'SERVER';


    /**
     * contains normalized full url of request
     *
     * @var null|string
     */
    protected $_url = null;

    /**
     * contains raw request data retrieved by php://input
     *
     * @var null|string
     */
    protected $_raw = null;

    /**
     * contains all concrete parameters found in request which can be found in
     * post, get or cookie array
     *
     * @var array
     */
    protected $_params = [];

    /**
     * contains the charset found in header
     *
     * @var null|string
     */
    protected $_charset = null;

    /**
     * contains the request content type found in header
     *
     * @var null|string
     */
    protected $_contentType = null;

    /**
     * contains the request content length if found in header
     *
     * @var null|string
     */
    protected $_contentLength = null;


    /**
     * contains a route object if request has been resolved by resolver matching a route
     *
     * @since 1.2
     * @var null|Route
     */
    protected $_route = null;



    /**
     * class constructor must be called from child class to initialize request setting
     * global rpc array and calling init function
     *
     * @param mixed $options expects optional options
     */
    public function __construct($options = null)
    {
        $this->init();
    }


    /**
     * init request if not has been initialized before trying to get charset, content type and
     * content length from headers as well as setting request url
     *
     * @return void
     * @throws \Setcooki\Wp\Exception
     */
    protected function init()
    {
        if($this->isPost())
        {
            if($this->hasPost())
            {
                $this->_params = $this->getPost();
            }else{
                $this->_params = $this->getRaw();
            }
        }
        if($this->isGet())
        {
            $this->_params = array_merge((array)$this->_params, (array)$this->getGet());
        }
        if($this->_url === null)
        {
            $this->_url = self::url();
            if(isset($_SERVER['CONTENT_TYPE']) && preg_match('/^([^\;]+)\;?\s?(?:charset\=(.+))?$/i', trim($_SERVER['CONTENT_TYPE']), $m))
            {
                $this->_contentType = strtolower(trim($m[1]));
                if(isset($m[2]))
                {
                    $this->_charset = strtolower(trim($m[2]));
                }
            }
            if(isset($_SERVER['CONTENT_LENGTH']) && !empty($_SERVER['CONTENT_LENGTH']))
            {
                $this->_contentLength = (int)$_SERVER['CONTENT_LENGTH'];
            }
        }
    }


    /**
     * get raw php post input string
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }


    /**
     * get raw php post input string
     *
     * @return string
     */
    public function getRaw()
    {
        if($this->_raw === null)
        {
            $this->_raw = trim((string)file_get_contents('php://input'));
        }
        return $this->_raw;
    }


    /**
     * get parameter from parameter array containing the parameters from post and get together
     * and merged. if parameter is not found will return default value passed in second
     * parameter
     *
     * @param string $name expects parameter name
     * @param null|mixed $default expects default return value
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getParam($name, $default = null)
    {
        return setcooki_object_get($this->_params, $name, setcooki_default($default));
    }


    /**
     * returns all merge post and get parameters from request
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }


    /**
     * checks if a parameter exist in post get merge parameter array by passing
     * parameter name in first argument. if first argument is empty will check
     * if anything is set in parameter array. if second parameter is set to true
     * will also check if parameter value is a value
     *
     * @param null|string $name expects the optional parameter name
     * @param bool $strict expects boolean value on whether to check strict or not
     * @return bool
     */
    public function hasParam($name = null, $strict = false)
    {
        if($name !== null)
        {
            return setcooki_object_isset($this->_params, $name, $strict);
        }else{
            return (!empty($this->_params)) ? true : false;
        }
    }


    /**
     * check whether any parameters/data exists in request body and returns boolean
     * value
     *
     * @return bool
     */
    public function hasParams()
    {
        return $this->hasParam();
    }


    /**
     * checks if a parameter exist in post get merged param array with strict mode
     *
     * @see \Setcooki\Wp\Request::hasParam()
     * @param null|string $name expects the optional parameter name
     * @return bool
     */
    public function isParam($name)
    {
        return $this->hasParam($name, true);
    }


    /**
     * get value from all global data array associated with request class. the second parameter
     * contains the scope string from where to look for parameter which are:
     *
     * params = the merged post and get parameters
     * post = the php post array
     * get = the php get array
     * cookie = the php cookie array
     * server = the php server array
     *
     * will return default value passed in third argument if parameter is not found
     *
     * @param string $name expects the parameter name
     * @param string $from expects
     * @param null $default expects default return value
     * @return null|mixed
     * @throws \Exception
     * @throws \Setcooki\Wp\Exception
     */
    public function getFrom($name, $from = self::PARAMS, $default = null)
    {
        $from = strtoupper(trim($from));

        switch($from)
        {
            case self::PARAMS:
                $return = setcooki_object_get($this->_params, $name, setcooki_default($default));
                break;
            case self::POST:
                $return = ((array_key_exists($name, $_POST)) ? $_POST[$name] : setcooki_default($default));
                break;
            case self::GET:
                $return = ((array_key_exists($name, $_GET)) ? $_GET[$name] : setcooki_default($default));
                break;
            case self::COOKIE:
                $return = ((array_key_exists($name, $_COOKIE)) ? $_COOKIE[$name] : setcooki_default($default));
                break;
            case self::SERVER:
                $return = ((array_key_exists($name, $_SERVER)) ? $_SERVER[$name] : setcooki_default($default));
                break;
            default:
                throw new Exception(setcooki_sprintf(__("Request variable scope: %s does not exist", SETCOOKI_WP_DOMAIN), $from));
        }
        return $return;
    }


    /**
     * get value from global variable scopes as defined in Setcooki\Wp\Request::getFrom() will
     * look in each scope / global array and if not found in any of them will return default
     * value defined in third parameter. if second parameter is not null but variable scope
     * constant name will redirect to getFrom function to get value
     *
     * @see \Setcooki\Wp\Request::getFrom()
     * @param string $name expects parameter name
     * @param string $from expects
     * @param null $default expects default return value
     * @return null|mixed
     * @throws \Exception
     * @throws \Setcooki\Wp\Exception
     */
    public function get($name, $from = null, $default = null)
    {
        $return = $default;

        if($from !== null)
        {
            return $this->getFrom($name, $from, $default);
        }

        switch(true)
        {
            case setcooki_object_isset($this->_params, $name):
                $return = setcooki_object_get($this->_params, $name, setcooki_default($default));
                break;
            case array_key_exists($name, $_POST):
                $return = $_POST[$name];
                break;
            case array_key_exists($name, $_GET):
                $return = $_GET[$name];
                break;
            case array_key_exists($name, $_COOKIE):
                $return = $_COOKIE[$name];
                break;
            case array_key_exists($name, $_SERVER):
                $return = $_SERVER[$name];
                break;
        }
        return $return;
    }


    /**
     * checks if the parameter passed in first argument exists in global arrays. if the second argument
     * is set will check only in global array with scope passed in this argument. e.g. "POST" will only
     * check in post array if parameter exists. if second parameter is not set will bubble through all
     * global variables until found in one of them or returns false if not found. the third parameter
     * activates the strict mode to check if value is a value
     *
     * @param string $name expects the parameter name
     * @param null|string $from expects optional scope value like "POST"
     * @param bool $strict expects boolean value whether to activate strict value check
     * @return bool
     */
    public function has($name, $from = null, $strict = false)
    {
        if($from === null)
        {
            $from = true;
        }
        switch($from)
        {
            case self::PARAMS:
                $return = setcooki_object_isset($this->_params, $name, $strict);
                if($return) return true;
                if($from !== true) break;
            case self::POST:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_POST) && setcooki_is_value($_POST[$name])) : array_key_exists($name, $_POST);
                if($return) return true;
                if($from !== true) break;
            case self::GET:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_GET) && setcooki_is_value($_GET[$name])) : array_key_exists($name, $_GET);
                if($return) return true;
                if($from !== true) break;
            case self::COOKIE:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_COOKIE) && setcooki_is_value($_COOKIE[$name])) : array_key_exists($name, $_COOKIE);
                if($return) return true;
                if($from !== true) break;
            case self::SERVER:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_SERVER) && setcooki_is_value($_SERVER[$name])) : array_key_exists($name, $_SERVER);
                if($return) return true;
                if($from !== true) break;
            default:
                return false;
        }
        return false;
    }


    /**
     * does the same as has method but always checking in strict mode
     *
     * @see \Setcooki\Wp\Request::has()
     * @param string $name expects the parameter name
     * @param null|string $from expects optional scope value like "POST"
     * @return bool
     */
    public function is($name, $from = null)
    {
        return $this->has($name, $from, true);
    }


    /**
     * set parameter to global scope array by passing parameter in first
     * argument, value in second and destination string as third parameter. the
     * the third value defines to which global variable to write to e.g. "POST"
     *
     * @param string $name expects the parameter name
     * @param null $value expects the value for parameter
     * @param string $to expects optional scope value like "POST"
     * @return Request
     * @throws \Exception
     */
    public function set($name, $value = null, $to = self::PARAMS)
    {
        $to = strtoupper(trim($to));

        switch($to)
        {
            case self::PARAMS:
                setcooki_object_set($this->_params, $name, $value);
                break;
            case self::POST:
                $_POST[$name] = $value;
                break;
            case self::GET:
                $_GET[$name] = $value;
                break;
            case self::COOKIE:
                $_COOKIE[$name] = $value;
                break;
            default:
                throw new Exception(setcooki_sprintf(__("Request variable scope: %s does not exist", SETCOOKI_WP_DOMAIN), $to));
        }
        return $this;
    }


    /**
     * returns whether request is a https ssl secure request or not
     *
     * @return bool
     */
    public function isHttps()
    {
        return (self::getScheme() === 'HTTPS') ? true : false;
    }


    /**
     * check whether request is post request
     *
     * @return bool
     */
    public function isPost()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') ? true : false;
    }


    /**
     * check whether request is get request
     *
     * @return bool
     */
    public function isGet()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'get') ? true : false;
    }


    /**
     * returns php post array
     *
     * @return mixed
     */
    public function getPost()
    {
        return $_POST;
    }


    /**
     * returns php get array
     *
     * @return mixed
     */
    public function getGet()
    {
        return $_GET;
    }


    /**
     * check if request is post and post variable has any value
     *
     * @return bool
     */
    public function hasPost()
    {
        return ($this->isPost() && !empty($_POST)) ? true : false;
    }


    /**
     * check if request is get and get variable has any value
     *
     * @return bool
     */
    public function hasGet()
    {
        return ($this->isGet() && !empty($_GET)) ? true : false;
    }


    /**
     * get url scheme from request
     *
     * @return string
     */
    public static function getScheme()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(isset($_SERVER['HTTPS']) && strtolower(trim($_SERVER['HTTPS'])) === 'on'){
                return 'HTTPS';
            }else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower(trim($_SERVER['HTTP_X_FORWARDED_PROTO'])) === 'https'){
                return 'HTTPS';
            }else if(isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower(trim($_SERVER['HTTP_X_FORWARDED_SSL'])) === 'on'){
                return 'HTTPS';
            }else{
                return 'HTTP';
            }
        }else{
            return 'CLI';
        }
    }


    /**
     * get host name from request. returns null if not found
     *
     * @return null|string
     */
    public static function getHost()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $host = $_SERVER['HTTP_HOST'];
            if(!empty($host))
            {
                return $host;
            }
            $host = $_SERVER['SERVER_NAME'];
            if(!empty($host))
            {
                return $host;
            }
        }
        return null;
    }


    /**
     * get server port from request. returns null if not found
     *
     * @return int|null
     */
    public static function getPort()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $port = $_SERVER['SERVER_PORT'];
            if(!empty($port))
            {
                return (int)$port;
            }
        }
        return null;
    }


    /**
     * get the server referer from request. returns null if not found
     *
     * @return null|string
     */
    public static function getReferer()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(getenv('HTTP_ORIGIN') && strcasecmp(getenv('HTTP_ORIGIN'), 'unknown'))
            {
                $ref = getenv('HTTP_ORIGIN');
            }
            else if(isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && strcasecmp($_SERVER['HTTP_ORIGIN'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_ORIGIN'];
            }
            else if(getenv('HTTP_REFERER') && strcasecmp(getenv('HTTP_REFERER'), 'unknown'))
            {
                $ref = getenv('HTTP_REFERER');
            }
            else if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strcasecmp($_SERVER['HTTP_REFERER'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_REFERER'];
            }else{
                $ref = false;
            }
            if($ref !== false && !empty($ref))
            {
                if(($host = parse_url($ref, PHP_URL_HOST)) !== false)
                {
                    return trim($host);
                }
            }
        }
        return null;
    }


    /**
     * get the server ip from request. returns null if not possible
     *
     * @return mixed|null|string
     */
    public static function getServerIp()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $ip = $_SERVER['SERVER_ADDR'];
            if(!empty($ip))
            {
                return $ip;
            }
            if(!empty($HTTP_SERVER_VARS) && !empty($HTTP_SERVER_VARS['SERVER_ADDR']))
            {
                return $HTTP_SERVER_VARS['SERVER_ADDR'];
            }
            $ip = gethostbyname($_SERVER['SERVER_NAME']);
            if((bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            {
                return $ip;
            }
            $ip = $_SERVER['LOCAL_ADDR'];
            if(!empty($ip))
            {
                return $ip;
            }
        }
        return null;
    }


    /**
     * get the client ip from request. returns null if not possible
     *
     * @return mixed|null
     */
    public static function getClientIp()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], "unknown"))
            {
               return $_SERVER['HTTP_CLIENT_IP'];
            }
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown"))
            {
               return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if(!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            {
               return $_SERVER['REMOTE_ADDR'];
            }
        }
        return null;
    }


    /**
     * get component from url as you would do with phps native function parse url expecting
     * the component flag in second argument which also cab be -1 returning all components as array.
     * if first parameter is not set will get url from current request. the url will not include
     * everything after the path variable
     *
     * @param null|string $url expects the optional url to parse
     * @param null|int $component expects optional php parse_url component flag
     * @return null|string|array
     * @throws \Exception
     */
    public static function url($url = null, $component = null)
    {
        $tmp = [];

        if($url === null)
        {
            if(strtolower(php_sapi_name()) !== 'cli')
            {
                if(isset($_SERVER['HTTPS']) && strtolower(trim($_SERVER['HTTPS'])) === 'on'){
                    $tmp[] = 'https://';
                }else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower(trim($_SERVER['HTTP_X_FORWARDED_PROTO'])) === 'https'){
                    $tmp[] = 'https://';
                }else if(isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower(trim($_SERVER['HTTP_X_FORWARDED_SSL'])) === 'on'){
                    $tmp[] = 'https://';
                }else{
                    $tmp[] = 'http://';
                }
                if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
                {
                    $tmp[] = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@';
                }
                if(!empty($_SERVER['SERVER_NAME']))
                {
                    $tmp[] = $_SERVER['SERVER_NAME'];
                }else{
                    $tmp[] = $_SERVER['HTTP_HOST'];
                }
                if(isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] !== 80)
                {
                    $tmp[] = ':' . (int)$_SERVER['SERVER_PORT'];
                }
                $tmp[] = '/' . ltrim($_SERVER['REQUEST_URI'], '/ ');
            }
            $url = implode('', $tmp);
        }

        if($component !== null)
        {
            if(($u = parse_url($url, (int)$component)) !== false)
            {
                return $u;
            }else{
                throw new Exception(setcooki_sprintf(__("Url: %s is not a valid url", SETCOOKI_WP_DOMAIN), $url));
            }
        }else{
            return $url;
        }
    }


    /**
     * get path from url or uri either by passing custom url/uri in second argument or assuming url/uri from current url
     * @see Request::url(). if you pass an integer in first argument will return the path part at this index assuming that
     * every path part is separated by /. you can pass also -1 as value which will return the last part
     *
     * @param null|int $part expects path position value
     * @param null|string $url expects optional url/uri
     * @return string
     * @throws \Setcooki\Wp\Exception
     */
    public static function path($part = null, $url = null)
    {
        if(is_null($url))
        {
            $url = self::url();
        }
        if(($url = parse_url($url, PHP_URL_PATH)) !== false)
        {
            if(!is_null($part))
            {
                $part = (int)$part;
                $url = array_filter(explode('/', trim($url, ' /')));
                if($part === -1)
                {
                    return $url[sizeof($url) - 1];
                }else{
                    return (array_key_exists($part, $url)) ? $url[$part] : '';
                }
            }else{
                return $url;
            }
        }
        return '';
    }


    /**
     * redirect to url
     *
     * @param string $url expects the url to redirect to
     * @param int $code expects the redirect code
     * @throws \Exception
     */
    public static function redirect($url, $code = 302)
   	{
   		if(filter_var($url, FILTER_VALIDATE_URL) !== false)
   		{
   			header('Location: ' . trim((string)$url), true, (int)$code);
   			if(!headers_sent())
   			{
   				die();
   			}
   		}else{
            throw new Exception(setcooki_sprintf(__("Url: %s is not a valid url", SETCOOKI_WP_DOMAIN), $url));
   		}
   	}


    /**
     * return charset if set if not returns null
     *
     * @return null|string
     */
    public function getCharset()
    {
        return $this->_charset;
    }


    /**
     * return content type if set if not returns null
     *
     * @return null|string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }


    /**
     * return content length if set if not returns null
     *
     * @return null|string
     */
    public function getContentLength()
    {
        return $this->_contentLength;
    }


    /**
     * return the accept content type in HTTP_ACCEPT header if set. returns the accepted types ordered by prio. returns
     * empty array if not accepted content types were detected
     *
     * @since 1.2
     * @return array
     */
    public function getAcceptedContentType()
    {
        $types = [];

        if(isset($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_ACCEPT']))
        {
            $type = preg_split('=\s*,\s*=i', trim($_SERVER['HTTP_ACCEPT']));
            foreach($type as $t)
            {
                if(stripos($t, ';') !== false && preg_match('=^([a-z\-\/\*]+)\;\s*(?:q\=([0-9\.]+))$=i', $t, $m))
                {
                    $types[strtolower(trim($m[1]))] = (float)$m[2];
                }else{
                    $types[strtolower($t)] = 1;
                }
            }
            arsort($types);
            $types = array_keys($types);
        }

        return $types;
    }


    /**
     * set route object if request is create from resolver matching route
     *
     * @since 1.2
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->_route = $route;
    }


    /**
     * get route object that exists if request is create from resolver matching route
     *
     * @since 1.2
     * @return null|Route
     */
    public function getRoute()
    {
        return $this->_route;
    }


    /**
     * check if request has route object = if request is create from resolver matching route
     *
     * @since 1.2
     * @return bool
     */
    public function hasRoute()
    {
        return ($this->_route) ? true : false;
    }


    /**
     * on string casting return raw input
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getRaw();
    }
}
