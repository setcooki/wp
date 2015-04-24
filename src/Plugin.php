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
     * @param null $options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));
    }


    /**
     * @param null $options
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
     * @param $class
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
    abstract public function activation();


    /**
     * @return mixed
     */
    abstract public function deactivation();
}