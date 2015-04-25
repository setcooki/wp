<?php

namespace Setcooki\Wp;

/**
 * Class Template
 * @package Setcooki\Wp
 */
class Template
{
    /**
     * @var null|\stdClass
     */
    protected $_vars = null;

    /**
     * @var null
     */
    protected $_view = null;

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
     * @param string $template expects absolute template file location
     * @param View $view expects option view instance
     */
    public function __construct($template, View $view = null)
    {
        $this->_vars = new \stdClass();
        $this->_view = $view;
        $this->_template = DIRECTORY_SEPARATOR . ltrim(trim($template), DIRECTORY_SEPARATOR);
    }


    /**
     * shortcut function to create a template see Setcooki\Wp\Template::__construct
     *
     * @see Setcooki\Wp\Template::__construct
     * @param string $template expects absolute template file location
     * @param View $view expects option view instance
     * @return Template
     */
    public static function create($template, View $view = null)
    {
        return new self($template, $view);
    }


    /**
     * setter/getter for view instance
     *
     * @param View $view expects optional view instance
     * @return null|View
     */
    public function view(View $view = null)
    {
        if($view !== null)
        {
            $this->_view = $view;
        }
        return $this->_view;
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
    public function get($key = null, $default = "")
    {
        if($key !== null)
        {
            if($this->has($key))
            {
                return setcooki_object_get($this->_vars, $key, $default);
            }else{
                return setcooki_default($default);
            }
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
     * @throws Exception
     */
    public function set($vars)
    {
        if(is_array($vars))
        {
            $this->_vars = setcooki_array_to_object($vars);
        }else if(is_object($vars)){
            $this->vars = $vars;
        }else{
            throw new Exception("first argument is not valid variable");
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
     * render the template by loading template file and parsing template placeholders filling them with variable values
     * if variables can be matched. the second argument can receive more runtime variables which are only use for the
     * current to render template
     *
     * @param null|string $template expects optional overwrite template file location
     * @param null|mixed $vars expects optional variables to apply
     * @return mixed
     * @throws Exception
     */
    public function render($template = null, $vars = null)
    {
        if($template !== null)
        {
            $template = DIRECTORY_SEPARATOR . ltrim(trim($template), DIRECTORY_SEPARATOR);
        }else{
            $template = $this->_template;
        }
        if(is_file($template))
        {
            if($vars !== null)
            {
                $this->add($vars);
            }
            ob_start();
            @extract((array)$this->get(), EXTR_SKIP);
            include $template;
            $buffer = ob_get_clean();
            $buffer = preg_replace_callback('/\{(\()([^)}]{2,})\)\}/i', array($this, 'parse'), $buffer);
            $buffer = preg_replace_callback('/\{(\$|\%)([^\}]{2,})\}/i', array($this, 'parse'), $buffer);
            return $this->_buffer = $buffer;
        }else{
            throw new Exception(sprintf("template: %s not found", $template));
        }
    }


    /**
     * shortcut function to render and echo output a template in a include sort of way - see Setcooki\Wp\Template::render
     * for more
     *
     * @see Setcooki\Wp\Template::render
     * @param null|string $template expects optional overwrite template file location
     * @param null|mixed $vars expects optional variables to apply
     * @throws Exception
     */
    public function inc($template, $vars = null)
    {
        echo $this->render($template, $vars);
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
        }else{
            return $buffer;
        }
    }


    /**
     * magic function to intercept overloading class properties which will result in looking with property name in variable
     * storage
     *
     * @param string $name expects property name
     * @return mixed
     */
    public function __get($name)
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
}