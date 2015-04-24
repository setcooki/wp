<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;
use Setcooki\Wp\View;

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
     * @param $template
     * @param View $view
     */
    public function __construct($template, View $view = null)
    {
        $this->_vars = new \stdClass();
        $this->_view = $view;
        $this->_template = $template;
    }


    /**
     * @param $template
     * @param View $view
     * @return Template
     */
    public static function create($template, View $view = null)
    {
        return new self($template, $view);
    }


    /**
     * @param $key
     * @param $value
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
    }


    /**
     * @param null $key
     * @param string $default
     * @return array|mixed|null|object|\stdClass
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
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return setcooki_object_isset($this->_vars, $key);
    }


    /**
     * @param $vars
     */
    public function set($vars)
    {
        if(is_array($vars))
        {
            $this->_vars = setcooki_array_to_object($vars);
        }else if(is_object($vars)){
            $this->vars = $vars;
        }else{
            //do nothing
        }
    }


    /**
     * @param $key
     */
    public function remove($key)
    {
        if($this->has($key))
        {
            unset($this->_vars->{$key});
        }
    }


    /**
     *
     */
    public function reset()
    {
        $this->_vars = new \stdClass();
    }


    /**
     * @param null $template
     * @param null $vars
     * @return mixed|string
     * @throws Exception
     */
    public function render($template = null, $vars = null)
    {
        if($template === null)
        {
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
     * @param $template
     * @param null $vars
     */
    public function inc($template, $vars = null)
    {
        echo $this->render($template, $vars);
    }


    /**
     * @param $matches
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
     * @param bool $echo
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
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }


    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->add($name, $value);
    }


    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * @param $name
     */
    public function __unset($name)
    {
        $this->remove($name);
    }
}