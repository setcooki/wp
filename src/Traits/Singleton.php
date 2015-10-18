<?php

namespace Setcooki\Wp\Traits;

/**
 * Class Singleton
 * @package Setcooki\Wp\Interfaces
 */
trait Singleton
{
    /**
     * @var
     */
    private static $_instance;

    /**
     * @param null $options
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
}