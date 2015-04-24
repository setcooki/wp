<?php

namespace Setcooki\Wp;

/**
 * Class Option
 * @package Setcooki\Wp
 */
class Option
{
    /**
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        self::save($name, $value);
    }


    /**
     * @param $name
     * @param $value
     * @param null $path
     * @return bool|mixed|void
     */
    public static function save($name, $value, $path = null)
    {
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
     * @param $name
     * @param bool $default
     * @return mixed|void
     */
    public static function get($name, $default = false)
    {
        if(stripos($name, '*') !== false)
        {
            $tmp = array();
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
            return get_option($name, $default);
        }
    }


    /**
     * @param $name
     * @param $path
     * @param bool $default
     * @return array|bool|mixed
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
     * @param $name
     * @param $value
     * @return mixed|void
     */
    public static function set($name, $value)
    {
        return self::save($name, $value);
    }


    /**
     * @param $name
     * @param $value
     * @param $path
     * @return bool|mixed|void
     */
    public static function setByPath($name, $value, $path)
    {
        return self::save($name, $value, $path);
    }


    /**
     * @param $name
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
            return delete_option($name);
        }
    }


    /**
     * @param $name
     * @param null $path
     * @return array|bool|mixed|void
     */
    public static function has($name, $path = null)
    {
        if(($value = get_option($name, false)) !== false)
        {
            if(is_array($value) && $path !== null)
            {
                return (setcooki_object_isset($value, trim((string)$path), false)) ? $value : false;
            }
            return $value;
        }
        return false;
    }


    /**
     * @param $name
     * @param null $path
     * @return array|bool|mixed|void
     */
    public static function is($name, $path = null)
    {
        if(($value = get_option($name, false)) !== false)
        {

            if(is_array($value) && $path !== null)
            {
                return (setcooki_object_isset($value, trim((string)$path), true)) ? $value : false;
            }
            return (!empty($value)) ? $value : false;
        }
        return false;
    }


    /**
     * @param $name
     * @param $path
     * @return bool|mixed|void
     */
    public static function unsetByPath($name, $path)
    {
        if(($value = self::has($name)) !== false)
        {
            if(is_array($value))
            {
                setcooki_object_unset($value, trim((string)$path));
                return update_option($name, $value);
            }else{
                return self::reset($name);
            }
        }
    }


    /**
     * @param $name
     * @return mixed|void
     */
    public static function reset($name)
    {
        return self::set($name, '');
    }
}