<?php

namespace Setcooki\Wp;

/**
 * Class Wp
 * @package Setcooki\Wp
 */
abstract class Wp
{
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
     * class constructor initializes base wp class
     */
    protected function __construct()
    {
        $this->root();
        $this->base();
    }


    /**
     * concrete class must implement init function
     *
     * @return mixed
     */
    abstract public function init();


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