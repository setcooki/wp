<?php

namespace Setcooki\Wp\Content;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Wp;
use Setcooki\Wp\Traits\Cache;
use Setcooki\Wp\Util\Params;

/**
 * Class Template
 *
 * @package     Setcooki\Wp\Content
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Template
{
    use Cache;


    /**
     * @var null|\stdClass
     */
    protected $_vars = null;

    /**
     * @var null
     */
    protected $_template = null;

    /**
     * @var string
     */
    protected $_buffer = '';


    /**
     * class constructor expects a template file as first argument and optional view instance as second
     *
     * @param string $template expects absolute or relative template file
     * @param null $vars
     * @throws \Exception
     */
    public function __construct($template, $vars = null)
    {
        $this->_vars = new \stdClass();
        $this->_template = $template;
        if(!is_null($vars))
        {
            $this->set($vars);
        }
    }


    /**
     * shortcut function to create a template see \Setcooki\Wp\Content\Template::__construct()
     *
     * @see \Setcooki\Wp\Content\Template::__construct()
     * @param string $template expects absolute or relative template file
     * @param null|mixed expects optional vars to set
     * @return Template
     */
    public static function create($template, $vars = null)
    {
        return new self($template, $vars);
    }


    /**
     * setter/getter method for absolute or relative template file
     *
     * @param null|string $template expects optional template file
     * @return bool|null|string
     */
    public function template($template = null)
    {
        if(!is_null($template))
        {
            $this->_template = $template;
        }
        return $this->_template;
    }


    /**
     * add a single variable to var storage or pass array of key => value variable pairs to var storage
     *
     * @param string|array $key expects variable name or array of key => value variable pairs
     * @param string|mixed $value expects the variable value in case first argument is not an array
     * @return null|\stdClass;
     */
    public function add($key, $value = '_NIL_')
    {
        if($value === '_NIL_')
        {
            if(is_array($key) || is_object($key))
            {
                foreach($key as $k => $v)
                {
                    $this->_vars->{$k} = (is_array($v)) ? setcooki_array_to_object($v) : $v;
                }
            }
        }else{
            $this->_vars->{$key} = (is_array($value)) ? setcooki_array_to_object($value) : $value;
        }
        return $this->_vars;
    }


    /**
     * get a variable value from var storage by variable name or value from variable by path which is by "." syntax
     *
     * @param null|string $key expects the variable key/path
     * @param string|mixed $default expects optional default return value
     * @return mixed
     * @throws \Exception
     */
    public function &get($key = null, $default = "")
    {
        if($key !== null)
        {
            if($this->has($key))
            {
                $key = setcooki_object_get($this->_vars, $key, $default);
            }else{
                $key = setcooki_default($default);
            }
            return $key;
        }else{
            return $this->_vars;
        }
    }


    /**
     * check the existence of a variable in strict or non-strict mode testing the variable value for validity
     *
     * @param null|string $key expects the variable key/path
     * @param bool $strict expects boolean value for strict mode
     * @return bool
     */
    public function has($key, $strict = false)
    {
        return setcooki_object_isset($this->_vars, $key, $strict);
    }


    /**
     * initially set or init the variable storage with an std object or array with key => value pairs
     *
     * @param array|object $vars expects the variables to set
     * @return void
     * @throws \Exception
     */
    public function set($vars)
    {
        if($vars instanceof Params)
        {
            $this->_vars = $vars->__toObject();
        }else if(is_array($vars)){
            $this->_vars = setcooki_array_to_object($vars);
        }else if(is_object($vars)){
            $this->_vars = (object)$vars;
        }else{
            throw new Exception(__("Vars passed in first argument are not allowed in this context", SETCOOKI_WP_DOMAIN));
        }
    }


    /**
     * remove a variable from var storage
     *
     * @param string $key expects the variable name
     * @return void
     */
    public function remove($key)
    {
        if($this->has($key))
        {
            unset($this->_vars->{$key});
        }
    }


    /**
     * reset the variable storage removing all variables
     *
     * @return void
     */
    public function reset()
    {
        $this->_vars = new \stdClass();
        $this->_buffer = '';
    }


    /**
     * render template file passed in constructor parsing template placeholders and filling them with variable values
     * assign to the template instance. the buffer or returned string can be cached if first argument is a int
     * value >= which represents the cache lifetime. cache instance must be set with View::cache prior to usage. the second
     * optional argument can be a callable sending view return buffer to that function for further manipulation
     *
     * @param null|int $cache expects optional cache lifetime
     * @param callable|null $callback expects optional buffer callback
     * @return string
     * @throws \Exception
     */
    public function render($cache = null, callable $callback = null)
    {
        if(($template = self::lookup($this->_template)) !== false)
        {
            if(!is_null($cache) && ctype_digit($cache) && $this->cache())
            {
                $cache = (int)$cache;
                $key = \Setcooki\Wp\Cache\Cache::key($template, $this->get());
            }else{
                $cache = -1;
                $key = null;
            }

            if($cache >= 0 && ($buffer = $this->cache($key, false)) !== false)
            {
                //do nothing
            }else{
                ob_start();
                @extract((array)$this->get(), EXTR_SKIP);
                require $template;
                $buffer = ob_get_clean();
                $buffer = preg_replace_callback('/\{(\()([^)}]{2,})\)\}/i', [$this, 'parse'], $buffer);
                $buffer = preg_replace_callback('/\{(\$|\%)([^\}]{2,})\}/i', [$this, 'parse'], $buffer);
                if($cache >= 0)
                {
                    $this->cache($key, $template, $cache);
                }
            }
            @clearstatcache();
            if(!is_null($callback))
            {
                $buffer = call_user_func($callback, [$buffer, $this]);
            }
            return $this->_buffer = $buffer;
        }else{
            throw new Exception(setcooki_sprintf(__("Template file: %s not found or not readable", SETCOOKI_WP_DOMAIN), $template));
        }
    }


    /**
     * static function to render a template file see Template::render()
     *
     * @see Template::create()
     * @see Template::render()
     * @param string $template expects tempalte absolute or relative file
     * @param null|mixed $vars expects variables to set
     * @param callable|null $callback expects optional buffer callback
     * @return string
     * @throws \Exception
     */
    public static function r($template, $vars = null, callable $callback = null)
    {
        return self::create($template, $vars)->render(null, $callback);
    }


    /**
     * include a file and pass array in second argument as local vars
     *
     * @param string $file expects a file path
     * @param array $vars expects optional vars
     * @return string
     * @throws \Exception
     */
    public function inc($file, Array $vars = [])
    {
        if(($file = self::lookup($file)) !== false)
        {
            extract($vars);
            ob_start();
            require $file;
            return ob_get_clean();
        }else{
            throw new Exception(setcooki_sprintf(__("Unable to include file: %s", SETCOOKI_WP_DOMAIN), $file));
        }
    }


    /**
     * lookup template file which can be either a absolute file path or a relative file path from plugin/theme base. if
     * no template file was found throws exception. if template was not found returns boolean false
     *
     * @param string $template expects a template file path
     * @return string|bool
     */
    public static function lookup($template)
    {
        $template = DIRECTORY_SEPARATOR . ltrim(trim($template), DIRECTORY_SEPARATOR);
        if(is_file($template) && is_readable($template))
        {
            return $template;
        }
        $template = Wp::b($template);
        if(is_file($template) && is_readable($template))
        {
            return $template;
        }
        return false;
    }


    /**
     * template buffer placeholder parser that will replace all placeholders "{$.}" with variables values in variable
     * storage
     *
     * @param array $matches regex matches
     * @return mixed|string
     */
    protected function parse($matches)
    {
        $return = "";

        if(array_key_exists(1, $matches))
        {
            switch(trim($matches[1]))
            {
                //function
                case '(';
                    //condition ? x : y
                    if(preg_match('/^\$([^\s]+)\s+\?\s+([^\:]+)\s+\:\s+(.*)$/i', $matches[2], $m))
                    {
                        if(sizeof($m) >= 4){
                            $return = ($this->has($m[1]) && $this->get($m[1])) ? $m[2] : $m[3];
                        }
                    }
                    break;
                //local var
                case '$':
                    $return = ($this->has($matches[2])) ? $this->get($matches[2]) : $matches[2];
                    break;
                //global var
                case '%':
                    //not implemented yet
                    break;
                default:
            }
        }
        if(is_array($return))
        {
            return (string)$return;
        }else{
            return $return;
        }
    }


    /**
     * flush template buffer to php output stream or return it only
     *
     * @param bool $echo expects boolean value to either echo the buffer or return it only
     * @return string
     */
    public function flush($echo = false)
    {
        $buffer = $this->_buffer;
        $this->_buffer = '';
        if((bool)$echo)
        {
            echo $buffer;
        }
        return $buffer;
    }


    /**
     * magic function to intercept overloading class properties which will result in looking with property name in variable
     * storage
     *
     * @param string $name expects property name
     * @return mixed
     */
    public function &__get($name)
    {
        return $this->get($name);
    }


    /**
     * magic function to intercept overloading class properties which will set the property name as new variable in variable
     * storage setting the value in second argument
     *
     * @param string $name expects property name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->add($name, $value);
    }


    /**
     * magic function to intercept overloading class properties which will check for property name in variable storage
     *
     * @param string $name expects property name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * magic function to intercept overloading class properties which will unset the property name in variable storage
     *
     * @param string $name expects property name
     * @return void
     */
    public function __unset($name)
    {
        $this->remove($name);
    }


    /**
     * reset template on destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->reset();
    }


    /**
     * reset template on clone
     *
     * @return void
     */
    public function __clone()
    {
        $this->reset();
    }


    /**
     * on serialize keep the following vars
     *
     * @return array
     */
    public function __sleep()
    {
        return ['vars', 'template'];
    }
}
