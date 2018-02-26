<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Exception;

/**
 * Trait Data
 *
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Data
{
	/**
	 * data container
	 *
	 * @var null
	 */
	public $data = null;


	/**
	 * data getter and setter
	 *
	 * @param null|mixed $data expects data
	 * @return null|mixed
	 */
	public function data($data = null)
	{
		if($data !== null)
		{
	        $this->data = $data;
		}
		return $this->data;
	}


	/**
	 * set data with or without key. if key is set the data container will become an array object which then is extended
	 * by key which can be a key name as string or a key name with dot notation resulting in creating the value at respective
	 * path since each dot is a array key resulting in a multidimensional array. if the key or first argument is empty the
	 * data container value is overwritten with value in second argument
	 *
	 * @param null|string $key expects key/path where to set the value or no value
	 * @param mixed $value expects value to set
	 * @return $this
	 */
	public function set($key = null, $value)
	{
		if(!empty($key))
		{
			setcooki_object_set($this->data, $key, $value);
		}else{
			$this->data = $value;
		}
		return $this;
	}


	/**
	 * see Data::set() for basic behaviour. the difference is that the value in second argument will be added to the value
	 * found at key/path whichin case of array found at key/path will do an array merge and in case of a string will do
	 * a string concat
	 *
	 * @see Data::set()
	 * @param null|string $key expects key/path where to set the value or no value
	 * @param mixed $value expects value to add
	 * @return $this
	 */
	public function add($key = null, $value)
	{
		if(!empty($key))
		{
			if(($data = $this->get($key)) !== null)
			{
				if(is_array($data))
				{
					$value = array_merge($data, (array)$value);
				}else{
					$value = (string)$data . (string)$value;
				}
			}
			$this->set($key, $value);
		}else{
			if(is_array($this->data))
			{
				$this->data = array_merge($this->data, (array)$value);
			}else{
				$this->data = (string)$this->data . (string)$value;
			}
		}
		return $this;
	}


    /**
     * get data container value or get data values at key/path passing a default value in second argument in case key/path
     * can not be resolved
     *
     * @param null|string $key expects key/path where to set the value or no value
     * @param null|mixed $default expects default return value
     * @return mixed|null
     * @throws \Exception
     */
	public function get($key = null, $default = null)
	{
		if(!empty($key))
		{
			return setcooki_object_get($this->data, $key, setcooki_default($default));
		}else{
			return $this->data();
		}
	}


	/**
	 * check if data container is not null or if key/path is set in first argument if anything is set at the respective
	 * key/path
	 *
	 * @param null|string $key expects key/path where to set the value or no value
	 * @return bool
	 */
	public function has($key = null)
	{
		if(!empty($key))
		{
			return setcooki_object_isset($this->data, $key);
		}else{
			return (!empty($this->data)) ? true : false;
		}
	}


	/**
	 * check if data container is not empty and a valid value or if key/path is set in first argument if a valid value is
	 * found at the respective key/path
	 *
	 * @param null|string $key expects key/path where to set the value or no value
	 * @return bool
	 */
	public function is($key = null)
	{
		if(!empty($key))
		{
			return setcooki_object_isset($this->data, $key, true);
		}else{
			return (setcooki_is_value($this->data)) ? true : false;
		}
	}


	/**
	 * remove or reset the data container. if the first argument key/path is not set will reset the data container. if the
	 * key/path value is not empty will remove = unset the key and value found at key/path
	 *
	 * @param null|string $key expects key/path where to set the value or no value
	 * @return $this
	 */
	public function remove($key = null)
	{
		if(!empty($key))
		{
			setcooki_object_unset($this->data, $key);
		}else{
			$this->data = null;
		}
		return $this;
	}


	/**
	 * to string conversions returns a serialized string of data container
	 *
	 * @return string
	 */
	public function __toString()
	{
		return serialize($this->data);
	}


	/**
	 * to json conversion returns a json encoded string of data container
	 *
	 * @return mixed|string
	 */
	public function __toJson()
	{
		return json_encode($this->data);
	}
}