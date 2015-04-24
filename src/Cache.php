<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

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
     * @param null $options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        $this->init();
    }


    /**
     * @param null $ns
     * @param null $driver
     * @param null $options
     * @return null
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
     * @param $driver
     * @param null $options
     * @param null $ns
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
     * @return bool
     */
    public static function hasInstance()
    {
        return ((self::$_instance !== null) ? true : false);
    }


    /**
     * @param $string
     * @param string $algo
     * @return string
     */
    public static function hash($string, $algo = 'sha1')
    {
        return hash(strtolower(trim((string)$algo)), trim((string)$string));
    }


    /**
     * @param null $seconds
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
     *
     */
    protected function __clone()
    {

    }
}