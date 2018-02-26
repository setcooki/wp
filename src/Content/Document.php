<?php

namespace Setcooki\Wp\Content;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Traits\Wp;

/**
 * Class Document
 *
 * @since       1.2
 * @package     Setcooki\Wp\Content
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
abstract class Document
{
    use Wp;


    /**
     * contains all document key => value pairs
     *
     * @var array
     */
    public $document = [];


    /**
     * class constructor sets options
     *
     * @param null|mixed $options expects optional class options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        setcooki_init_options($options, $this);
    }


    /**
     * set a singular value in document key => value store
     *
     * @param string $key expects the key
     * @param string|array $value expects the value
     * @return $this
     */
    public function set($key, $value)
    {
        if(is_array($value) && empty($value))
        {
            $value = null;
        }
        $this->document[$key] = $value;
        return $this;
    }


    /**
     * add to a document value by key supposing key is an array and value is anything but array
     *
     * @param string $key expects the key
     * @param string $value expects the value
     * @return $this
     */
    public function add($key, $value)
    {
        if(!array_key_exists($key, $this->document))
        {
            $this->document[$key] = [];
        }
        $this->document[$key][] = $value;
        return $this;
    }


    /**
     * get the value previously stored with key
     *
     * @param string $key expects the key
     * @param null|mixed $default expects default return value
     * @return mixed|null
     * @throws \Exception
     */
    public function get($key, $default = null)
    {
        if($this->has($key))
        {
            return $this->document[$key];
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * checks if a key in document store exists
     *
     * @param string $key expects the key
     * @return bool
     */
    public function has($key)
    {
        return (array_key_exists($key, $this->document));
    }


    /**
      * checks if a key in document store exists and has a value !== null
      *
      * @param string $key expects the key
      * @return bool
      */
    public function is($key)
    {
        return (array_key_exists($key, $this->document) && $this->document[$key] !== null);
    }


    /**
     * remove a key => value pair from store
     *
     * @param string $key expects the key
     * @return $this
     */
    public function remove($key)
    {
        if($this->has($key))
        {
            unset($this->document[$key]);
        }
        return $this;
    }


    /**
     * reset the document store
     *
     * @return $this
     */
    public function reset()
    {
        $this->document = [];
        return $this;
    }


    /**
     * render a value stored in document by key forwarding the rendering to concrete class if key translates to a method
     * the can be called. if the key does not translate will pass the key´s value to concretes class render method
     *
     * @param string $key expects the key which value to render
     * @param null|mixed $default expects the default return value
     * @return mixed|null
     * @throws \Exception
     */
    public function render($key, $default = null)
    {
        $key = str_replace(['_'], '.', trim($key));
        $method = str_replace('.', '', ucwords($key, '.'));
        $method = lcfirst($method);
        if(method_exists($this, $method))
        {
            return $this->_render($key, call_user_func([$this, $method]));
        }else if(array_key_exists($key, $this->document)){
            return $this->_render($key, $this->document[$key]);
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * concrete rendering handles all the values store in document store
     *
     * @param string $key expects the key
     * @param string|array $content expects the content to render
     * @return mixed
     */
    abstract protected function _render($key, $content);


    /**
     * magic method to set key => value pair to document store
     *
     * @param string $name expects the key
     * @param string|array $value expects the value
     * @return Document
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }


    /**
     * magic method the get value by key
     *
     * @param string $name expects the key
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }


    /**
     * magic method to check if key exists in document store
     *
     * @param string $name expects the key
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * remove key => value pair from document store
     *
     * @param string $name expects the key
     * @return Document
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * on cast to array return document store
     *
     * @return array
     */
    public function __toArray()
   	{
   		return $this->document;
   	}


    /**
     * on serialization serialize document store
     *
     * @return array
     */
    public function __sleep()
    {
        return ['document'];
    }
}