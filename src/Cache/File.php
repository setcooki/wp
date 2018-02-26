<?php

namespace Setcooki\Wp\Cache;

use Setcooki\Wp\Exception;

/**
 * Class File
 *
 * @package     Setcooki\Wp\Cache
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class File extends Cache
{
    /**
     * path option defines the path where cache files are stored
     */
    const PATH                      = 'PATH';

    /**
     * extension option defines the file extension value for cache files
     */
    const EXTENSION                 = 'EXTENSION';

    /**
     * expiration option defines the expiration value of cache file
     */
    const EXPIRATION                = 'EXPIRATION';


    /**
     * @var array
     */
    public $options = array
    (
        self::EXTENSION             => '',
        self::EXPIRATION            => 60
    );


    /**
     * File constructor.
     * @param null $options
     * @throws Exception
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->init();
    }


    /**
     * @param null $options
     * @return File
     */
    public static function create($options = null)
    {
        return new self($options);
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
                    throw new Exception(__("Unable to create cache directory", SETCOOKI_WP_DOMAIN));
                }
            }
            $path = new \SplFileInfo($path);
            if(!$path->isReadable())
            {
                throw new Exception(__("Cache directory is not readable", SETCOOKI_WP_DOMAIN));
            }
            if(!$path->isWritable())
            {
                throw new Exception(__("Cache directory is not writable"), SETCOOKI_WP_DOMAIN);
            }
            setcooki_set_option(self::PATH, rtrim($path->getRealPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this);
      	}
        catch(Exception $e)
        {
            throw new Exception(setcooki_sprintf(__("File cache directory error: %s for: %s", SETCOOKI_WP_DOMAIN), $e->getMessage(), $path));
        }
    }


    /**
     * @param string $key
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
                throw new Exception(__("Unable to read content from cache file", SETCOOKI_WP_DOMAIN));
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