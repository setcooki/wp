<?php

namespace Setcooki\Wp\Cache;

use Setcooki\Wp\Cache;
use Setcooki\Wp\Exception;

/**
 * Class File
 * @package Setcooki\Wp\Cache
 */
class File extends Cache
{
    const PATH                      = 'PATH';
    const EXTENSION                 = 'EXTENSION';
    const EXPIRATION                = 'EXPIRATION';

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    public $options = array
    (
        self::EXTENSION             => '',
        self::EXPIRATION            => 60
    );


    /**
     * @param null $options
     * @return null|File
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * @throws Exception
     */
    protected function init()
    {
        $path = rtrim(setcooki_get_option(self::PATH, $this), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        try
        {
            if(!is_dir($path))
            {
                if(mkdir($path))
                {
                    chmod($path, 0775);
                }else{
                    throw new Exception("unable to create cache directory");
                }
            }
            $path = new \SplFileInfo($path);
            if(!$path->isReadable())
            {
                throw new Exception("cache directory is not readable");
            }
            if(!$path->isWritable())
            {
                throw new Exception("cache directory is not writable");
            }
            setcooki_set_option(self::PATH, rtrim($path->getRealPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this);
      	}
        catch(Exception $e)
        {
            throw new Exception(setcooki_sprintf("file cache directory error: %s for: %s", $e->getMessage(), $path));
        }
    }


    /**
     * @param $key
     * @param null $default
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function get($key, $default = null)
    {
        if($this->has($key))
        {
            if(($value = file_get_contents(setcooki_get_option(self::PATH, $this) . $this->filename($key))) !== false)
            {
                if(time() >= (int)substr($value, 0, 10))
                {
                    $this->forget($key);
                    return setcooki_default($default);
                }else{
                    return unserialize(substr(trim($value), 10));
                }
            }else{
                throw new Exception("unable to read content from cache file");
            }
        }else{
            return setcooki_default($default);
        }
    }


    /**
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return bool
     */
    public function set($key, $value, $lifetime = null)
    {
        if($lifetime === null)
        {
            $lifetime = setcooki_get_option(self::EXPIRATION, $this);
        }
        return (bool)file_put_contents(setcooki_get_option(self::PATH, $this) . $this->filename($key), $this->timestamp($lifetime) . serialize($value), LOCK_EX);
    }


    /**
     * @param $key
     * @return bool
     */
    public function forget($key)
    {
        if($this->has($key))
        {
            return unlink(setcooki_get_option(self::PATH, $this) . $this->filename($key));
        }else{
            return false;
        }
    }


    /**
     * @param bool $expired
     * @return bool
     */
    public function purge($expired = true)
    {
        if(($files = @glob(setcooki_get_option(self::PATH, $this) . '*')) !== false)
        {
            foreach($files as $f)
            {
                if(is_file($f))
                {
                    if((bool)$expired)
                    {
                        if(($value = file_get_contents($f)) !== false)
                        {
                            if(time() >= substr($value, 0, 10))
                            {
                                unlink($f);
                            }
                        }
                    }else{
                        unlink($f);
                    }
                }
            }
        }
        @clearstatcache();
        return true;
    }


    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return (file_exists(setcooki_get_option(self::PATH, $this) . $this->filename($key)));
    }


    /**
     * @param $key
     * @return string
     */
    protected function filename($key)
    {
        if(!preg_match('/^[a-f0-9]{32}$/', $key))
        {
            $key = sha1(trim((string)$key));
        }
        $ext = setcooki_get_option(self::EXTENSION, $this);
        if(!empty($ext))
        {
            return (string)$key . '.' . trim(setcooki_get_option(self::EXTENSION, $this), '.');
        }else{
            return (string)$key;
        }
    }
}