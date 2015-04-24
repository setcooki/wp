<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

/**
 * Class Logger
 * @package Setcooki\Wp
 */
class Logger
{
    const EMERGENCY         = LOG_EMERG;
    const ALERT             = LOG_ALERT;
    const CRITICAL          = LOG_CRIT;
    const ERROR             = LOG_ERR;
    const WARNING           = LOG_WARNING;
    const NOTICE            = LOG_NOTICE;
    const INFO              = LOG_NOTICE;
    const DEBUG             = LOG_DEBUG;

    const LOG_LEVEL         = 'LOG_LEVEL';
    const EXTENSION         = 'EXTENSION';
    const FILE_NAME         = 'FILE_NAME';
    const PERMISSION        = 'PERMISSION';
    const DATE_FORMAT       = 'DATE_FORMAT';
    const BACKTRACE         = 'BACKTRACE';
    const FLUSH             = 'FLUSH';

    /**
     * @var null|resource
     */
    public $handle = null;

    /**
     * @var null|string
     */
    public $file = null;

    /**
     * @var array
     */
    private $_logs = array();

    /**
     * @var null|string
     */
    protected $_dir = null;

    /**
     * @var array
     */
    protected $_levelMap = array
    (
        self::EMERGENCY     => 'EMERGENCY',
        self::ALERT         => 'ALERT',
        self::CRITICAL      => 'CRITICAL',
        self::ERROR         => 'ERROR',
        self::WARNING       => 'WARNING',
        self::NOTICE        => 'NOTICE',
        self::INFO          => 'INFO',
        self::DEBUG         => 'DEBUG'
    );

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    public $options = array
    (
        self::LOG_LEVEL     => 0,
        self::EXTENSION     => 'log',
        self::FILE_NAME     => null,
        self::PERMISSION    => 0777,
        self::DATE_FORMAT   => 'Y-m-d G:i:s.u',
        self::BACKTRACE     => false,
        self::FLUSH         => false
    );


    /**
     * @param $dir
     * @param null $options
     * @throws Exception
     */
    protected function __construct($dir, $options = null)
    {
        setcooki_init_options($options, $this);
        if(!file_exists($dir))
        {
            mkdir($dir, setcooki_get_option(self::PERMISSION, $this), true);
        }
        $this->_dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if(is_writable($this->_dir))
        {
            if(setcooki_has_option(self::FILE_NAME, $this, true))
            {
                $name = trim(setcooki_get_option(self::FILE_NAME, $this));
            }else{
                $name = strftime('%Y-%m-%d', time());
            }
            $this->file = $this->_dir . $name . '.' . trim(setcooki_get_option(self::EXTENSION, $this), ' .');
        }else{
            throw new Exception(setcooki_sprintf("log directory: %s is not writable", $this->_dir));
        }
    }


    /**
     * @param $dir
     * @param null $options
     * @return null|Logger
     */
    public static function create($dir = null, $options = null)
    {
        return self::instance($dir, $options);
    }


