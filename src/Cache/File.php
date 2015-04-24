<?php

namespace Setcooki\Wp\Cache;

use Setcooki\Wp\Cache;
use Setcooki\Wp\Exception;

class File extends Cache
{
    const PATH                      = 'PATH';
    const EXTENSION                 = 'EXTENSION';
    const EXPIRATION                = 'EXPIRATION';

    protected static $_instance = null;

    public $options = array
    (
        self::EXTENSION             => '',
        self::EXPIRATION            => 60
    );


    /**
     * @param null $options
     * @return null|Apc
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
        try
        {
            $dir = new \SplFileInfo(setcooki_get_option(self::PATH, $this));
            if(!$dir->isReadable())
            {
                throw new Exception("cache directory is not readable");
            }
            if(!$dir->isWritable())
            {
                throw new Exception("cache directory is not writable");
            }
            setcooki_set_option(self::PATH, rtrim($dir->getRealPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this);
      	}
        catch(Exception $e)
        {
            throw new Exception(setcooki_sprintf("cache directory file info error: %d, %s", $e->getCode(), $e->getMessage()));
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
     * @return void
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