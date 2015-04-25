<?php

namespace Setcooki\Wp;

/**
 * Class Cache
 * @package Setcooki\Wp
 */
abstract class Cache
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
     * class constructor set options and init´s cache class
     *
     * @param null|array $options expects optional options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        $this->init();
    }


    /**
     * static instance getter/setter method to set or get cache class instances by namespace if first argument. if no
     * arguments are present will return the last set or current class cache instance
     *
     * @param null|string $ns expects the namespace identifier
     * @param null|string $driver the cache driver/class name
     * @param null|array $options optional class instance options
     * @return null|Cache
     * @throws Exception
     */
    public static function instance($ns = null, $driver = null, $options = null)
    {
        if(func_num_args() > 0)
        {
            //setting
            if($driver !== null)
            {
                self::factory($driver, $options, $ns);
            }
            //getting
            if($ns !== null)
            {
                if(array_key_exists($ns, self::$_instances))
                {
                    return self::$_instances[trim((string)$ns)];
                }else{
                    throw new Exception(setcooki_sprintf("no cache instance under ns: %s registered", $ns));
                }
            }else{
                return self::$_instance;
            }
        }else{
            //getting
            if(self::$_instance !== null)
            {
                return self::$_instance;
            }else{
                throw new Exception("can not get current cache class instance since no instance has been set yet");
            }
        }
    }


    /**
     * cache factory method creates a cache instance by driver/class name. in case multiple cache instances are needed
     * you need to specify a third argument as cache instance identifier namespace
     *
     * @param string $driver expects the driver/class name
     * @param null|array $options optional class instance options
     * @param null|string $ns expects an optional namespace identifier
     * @return mixed
     * @throws Exception
     */
    public static function factory($driver, $options = null, $ns = null)
    {
        $class = __CLASS__ . NAMESPACE_SEPARATOR . ucfirst($driver);
        if(class_exists($class, true))
        {
            if($ns !== null)
            {
                return self::$_instance = self::$_instances[trim((string)$ns)] = new $class($options);
            }else{
                return self::$_instance = new $class($options);
            }
        }else{
            throw new Exception(setcooki_sprintf("cache driver: %s does not exist", $driver));
        }
    }


    /**
     * check if any cache instance is set already
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return ((self::$_instance !== null) ? true : false);
    }


    /**
     * hash as string to make a cache key
     *
     * @param string $string expects a string as cache key basis
     * @param string $algo expects the hashing algo
     * @return string
     */
    public static function hash($string, $algo = 'sha1')
    {
        return hash(strtolower(trim((string)$algo)), trim((string)$string));
    }


    /**
     * make a cache timestamp by now + seconds passed in first argument which can be null defaulting in now only
     *
     * @param null|int $seconds expects seconds to add to time
     * @return int
     */
    protected function timestamp($seconds = null)
    {
        if($seconds !== null)
        {
            return time() + (int)$seconds;
        }else{
            return time();
        }
    }


    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    abstract public function get($key, $default = null);


    /**
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return mixed
     */
    abstract public function set($key, $value, $lifetime = null);


    /**
     * @param $key
     * @return mixed
     */
    abstract public function has($key);


    /**
     * @param $key
     * @return mixed
     */
    abstract public function forget($key);


    /**
     * @param bool $expired
     * @return mixed
     */
    abstract public function purge($expired = true);


    /**
     * prevent cloning of cache class
     *
     * @return void
     */
    protected function __clone(){}
}