<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Exception;

/**
 * Trait Setget
 *
 * @since       1.1.3
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Setget
{
	/**
	 * setter method which can set properties by name => value pairs or by array with key => value pairs. with third
	 * argument which defaults to boolean true only properties will be set (assigned value) that already exists - no
	 * overloading allowed. in case overloading use add() method
	 *
	 * @param string|array $name expects property name or array of name => value pairs
	 * @param string|mixed $value expects value to set
	 * @param bool $strict expects boolean flag for enable/disable property overloading
	 * @return $this
	 */
	public function set($name, $value = '__NULL__', $strict = true)
	{
		if((is_array($name) || is_object($name)) && $value === '__NULL__')
		{
			foreach((array)$name as $key => $val)
			{
				$this->set($key, $val);
			}
		}else{

			if((bool)$strict)
			{
				if(array_key_exists((string)$name, get_object_vars($this))) $this->{(string)$name} = $value;
			}else{
				$this->{(string)$name} = $value;
			}
		}
		return $this;
	}


	/**
	 * setter method with overloading capacities - see Setget::set()
	 *
	 * @see Setget::set()
	 * @param string|array $name expects property name or array of name => value pairs
	 * @param string|mixed $value expects value to set
	 * @return $this
	 */
	public function add($name, $value)
	{
		return $this->set($name, $value, false);
	}


    /**
     * getter method
     *
     * @param string $name expects name of property
     * @param null|mixed $default expects default return value
     * @return mixed
     * @throws \Exception
     */
	public function get($name, $default = null)
	{
		if($this->has($name))
		{
			return $this->{(string)$name};
		}else{
			return setcooki_default($default);
		}
	}


	/**
	 * checks if a property exists
	 *
	 * @param string $name expects name of property
	 * @return bool
	 */
	public function has($name)
	{
		return (array_key_exists((string)$name, get_object_vars($this))) ? true : false;
	}


	/**
	 * checks if a property exists and the value is a value that is not null and not empty
	 *
	 * @param string $name expects name of property
	 * @return bool
	 */
	public function is($name)
	{
		return (array_key_exists((string)$name, get_object_vars($this)) && setcooki_is_value($this->{(string)$name})) ? true : false;
	}


	/**
	 * removes a property
	 *
	 * @param string $name expects name of property
	 * @return $this
	 */
	public function remove($name)
	{
		if($this->has($name))
		{
			unset($this->{(string)$name});
		}
		return $this;
	}


	/**
	 * adds a property by overloading
	 *
	 * @see Setget::add()
	 * @param string $name expects property name
	 * @param mixed $value expects value to set
	 * @return $this
	 */
	public function __set($name, $value)
	{
		return $this->add($name, $value);
	}


	/**
	 * gets a property by overloading
	 *
	 * @see Setget::get()
	 * @param string $name expects property name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}


	/**
	 * checks if a property exists by overloading
	 *
	 * @see Setget::has()
	 * @param string $name expects property name
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->has($name);
	}


	/**
	 * removes a property by overloading
	 *
	 * @see Setget::remove()
	 * @param string $name expects property name
	 * @return $this
	 */
	public function __unset($name)
	{
		return $this->remove($name);
	}


	/**
	 * overload to string = serializes sring
	 *
	 * @return string
	 */
	public function __toString()
	{
		return serialize(get_object_vars($this));
	}


	/**
	 * overload to array = object to array conversion
	 *
	 * @return array
	 */
	public function __toArray()
	{
		return (array)get_object_vars($this);
	}


	/**
	 * overload to json = object to json encode
	 *
	 * @return string
	 */
	public function __toJson()
	{
		return json_encode(get_object_vars($this));
	}
}