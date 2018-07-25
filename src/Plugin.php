<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

/**
 * Class Plugin
 *
 * @package     Setcooki\Wp
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
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
    protected static $_instances = [];


    /**
     * init plugin and register activation and deactivation hooks
     *
     * @param null|mixed $options expects optional class options
     * @throws \Exception
     */
    protected function __construct($options = null)
    {
        $self = $this;
        $plugin = setcooki_path('plugin');
        $plugin = $plugin . DIRECTORY_SEPARATOR . basename($plugin) . PHP_EXT;

        setcooki_init_options($options, $this);
        register_activation_hook($plugin, function() use ($self)
        {
            $self->_activate();
        });
        register_deactivation_hook($plugin, function() use ($self)
        {
            $self->_deactivate();
        });
        register_uninstall_hook($plugin, [__CLASS__, '_uninstall']);

        parent::__construct();
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
     * @throws \Exception
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
                throw new Exception(__("No plugin instance has been created yet", SETCOOKI_WP_DOMAIN));
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
     * @throws \Exception
     */
    public static function select($id)
    {
        if(self::hasInstance($id))
        {
            return self::instance($id);
        }else{
            throw new Exception(setcooki_sprintf(__("No instance found for id: %s", SETCOOKI_WP_DOMAIN), $id));
        }
    }


    /**
     * internal plugin activation hook
     *
     * @return void
     */
    protected function _activate()
    {
        if(!current_user_can('activate_plugins'))
        {
            return;
        }
        $this->activate();
    }


    /**
     * internal plugin deactivation hook
     *
     * @return void
     */
    protected function _deactivate()
    {
        if(!current_user_can('activate_plugins'))
        {
            return;
        }
        $this->deactivate();
    }


    /**
     * internal uninstall hook
     *
     * @return void
     * @throws \Exception
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
        if(self::hasInstance(get_current_blog_id()))
        {
            self::instance(get_current_blog_id())->uninstall();
        }else if(($wp = parent::wp()) !== null){
            $wp->uninstall();
        }else{
            throw new Exception(__("Unable to get plugin instance for uninstall", SETCOOKI_WP_DOMAIN));
        }
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
