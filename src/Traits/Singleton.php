<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Exception;

/**
 * Trait Singleton
 *
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Singleton
{
    /**
     * @var
     */
    protected static $_instance = null;


    /**
     * create static single instance with class options
     *
     * @param null|mixed $options expects optional options
     * @return Singleton
     */
    public static function instance($options = null)
    {
        if(static::$_instance === null)
        {
            static::$_instance = new static($options);
        }
        return static::$_instance;
    }


    /**
     * check if singleton has been instantiated
     *
     * @return bool
     */
    public static function instantiated()
    {
        return (static::$_instance !== null) ? true : false;
    }
}
