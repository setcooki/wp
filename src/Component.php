<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Interfaces\Renderable;

/**
 * Class Component
 * @package Setcooki\Wp
 */
abstract class Component implements Renderable
{
    /**
     * @var null
     */
    public $wp = null;

    /**
     * @var array
     */
    public $options = array();

    /**
     * @var array
     */
    private static $_cache = array();

    /**
     * @var array
     */
    protected static $_components = array();


    /**
     * class constructor passes wp instance and sets options
     *
     * @param Wp $wp expects the wp instance
     * @param null|array $options optional options
     */
    public function __construct(Wp $wp, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->wp = $wp;
    }


    /**
     * shortcut method to create a new component instance
     *
     * @param Wp $wp expects the wp instance
     * @param null|array $options optional options
     * @return mixed
     */
    public static function create(Wp $wp, $options = null)
    {
        $class = get_called_class();
        return new $class($wp, $options);
    }


    /**
     * register a component by id
     *
     * @param string|int $id expects the id as string or int
     * @param Component $component expects the component instance
     * @return void
     */
    public static function register($id, Component $component)
    {
        self::$_components[$id] = $component;
    }


    /**
     * unregister component for id or unregister all components if no args are supplied
     *
     * @param string|int|null $id expects the id as string or int
     * @return bool
     */
    public static function unregister($id = null)
    {
        if(!is_null($id))
        {
            if(array_key_exists($id, self::$_components))
            {
                unset(self::$_components[$id]);
                return true;
            }
            return false;
        }else{
            self::$_components = array();
            self::$_cache = array();
            return true;
        }
    }


    /**
     * check if any component is registered or if passed with id in first argument if component is registered with id
     *
     * @param string|int|null $id expects the id as string or int
     * @return bool
     */
    public static function isRegistered($id = null)
    {
        if(!is_null($id))
        {
            return (!empty(self::$_components)) ? true : false;
        }else{
            return (array_key_exists($id, self::$_components)) ? true : false;
        }
    }


    /**
     * get all registered components
     *
     * @return array
     */
    public static function registered()
    {
        return self::$_components;
    }


    /**
     * execute = render components with optional passed params in second argument. components can be passed as single
     * component id or instance or array of the same. if first argument is null will render all registered components
     *
     * @param null|array|string|mixed $components
     * @param null|mixed $params expects optional params to pass to render method
     * @param string $buffer expects optional buffer reference
     * @return string
     */
    public static function execute($components = null, $params = null, &$buffer = '')
    {
        if($components === null)
        {
            $components = array_keys(self::$_components);
        }
        foreach((array)$components as $c)
        {
            if(!is_object($c))
            {
                if(array_key_exists($c, self::$_components)){
                    $c = self::$_components[$c];
                }else{
                    continue;
                }
            }

            ob_start();
            $key = md5(get_class($c) .  serialize($params));
            if(array_key_exists($key, self::$_cache))
            {
                echo self::$_cache[$key];
            }else{
                $data = $c->render($params);
                if(!is_null($data))
                {
                    if(is_object($data) && in_array('Setcooki\Wp\Interfaces\Renderable', class_implements($data)))
                    {
                        echo $data->render($params);
                    }else if(is_array($data) || is_object($data)) {
                        echo implode('', (array)$data);
                    }else{
                        echo $data;
                    }
                }
            }
            $buffer .= $data = ob_get_clean();
            self::$_cache[$key] = $data;
        }

        return $buffer;
    }


    /**
     * render the component
     *
     * @param null|mixed $params expects optional params
     * @return mixed
     */
    abstract public function render($params = null);
}