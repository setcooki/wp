<?php

namespace Setcooki\Wp;

/**
 * Class Plugin
 * @package Setcooki\Wp
 */
abstract class Plugin
{
    /**
     * @var null
     */
    protected static $_instance = null;


    /**
     * init plugin and register activation and deactivation hooks
     *
     * @param null⁄mixed $options expects optional class options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array($this, 'uninstall'));
    }


    /**
     * static class instance setter/getter
     *
     * @param null⁄mixed $options expects optional class options
     * @return null|Plugin
     */
    public static function instance($options = null)
    {
        $class = get_called_class();

        if(self::$_instance === null)
        {
            self::$_instance = new $class($options);
        }
        return self::$_instance;
    }


    /**
     * build in autoloader will only load classes of actual plugin implementation that will extend the plugin skeleton
     *
     * @param string $class expects the class name to load
     * @return void
     */
    public static function autoload($class)
    {
        $ns = substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\'));
        $src = rtrim(realpath(dirname(__FILE__) . '/../../'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if(stripos(trim($class, ' \\/'), $ns) !== false)
        {
            require_once $src . str_replace(array('\\'), DIRECTORY_SEPARATOR, $class) . '.php';
        }
    }


    /**
     * @return mixed
     */
    abstract public function init();


    /**
     * @return mixed
     */
    abstract public function activate();


    /**
     * @return mixed
     */
    abstract public function deactivate();


    /**
     * @return mixed
     */
    abstract public function uninstall();
}