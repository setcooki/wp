<?php

namespace Setcooki\Wp\Cache;

use Setcooki\Wp\Exception;

/**
 * Class Cache
 *
 * @package     Setcooki\Wp\Cache
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
abstract class Cache
{
    /**
     * contains current instance
     *
     * @var null
     */
    protected static $_instance = null;

    /**
     * contains all instances
     *
     * @var array
     */
    protected static $_instances = [];


    /**
     * class constructor set options and init´s cache class
     *
     * @param null|array $options expects optional options
     * @throws \Exception
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
    }


    /**
     * static instance getter/setter method to set or get cache class instances by namespace if first argument. if no
     * arguments are present will return the last set or current class cache instance
     *
     * @param null|string $ns expects the namespace identifier
     * @param null|string $driver the cache driver/class name
     * @param null|array $options optional class instance options
     * @return null|Cache
     * @throws \Exception
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
                    throw new Exception(setcooki_sprintf(__("No cache instance under ns: %s registered", SETCOOKI_WP_DOMAIN), $ns));
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
                throw new Exception(__("Can not get current cache class instance since no instance has been set yet", SETCOOKI_WP_DOMAIN));
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
     * @throws \Exception
     */
    public static function factory($driver, $options = null, $ns = null)
    {
        $class = __NAMESPACE__ . NAMESPACE_SEPARATOR . ucfirst($driver);
        if(class_exists($class, true))
        {
            if($ns !== null)
            {
                return self::$_instance = self::$_instances[trim((string)$ns)] = new $class($options);
            }else{
                return self::$_instance = new $class($options);
            }
        }else{
            throw new Exception(setcooki_sprintf(__("Cache driver: %s does not exist", SETCOOKI_WP_DOMAIN), $driver));
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
     * create a hash key from argument passed to this function
     *
     * @return null|string
     */
    public static function key()
    {
        if(func_num_args() > 0)
        {
            $key = function($arg, &$tmp = []) use(&$key)
            {
                if(is_array($arg) || is_object($arg))
                {
                    foreach((array)$arg as $a)
                    {
                        $key($a, $tmp);
                    }
                }else{
                    $tmp[] = strtolower((string)$arg);
                }
                return $tmp;
            };
            return self::hash(implode('', $key(func_get_args())));
        }
        return null;
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
     * get cache item
     *
     * @param string $key expects the cache item key
     * @param null|mixed $default expects default return value
     * @return mixed
     */
    abstract public function get($key, $default = null);


    /**
     * set a cache item
     *
     * @param string $key expects the cache item key
     * @param mixed $value expects the value to cache
     * @param null|int $lifetime expects optional lifetime of cache item where omit means never expire
     * @return mixed
     */
    abstract public function set($key, $value, $lifetime = null);


    /**
     * checks if a cache item exists
     *
     * @param string $key expects the cache item key
     * @return bool
     */
    abstract public function has($key);


    /**
     * forget/remove a cache item
     *
     * @param string $key expects the cache item key
     * @return bool
     */
    abstract public function forget($key);


    /**
     * purge or clear cache store
     *
     * @param bool $expired expects boolean flag whether to remove only expired items or all
     * @return bool
     */
    abstract public function purge($expired = true);


    /**
     * prevent cloning of cache class
     *
     * @return void
     */
    protected function __clone(){}
}