    /**
     * @param $dir
     * @param null $options
     * @return null|Logger
     */
    public static function instance($dir = null, $options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($dir, $options);
        }
        return self::$_instance;
    }


    /**
     * @return bool
     */
    public static function hasInstance()
    {
        return (self::$_instance !== null) ? true : false;
    }


    /**
     * @param $message
     * @param null $args
     */
    public function emergency($message, $args = null)
    {
        $this->log(self::EMERGENCY, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function alert($message, $args = null)
    {
        $this->log(self::ALERT, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function critical($message, $args = null)
    {
        $this->log(self::CRITICAL, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function error($message, $args = null)
    {
        $this->log(self::ERROR, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function warning($message, $args = null)
    {
        $this->log(self::WARNING, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function notice($message, $args = null)
    {
        $this->log(self::NOTICE, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function info($message, $args = null)
    {
        $this->log(self::INFO, $message, $args);
    }


    /**
     * @param $message
     * @param null $args
     */
    public function debug($message, $args = null)
    {
        $this->log(self::DEBUG, $message, $args);
    }


    /**
     * @param $object
     * @param null $level
     * @param null $args
     * @throws Exception
     */
    public static function l($object, $level = null, $args = null)
    {
        if(self::hasInstance())
        {
            self::instance()->log($object, $level, $args);
        }
    }


    /**
     * @param $object
     * @param null $level
     * @param null $args
     * @return null
     * @throws Exception
     */
    public function log($object, $level = null, $args = null)
    {
        $levels = setcooki_get_option(self::LOG_LEVEL, $this);

        if($object instanceof \ErrorException)
        {
            $message = trim($object->getMessage());
            $level = (int)$object->getSeverity();
        }else if($object instanceof \Exception){
            $message = trim($object->getMessage());
            $level = self::ERROR;
        }else if(is_array($object) && array_key_exists(0, $object)){
            $message = setcooki_sprintf((string)$object[0], ((sizeof($object) > 1) ? array_slice($object, 1, sizeof($object)) : null));
            $level = (int)$level;
        }else{
            $message = trim((string)$object);
            $level = (int)$level;
        }

        if(!array_key_exists($level, $this->_levelMap))
        {
            return;
        }
        if(is_array($levels) && !in_array($level, $levels))
        {
            return;
        }else if(is_int($levels) && ($level < $levels || $levels === -1)){
            return;
        }else if(is_bool($levels) && !$levels){
            return;
        }

        if(!@is_resource($this->handle))
        {
            if(($this->handle = fopen($this->file, 'a')) === false)
            {
                throw new Exception(setcooki_sprintf('unable to create log file: %s', $this->file));
            }
        }

        $time = microtime(true);
        $micro = sprintf("%06d", ($time - floor($time)) * 1000000);
        $date = new \DateTime(date('Y-m-d H:i:s.'.$micro, $time));
        $date = $date->format(setcooki_get_option(self::DATE_FORMAT, $this));

        $data = "";
        $data .= "[{$date}]";
        $data .= " ";
        $data .= "[{$this->_levelMap[$level]}]";
        $data .= " ";
        $data .= $message;

        if(!empty($args))
        {
            $tmp = array();
            foreach((array)$args as $k => $v)
            {
                if(is_numeric($k))
                {
                    $tmp[] = $v;
                }else{
                    $tmp[] = "$k: $v";
                }
            }
            $data .= ", (".implode(', ', $tmp).")";
        }

        if(setcooki_get_option(self::BACKTRACE, $this))
        {
            if($object instanceof Exception)
            {
                $trace = $object->getTraceAsString();
            }else{
                ob_start();
                debug_print_backtrace();
                $trace = ob_get_contents();
                ob_end_clean();
            }
            $data .= rtrim(PHP_EOL . $trace);
        }

        $data .= PHP_EOL;

        if(setcooki_get_option(self::FLUSH, $this))
        {
            $this->_logs[trim(md5($data))] = $data;
        }

        $this->write($data);

        unset($tmp);
        unset($data);
        unset($date);

        return null;
    }


    /**
     * @param $message
     * @throws Exception
     */
    public function write($message)
    {
        if($this->handle)
        {
            if(fwrite($this->handle, $message) !== false)
            {
                @clearstatcache();
            }else{
                throw new Exception('unable to write to log file - check permissions');
            }
        }
    }


    /**
     *
     */
    public function __destruct()
    {
        if($this->handle)
        {
            @fclose($this->handle);
        }
        if(!empty($this->_logs))
        {
            if(strtolower(php_sapi_name()) === 'cli')
            {
                echo implode(PHP_EOL, $this->_logs);
            }else{
                echo '<pre>' . implode('', array_values($this->_logs)) . '</pre>';
            }
        }
        unset($this->_logs);
        $this->_logs = array();
        @clearstatcache();
    }
}