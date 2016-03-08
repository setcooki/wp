<?php

namespace Setcooki\Wp\Cache;

use Setcooki\Wp\Exception;

/**
 * Class Apc
 * @package Setcooki\Wp\Cache
 */
class Apc extends Cache
{
    /**
     * prefix option defines a cache item key prefix value
     */
    const KEY_PREFIX                = 'KEY_PREFIX';

    /**
     * expiration option defines the expiration value of cache file
     */
    const EXPIRATION                = 'EXPIRATION';


    /**
     * @var array
     */
    public $options = array
    (
        self::KEY_PREFIX            => 0,
        self::EXPIRATION            => 60
    );


    /**
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->init();
    }


    /**
     * @param null $options
     * @return null|File
     */
    public static function create($options = null)
    {
        return new self($options);
    }


    /**
     * @throws Exception
     */
    protected function init()
    {
        if(!extension_loaded('apc'))
        {
            throw new Exception("apc extension is not supported by this system");
        }
    }


    /**
     * @param $key
     * @param null $default
     * @return mixed
     * @throws \Exception
     */
    public function get($key, $default = null)
    {
        if(($value = apc_fetch(setcooki_get_option(self::KEY_PREFIX, $this) . $key)) !== false)
        {
            return $value;
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return bool
     */
    public function set($key, $value, $lifetime = null)
    {
        if($lifetime === null)
        {
            $lifetime = setcooki_get_option(self::EXPIRATION, $this);
        }
        return (bool)apc_store(setcooki_get_option(self::KEY_PREFIX, $this) . $key, $value, $lifetime);
    }


    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return (bool)apc_exists(setcooki_get_option(self::KEY_PREFIX, $this) . $key);
    }


    /**
     * @param $key
     * @return bool
     */
    public function forget($key)
    {
        return (bool)apc_delete(setcooki_get_option(self::KEY_PREFIX, $this) . $key);
    }


    /**
     * @param bool $expired
     * @return bool
     */
    public function purge($expired = true)
    {
        return apc_clear_cache();
    }
}