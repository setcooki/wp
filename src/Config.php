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
     * @var null
     */
    protected static $_instance = null;

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
        if(is_array($config))
        {
            if(array_key_exists(0, $config))
            {
                foreach($config as $c)
                {
                    $this->_config = array_replace_recursive($this->_config, (array)$this->load($c, false));
                }
            }else{
                $this->_config = $this->load($config, false);
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
            self::$_instances[$ns] = self::$_instance = new self($config);
        }
        return self::$_instances[$ns];
    }


    /**
     * get a config instance with namespace identifier or get last set instance if no namespace identifier is supplied
     *
     * @param string|null $ns expect the optional namespace identifier
     * @return Config
     * @throws Exception
     */
    public static function instance($ns = null)
    {
        if($ns !== null && array_key_exists($ns, self::$_instances))
        {
            return self::$_instances[$ns];
        }else if(self::$_instance !== null){
            return self::$_instance;
        }else{
            throw new Exception(setcooki_sprintf('config instance under: %s not set', $ns));
        }
    }


    /**
     * check if an instance is set or instance under namespace is set
     *
     * @param null|string $ns expect the optional namespace identifier
     * @return bool
     */
    public static function hasInstance($ns = null)
    {
        if($ns !== null)
        {
            return (array_key_exists($ns, self::$_instances)) ? true : false;
        }else{
            return (self::$_instance !== null) ? true : false;
        }
    }


    /**
     * get or set/change instance with or without namespace. if all arguments are null will get the current active instance
     * see Setcooki\Wp\Config::instance. if the first argument is not null and the second is null will try to get instance
     * for that namespace. if all arguments are not null will change the namespace name of an instance
     *
     * @param null|string $ns expects optional existing namespace identifier
     * @param null|string $new expects optional namespace identifier for replacement
     * @return Config
     * @throws Exception
     */
    public static function ns($ns = null, $new = null)
    {
        if($ns !== null && $new !== null)
        {
            if(array_key_exists($ns, self::$_instances))
            {
                self::$_instances[$new] = self::$_instances[$ns];
                unset(self::$_instances[$ns]);
                return self::$_instances[$new];
            }else{
                throw new Exception(setcooki_sprintf('no register config ns found with: %s', $ns));
            }
        }else if($ns !== null && $new === null){
            return self::instance($ns);
        }else{
            return self::instance();
        }
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
        if(is_string($file) && preg_match('/\.phtml|\.php([0-9]{1,})?|\.inc$/i', $file))
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
     * static shortcut function for Setcooki\Wp\Config::set
     *
     * @see Setcooki\Wp\Config::set
     * @param null|string $key expects the config key
     * @param null|mixed $value expects the config value
     * @param null|string $ns expects the optional namespace of the config store
     * @return void
     */
    public static function s($key = null, $value = null, $ns = null)
    {
        self::instance($ns)->set($key, $value);
    }


    /**
     * set key => value pair to config store by passing optional namespace identifier. if the ns identifier is empty will
     * lookup current set storage instance
     *
     * @param null|string $key expects the config key
     * @param null|mixed $value expects the config value
     * @param null|string $ns expects the optional namespace of the config store
     * @return void
     */
    public function set($key = null, $value = null, $ns = null)
    {
        setcooki_object_set(self::instance($ns)->_config, $key, $value);
    }


    /**
     * static shortcut function for Setcooki\Wp\Config::get
     *
     * @see Setcooki\Wp\Config::get
     * @param null|string $key expects the config key
     * @param null|mixed $default expects a default return value
     * @param null|string $ns expects the optional namespace of the config store
     * @return mixed
     * @throws \Exception
     */
    public static function g($key = null, $default = null, $ns = null)
    {
        return self::instance($ns)->get($key, $default, $ns);
    }


    /**
     * get a value by key from config store by optional namespace. if the ns identifier is empty will lookup current
     * set storage instance
     *
     * @param null|string $key expects the config key
     * @param null|mixed $default expects a default return value
     * @param null|string $ns expects the optional namespace of the config store
     * @return mixed
     * @throws \Exception
     */
    public function get($key = null, $default = null, $ns = null)
    {
        if(setcooki_object_isset(self::instance($ns)->_config, $key))
        {
            return setcooki_object_get(self::instance($ns)->_config, $key, setcooki_default($default));
        }
        return setcooki_default($default);
    }


    /**
     * static shortcut for Setcooki\Wp\Config::has
     *
     * @since 1.1.3
     * @param null|string $key expects the config key
     * @param null|string $ns expects the optional namespace of the config store
     * @return bool
     * @throws Exception
     */
    public static function h($key = null, $ns = null)
    {
        return self::instance($ns)->has($ns, $key);
    }


    /**
     * checks if a config value by key is set in config store identified by namespace identifier in first argument
     *
     * @param string $ns expects the namespace of the config store
     * @param null|string $key expects the config key
     * @param bool $strict expects boolean flag for checking config value validity
     * @return bool
     */
    public function has($ns, $key, $strict = false)
    {
        return (isset(self::$_instances[$ns]) && setcooki_object_isset(self::$_instances[$ns]->_config, $key, $strict)) ? true : false;
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