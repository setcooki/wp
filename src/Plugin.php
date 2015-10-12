<?php

namespace Setcooki\Wp;

/**
 * Class Plugin
 * @package Setcooki\Wp
 */
abstract class Plugin extends Wp
{
    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected static $_instances = array();


    /**
     * init plugin and register activation and deactivation hooks
     *
     * @param null⁄mixed $options expects optional class options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        register_activation_hook(__FILE__, array(__CLASS__, '_activate'));
        register_deactivation_hook(__FILE__, array(__CLASS__, '_deactivate'));
        register_uninstall_hook(__FILE__, array(__CLASS__, '_uninstall'));
    }


    /**
     * static class instance setter/getter. the multi instance way of creating instances of plugin is wp multi-site compatible
     * therefore a instance id must be passed in first argument when creating the plugin instance. for getting instance
     * it is enough to either omit first argument, for returning current active instance, or pass the instance id as argument
     * value
     *
     * @param null|mixed $id expects the optional plugin id
     * @param null|mixed $options expects optional class options
     * @return null|Plugin
     * @throws Exception
     */
    public static function instance($id = null, $options = null)
    {
        $class = get_called_class();

        if($id !== null)
        {
            if(!array_key_exists($id, self::$_instances))
            {
                self::$_instances[$id] = self::$_instance = new $class($options);
            }
            return self::$_instances[$id];
        }else{
            if(self::hasInstance())
            {
                return self::$_instance;
            }else{
                throw new Exception('no plugin instance has been created yet');
            }
        }
    }


    /**
     * check if plugin has been initiated via static singleton instance() method either by passing instance id in first
     * argument or no argument which will test if any current instance is selected
     *
     * @param null|mixed $id expects the optional instance id
     * @return bool
     */
    public static function hasInstance($id = null)
    {
        if($id !== null)
        {
            return (array_key_exists($id, self::$_instances)) ? true : false;
        }else{
            return (!is_null(self::$_instance)) ? true : false;
        }
    }


    /**
     * safe way to switch between instances because any instance that will be selected but has not been instantiated yet
     * will throw an exception
     *
     * @param mixed $id expects the instance id
     * @return null|Plugin
     * @throws Exception
     */
    public static function select($id)
    {
        if(self::hasInstance($id))
        {
            return self::instance($id);
        }else{
            throw new Exception(setcooki_sprintf('no instance found for id: %s', $id));
        }
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
     * internal plugin activation hook
     *
     * @return void
     * @throws Exception
     */
    protected static function _activate()
    {
        if(!current_user_can('activate_plugins'))
        {
            return;
        }
        self::instance()->activate();
    }


    /**
     * internal plugin deactivation hook
     *
     * @return void
     * @throws Exception
     */
    protected static function _deactivate()
    {
        if(!current_user_can('activate_plugins'))
        {
            return;
        }
        self::instance()->deactivate();
    }


    /**
     * internal uninstall hook
     *
     * @return void
     * @throws Exception
     */
    protected static function _uninstall()
    {
        if(!current_user_can('activate_plugins'))
        {
            return;
        }
        if(!defined('WP_UNINSTALL_PLUGIN') &&  __FILE__ !== WP_UNINSTALL_PLUGIN)
        {
            return;
        }
        self::instance()->uninstall();
    }


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