<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

/**
 * Class Option
 *
 * @package     Setcooki\Wp
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Option
{
    /**
     * class constructor saves an option
     *
     * @param string $name expects a option name
     * @param mixed $value expects the option value
     */
    public function __construct($name, $value)
    {
        self::save($name, $value);
    }


    /**
     * save option to wp option table expecting name and value in first and second argument. the third argument can be
     * a array path since this class allows for setting values in multidimensional arrays using "." dot syntax. the third
     * argument exists only because of legacy reason since the first argument option name can be a path already
     *
     * @param string $name expects a option name
     * @param mixed $value expects the option value
     * @param null|string $path expects a optional set path
     * @return bool|mixed
     */
    public static function save($name, $value, $path = null)
    {
        if(stripos($name, '.') !== false && $path !== null)
        {
            $path = substr($name, stripos($name, '.') + 1);
            $name = substr($name, 0, stripos($name, '.'));
        }
        if(($option = self::has($name)) !== false)
        {
            if(is_array($option) && is_array($value))
            {
                if($path !== null)
                {
                    setcooki_object_set($option, trim((string)$path), $value);
                    $value = $option;
                }else{
                    $value = array_merge($option, $value);
                }
            }else if(is_array($option) && !is_array($value)){
                if($path !== null)
                {
                    setcooki_object_set($option, trim((string)$path), $value);
                    $value = $option;
                }else{
                    $value = ($option[] = $value);
                }
            }
            if(update_option($name, $value))
            {
                return self::get($name);
            }
        }else{
            if(add_option($name, $value))
            {
                return self::get($name);
            }
        }
        return false;
    }


    /**
     * get an option by name returning default value passed in second argument if option can not be found. the name can
     * end with a "*" wildcard character returning all options that match the name regex like. the option name also allows
     * for "." path naming getting values from multidimensional objects/arrays
     *
     * @param string $name expects the option name
     * @param bool|mixed $default expects the optional return default value
     * @return bool|mixed
     */
    public static function get($name, $default = false)
    {
        if(stripos($name, '*') !== false)
        {
            $tmp = [];
            $options = wp_load_alloptions();
            foreach($options as $k => $v)
            {
                if(preg_match("/^$name.*/i", $v))
                {
                    $tmp[$k] = $v;
                }
            }
            return (sizeof($tmp) > 0) ? $tmp : $default;
        }else{
            if(stripos($name, '.') !== false)
            {
                return self::getByPath
                (
                    substr($name, 0, stripos($name, '.')),
                    substr($name, stripos($name, '.') + 1),
                    $default
                );
            }else{
                return get_option($name, $default);
            }
        }
    }


    /**
     * get an option by path - see Setcooki\Wp\Option::get()
     *
     * @see \Setcooki\Wp\Option::get()
     * @param string $name expects the option name
     * @param string $path expects "." dot syntax path
     * @param bool|mixed $default expects the optional return default value
     * @return bool|mixed
     */
    public static function getByPath($name, $path, $default = false)
    {
        if(($value = self::has($name)) !== false)
        {
            if(is_array($value))
            {
                return setcooki_object_get($value, trim((string)$path), $default);
            }else{
                return $value;
            }
        }
        return $default;
    }


    /**
     * set a value in second argument to option name set in first argument
     *
     * @param string $name expects the option name
     * @param mixed $value expects the value to set
     * @return bool|mixed
     */
    public static function set($name, $value)
    {
        return self::save($name, $value);
    }


    /**
     * set a value in second argument to option name set in first argument. you must supply a "." dot syntaxed path in
     * third argument because this method is ment to set values in multidimensional objects/array
     *
     * @param string $name expects the option name
     * @param mixed $value expects the value to set
     * @param string $path expects the path
     * @return bool|mixed
     */
    public static function setByPath($name, $value, $path)
    {
        return self::save($name, $value, $path);
    }


    /**
     * delete a option by name or delete multiple options with wildcard syntax which can be a part of option name ending
     * with "*" character. this function will also delete values with "." dot path syntax
     *
     * @param string $name expects the option name
     * @return bool
     */
    public static function delete($name)
    {
        if(stripos($name, '*') !== false)
        {
            $options = wp_load_alloptions();
            foreach($options as $option)
            {
                if(preg_match("/^$name.*/i", $option))
                {
                    delete_option($option);
                }
            }
            return true;
        }else{
            if(stripos($name, '.') !== false)
            {
                if(($options = self::get(substr($name, 0, stripos($name, '.')))) !== false)
                {
                    setcooki_object_unset($options, substr($name, stripos($name, '.') + 1));
                    return true;
                }else{
                    return false;
                }
            }else{
                return delete_option($name);
            }
        }
    }


    /**
     * check if a option exists by name which can be a "." syntax path checking deep in multidimensional array/object. the
     * second argument defines whether to check if value is a valid value (empty|null|false|'' are not valid values) or not
     *
     * @param string $name expects the option name
     * @param bool $strict expects boolean flag for if to check value for valid value
     * @return bool|mixed
     */
    public static function has($name, $strict = false)
    {
        if(($value = self::get($name, '_NIL_')) !== '_NIL_')
        {
            if((bool)$strict)
            {
                return (setcooki_is_value($value)) ? $value : false;
            }else{
                return $value;
            }
        }
        return false;
    }


    /**
     * shortcut function for Setcooki\Wp\Option::has() in strict mode true
     *
     * @param string $name expects the option name
     * @return bool|mixed
     */
    public static function is($name)
    {
        return self::has($name, true);
    }


    /**
     * reset a option to default value which is a empty string using Setcooki\Wp\Option::set() function
     *
     * @see \Setcooki\Wp\Option::set()
     * @param string $name expects the option name
     * @return bool|mixed
     */
    public static function reset($name)
    {
        return self::set($name, '');
    }
}