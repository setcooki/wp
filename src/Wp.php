<?php

namespace Setcooki\Wp;

/**
 * Class Wp
 * @package Setcooki\Wp
 */
abstract class Wp
{
    /**
     * contains all plugins/themes create with this framework
     *
     * @var array
     */
    private static $_me = array();

    /**
     * contains all unique objects stored with this instance of the framework
     *
     * @var array
     */
    private $_store = array();

    /**
     * contains the themes/plugins base path
     *
     * @var null|string
     */
    public $base = null;

    /**
     * contains the themes/plugins root path
     *
     * @var null|string
     */
    public $root = null;

    /**
     * contains the name of the theme/(plugin
     *
     * @var null|string
     */
    public $name = null;

    /**
     * contains the scope of wp instance which can be a theme or plugin
     *
     * @var null|string
     */
    public $scope = null;


    /**
     * class constructor initializes base wp class
     */
    protected function __construct()
    {
        $this->root();
        $this->base();
        $this->scope();
        $this->name();

        if(!empty($this->scope) && !empty($this->name))
        {
            self::$_me["$this->scope:$this->name"] = $this;
        }
    }


    /**
     * concrete class must implement init function
     *
     * @return mixed
     */
    abstract public function init();


    /**
     * each framework instance can has a unique storage capacity by using this setter/getter method. all important objects
     * that need to exist only once per instance can be stored here in a key => value manner. e.g. there should be only
     * on router instance per framework instance and that instance is stored automatically under the "router" key here
     *
     * @param null|string $name expects the object name in setter/getter mode
     * @param null|mixed $value expects the value to set in setter mode
     * @param null|mixed $default expects the default return value in getter mode
     * @return $this|array|mixed
     */
    public function store($name = null, $value = null, $default = null)
    {
        if(!is_null($name))
        {
            $name = trim((string)$name);
            if(!is_null($value))
            {
                if($value === false)
                {
                    unset($this->_store[$name]);
                }else{
                    $this->_store[$name] = $value;
                }
                return $this;
            }else{
                if(array_key_exists($name, $this->_store))
                {
                    return $this->_store[$name];
                }else{
                    return setcooki_default($default);
                }
            }
        }else{
            return $this->_store;
        }
    }


    /**
     * checks if the the framework instance has a unique object stored under name passed in first argument
     *
     * @param null|string $name expects the object name
     * @return bool
     */
    public function stored($name)
    {
        return (array_key_exists(trim((string)$name), $this->_store)) ? true : false;
    }


