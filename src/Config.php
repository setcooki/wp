<?php

namespace Setcooki\Wp;

/**
 * Class Config
 * @package Setcooki\Wp
 */
class Config
{
    /**
     * @var array
     */
    private $_config = array();


    /**
     * @var array
     */
    protected static $_instances = array();


    /**
     * load config files passed in first argument which can be a single config file location or an array of multiple config
     * files which are merge to one config object
     *
     * @param string|mixed $config expects either a string file name or an array of config files
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
     * static class creator which is the expected way to use config class. pass config file or files in first argument and
     * set namespace identifier in second argument. that way multiple config namespaces/instances can be maintained
     *
     * @see Cache::__construct
     * @param string|mixed $config expects either a string file name or an array of config files
     * @param string $ns expects config namespace identifier
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
     * load a config file into config store
     *
     * @param string $file expects a absolute path to config file
     * @param bool $throw expects boolean value to throw exception on failure or return array
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
     * set key => value pair to config store by namespace
     *
     * @param string $ns expects the namespace of the config store
     * @param null|string $key expects the config key
     * @param null|mixed $value expects the config value
     */
    public static function set($ns, $key = null, $value = null)
    {
        if(isset(self::$_instances[$ns]))
        {
            setcooki_object_set(self::$_instances[$ns]->_config, $key, $value);
        }
    }


    /**
     * get a value by key from config store by namespace
     *
     * @param string $ns expects the namespace of the config store
     * @param null|string $key expects the config key
     * @param null|mixed $default expects a default return value
     * @return mixed
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
     * checks if a config value by key is set in config store identified by namespace identifier in first argument
     *
     * @param string $ns expects the namespace of the config store
     * @param null|string $key expects the config key
     * @return bool
     */
    public static function has($ns, $key)
    {
        return (isset(self::$_instances[$ns]) && setcooki_object_isset(self::$_instances[$ns]->_config, $key)) ? true : false;
    }


    /**
     * unset config store for namespace identifier in first argument
     *
     * @param string $ns expects the namespace of the config store
     * @return void
     */
    public static function reset($ns)
    {
        if(isset(self::$_instances[$ns]))
        {
           self::$_instances[$ns]->_config = array();
        }
    }
}