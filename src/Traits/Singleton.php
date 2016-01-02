<?php

namespace Setcooki\Wp\Traits;

/**
 * Class Singleton
 * @package Setcooki\Wp\Traits
 */
trait Singleton
{
    /**
     * @var
     */
    private static $_instance = null;


    /**
     * create static single instance with class options
     *
     * @param null|mixed $options expects optional options
     * @return Singleton
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * check if singleton has been instantiated
     *
     * @return bool
     */
    public static function instantiated()
    {
        return (self::$_instance !== null) ? true : false;
    }
}