<?php

namespace Setcooki\Wp\Util;

use Setcooki\Wp\Exception;

/**
 * Class Params
 *
 * @package     Setcooki\Wp\Util
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Params
{
    /**
     * class constructor set params as http query string, array or object or Params instance
     *
     * @param null|mixed|Params $params options params to init
     */
    public function __construct($params = null)
    {
        $tmp = [];

        if($params instanceof Params)
        {
            $this->set(get_object_vars($params));
        } else if(is_string($params)) {
            parse_str(trim($params), $tmp);
            $this->set($tmp);
        }else if(is_array($params) || is_object($params)){
            $this->set((array)$params);
        }
    }


    /**
     * create instance with optional params
     *
     * @param null|mixed $params options params to init
     * @return Params
     */
    public static function create($params = null)
    {
        return new self($params);
    }


    /**
     * set params either as array with key => value pairs or single with two arguments set
     *
     * @param string|array $name expects the parameter name
     * @param null|mixed $value expects the parameter value
     * @return Params
     */
    public function set($name, $value = null)
    {
        if(is_null($value) && is_array($name))
        {
            foreach($name as $k => $v)
            {
                $this->{trim((string)$k)} = $v;
            }
        }else{
            $this->{trim((string)$name)} = $value;
        }
        return $this;
    }


    /**
     * get parameter value returning default value if parameter does not exist
     *
     * @param string $name expects the parameter name
     * @param null|mixed $default expects optional default value
     * @return mixed
     * @throws \Exception
     */
    public function get($name, $default = null)
    {
        $name = trim((string)$name);

        if(stristr($name, '.') !== false)
        {
            return setcooki_object_get($this, $name, $default);
        }else if(property_exists($this, $name)) {
            return $this->$name;
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * remove a parameter
     *
     * @param string $name expects the parameter name
     * @return $this
     */
    public function remove($name)
    {
        $name = trim((string)$name);

        if($this->is($name))
        {
            unset($this->$name);
        }
        return $this;
    }


    /**
     * check if parameter exists
     *
     * @param string $name expects the parameter name
     * @return bool
     */
    public function has($name)
    {
        try
        {
            if(stristr($name, '.') !== false)
            {
                return setcooki_object_isset($this, $name, false);
            }else{
                return (property_exists($this, trim((string)$name))) ? true : false;
            }
        }
        catch(\Exception $e)
        {
            return false;
        }
    }


    /**
     * check if the parameter exists and has a value the is not php empty
     *
     * @param string $name expects the parameter name
     * @return bool
     */
    public function is($name)
    {
        try
        {
            if(stristr($name, '.') !== false)
            {
                return setcooki_object_isset($this, $name, true);
            }else{
                return (property_exists($this, trim((string)$name)) && !empty($this->{trim((string)$name)})) ? true : false;
            }
        }
        catch(\Exception $e)
        {
            return false;
        }
    }


    /**
     * magic method to set parameter key => value pair as if it where a class property
     *
     * @param string $name expects the parameter name
     * @param mixed $value expects the parameter value
     * @return Params
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
        return $this;
    }


    /**
     * magic method to get parameter value as if it where a class property
     *
     * @param string $name expects the parameter name
     * @return null
     */
    public function __get($name)
    {
        return $this->get($name);
    }


    /**
     * magic method to check if a parameter exists as if it where a class property
     *
     * @param string $name expects the parameter name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * return parameters as array
     *
     * @return array
     */
    public function __toArray()
    {
        return (array)$this;
    }


    /**
     * return parameter as simple std object
     *
     * @return object
     */
    public function __toObject()
    {
        return (object)$this;
    }


    /**
     * return parameters as http query string
     *
     * @return string
     */
    public function __toString()
    {
        return http_build_query($this->__toArray());
    }
}