<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

class Config
{
    private $_config = array();

    protected static $_instances = array();


    /**
     * @param $config
     * @throws Exception
     */
    public function __construct($config)
    {
        if(is_array($config) && array_key_exists(0, $config))
        {
            foreach($config as $c)
            {
                $this->_config = array_merge($this->_config, (array)$this->load($c, false));
            }
        }else{
            $this->_config = $this->load($config, false);
        }
    }


    /**
     * @param $config
     * @param $ns
     * @return mixed
     */
    public static function init($config, $ns)
    {
        if(!array_key_exists($ns, self::$_instances))
        {
            self::$_instances[$ns] = new self($config);
        }
        return self::$_instances[$ns];
    }


    /**
     * @param $file
     * @param bool $throw
     * @return array
     * @throws Exception
     */
    public static function load($file, $throw = true)
    {
        if(preg_match('/\.phtml|\.php([0-9]{1,})?|\.inc$/i', $file))
        {
            if(is_file($file))
            {
                $file = require $file;
                if($file === 1)
                {
                    $file = array_slice(get_defined_vars(), 1);
                }
            }else{
                if((bool)$throw){
                    throw new Exception(sprintf(_("unable to load php config file: %s"), $file));
                }else{
                    return array();
                }
            }
        }
        return (array)$file;
    }


    /**
     * @param $ns
     * @param null $key
     * @param null $value
     */
    public static function set($ns, $key = null, $value = null)
    {
        if(isset(self::$_instances[$ns]))
        {
            setcooki_object_set(self::$_instances[$ns]->_config, $key, $value);
        }
    }


    /**
     * @param $ns
     * @param null $key
     * @param null $default
     * @return array|mixed
     * @throws \Exception
     */
    public static function get($ns, $key = null, $default = null)
    {
        if(isset(self::$_instances[$ns]) && setcooki_object_isset(self::$_instances[$ns]->_config, $key))
        {
            return setcooki_object_get(self::$_instances[$ns]->_config, $key, setcooki_default($default));
        }
        return setcooki_default($default);
    }


    /**
     * @param $ns
     * @param $key
     * @return bool
     */
    public static function has($ns, $key)
    {
        return (isset(self::$_instances[$ns]) && setcooki_object_isset(self::$_instances[$ns]->_config, $key)) ? true : false;
    }


    /**
     * @param $ns
     */
    public static function reset($ns)
    {
        if(isset(self::$_instances[$ns]))
        {
           self::$_instances[$ns]->_config = array();
        }
    }
}