    /**
     * get/set base path of either plugin or theme
     *
     * @return null|string
     */
    public function base()
    {
        $base = null;

        if(is_null($this->base))
        {
            foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $bt)
            {
                if($this->isTheme() && preg_match('=(.*)functions.php$=i', $bt['file'], $m))
                {
                    $base = DIRECTORY_SEPARATOR . trim($m[1], ' ' . DIRECTORY_SEPARATOR);
                    break;
                }else if($this->isPlugin() && $bt['class'] === get_called_class()){
                    $dirs = explode(DIRECTORY_SEPARATOR, trim($bt['file'], ' ' . DIRECTORY_SEPARATOR));
                    for($i = sizeof($dirs) - 1; $i >= 0; $i--)
                    {
                        if(trim($dirs[$i]) === 'plugins')
                        {
                            $base = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_slice($dirs, 0, $i + 2));
                            break;
                        }
                    }
                    break;
                }
            }
            $this->base = $base;
        }
        return $this->base;
    }


    /**
     * static method to get base path of plugin/theme from anywhere. will only work if wp base class or extended
     * classes have been initialized prior to calling this functions or callee really resides in a wordpress theme/plugin.
     * NOTE: this function is experimental!
     *
     * @experimental
     * @param string $path expects optional path addition
     * @return string
     */
    public static function b($path = '')
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach((array)$debug as $d)
        {
            if(array_key_exists('object', $d) && is_subclass_of($d['object'], 'Setcooki\Wp\Wp') && property_exists($d['object'], 'base'))
            {
                return (!empty($path)) ? rtrim($d['object']->base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, ' ' . DIRECTORY_SEPARATOR) : rtrim($d['object']->base, DIRECTORY_SEPARATOR);
            }
        }
        if(empty($path))
        {
            foreach((array)$debug as $d)
            {
                if(stripos($d['file'], '/themes/') !== false || stripos($d['file'], '/plugins/') !== false)
                {
                    return rtrim(preg_replace('@(.*)((\/themes|\/plugins)\/([^\/]{1,}))(.*)$@i', '$1$2', $d['file']), ' ' . DIRECTORY_SEPARATOR);
                }
            }
        }
        return $path;
    }


    /**
     * get/set root path of wordpress installation
     *
     * @return null|string
     */
    public function root()
    {
        if(is_null($this->root))
        {
            $this->root = setcooki_path('root');
        }
        return $this->root;
    }


    /**
     * get/set the name of this wp theme/plugin instance which is defined by theme/plugin folder name
     *
     * @return null|string
     */
    public function name()
    {
        if(is_null($this->name))
        {
            $this->name = basename($this->base());
        }
        return $this->name;
    }


    /**
     * get/set the scope which can be either a theme or a plugin from current instance
     *
     * @return null|string
     */
    public function scope()
    {
        if(is_null($this->scope))
        {
            $this->scope = ($this->isPlugin()) ? 'plugin' : 'theme';
        }
        return $this->scope;
    }


    /**
     * return boolean value if the concrete class derived from wp base class is a theme
     *
     * @return bool
     */
    public function isTheme()
    {
        return (stripos(get_class($this), 'theme') !== false || is_subclass_of($this, 'Setcooki\Wp\Theme', false)) ? true : false;
    }


    /**
     * return boolean value if the concrete class derived from wp base class is a plugin
     *
     * @return bool
     */
    public function isPlugin()
    {
        return (stripos(get_class($this), 'plugin') !== false || is_subclass_of($this, 'Setcooki\Wp\Plugin', false)) ? true : false;
    }


    /**
     * check if a plugin or theme by scope + name has been initialized and return the instance. the id passed in first arg
     * must have the following syntax "{$scope}:{$name}" like "theme:foo". if id is not passed will try to determine the
     * id by extracting it from base path. will return the instance or return default value specified in second argument
     *
     * @param string|null $id expects the id like {$scope}:{$name}
     * @param null|mixed $default expects default return value
     * @return mixed
     */
    public static function me($id = null, $default = null)
    {
        if(is_null($id))
        {
            $path = self::b();
            if(!empty($path))
            {
                $path = array_values(array_filter(explode(DIRECTORY_SEPARATOR, $path)));
                if(sizeof($path) >= 2)
                {
                    $id = trim(strtolower(substr($path[sizeof($path)-2], 0, -1)) . ':' . trim($path[sizeof($path)-1]), ' ' . DIRECTORY_SEPARATOR);
                }
            }
        }
        $id = trim((string)$id);
        if(array_key_exists($id, self::$_me))
        {
            return self::$_me[$id];
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * build in autoloader will load setcooki/wp from vendor
     *
     * @param string $class expects the class name to load
     * @return false
     */
    public static function autoload($class)
    {
        $ext = '.php';
        $src = rtrim(realpath(dirname(__FILE__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $class = trim((string)$class, ' \\');

        //setcooki/wp ns
        if(stripos(trim($class, ' \\/'), substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\'))) !== false)
        {
            $file = trim(str_ireplace(__NAMESPACE__, '', $class), ' \\');
            $file = str_replace(array('\\'), DIRECTORY_SEPARATOR, $file);
            require_once $src . $file . $ext;
        //others dirs/ns set with global options
        }else if(setcooki_conf(SETCOOKI_WP_AUTOLOAD_DIRS)){
            foreach((array)setcooki_conf(SETCOOKI_WP_AUTOLOAD_DIRS) as $dir)
            {
                if(is_array($dir))
                {
                    if(array_key_exists(1, $dir))
                    {
                        $class = trim(str_ireplace(trim($dir[1], ' \//'), '', $class), ' \\');
                    }
                    $dir = (array_key_exists(0, $dir)) ? $dir[0] : '';
                }
                $class = str_replace(array('\\'), DIRECTORY_SEPARATOR, $class);
                $file = DIRECTORY_SEPARATOR . trim((string)$dir, ' \\/') . DIRECTORY_SEPARATOR . $class . $ext;
                if(file_exists($file))
                {
                    require_once $file;
                }
            }
        }
        return false;
    }
}