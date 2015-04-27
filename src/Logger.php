<?php

namespace Setcooki\Wp;

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
     * class constructor expects a log dir and optional class options
     *
     * @param string $dir expects a log directory path
     * @param null|array $options optional options
     * @throws Exception
     */
    protected function __construct($dir, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->_dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if(!file_exists($this->_dir))
        {
            mkdir($this->_dir, setcooki_get_option(self::PERMISSION, $this), true);
        }
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
     * shortcut function for Setcooki\Wp\Logger::instance
     *
     * @see Setcooki\Wp\Logger::instance
     * @param string $dir expects a log directory path
     * @param null|array $options optional options
     * @return null|Logger
     */
    public static function create($dir = null, $options = null)
    {
        return self::instance($dir, $options);
    }


    /**
     * static singleton setter/getter function
     *
     * @see Setcooki\Wp\Logger::__construct
     * @param string $dir expects a log directory path
     * @param null|array $options optional options
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
     * check if a logger instance is set
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (self::$_instance !== null) ? true : false;
    }


    /**
     * method to log emergency messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function emergency($message, $args = null)
    {
        $this->log(self::EMERGENCY, $message, $args);
    }


    /**
     * method to log alert messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function alert($message, $args = null)
    {
        $this->log(self::ALERT, $message, $args);
    }


    /**
     * method to log critical messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function critical($message, $args = null)
    {
        $this->log(self::CRITICAL, $message, $args);
    }


    /**
     * method to log error messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function error($message, $args = null)
    {
        $this->log(self::ERROR, $message, $args);
    }


    /**
     * method to log warning messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function warning($message, $args = null)
    {
        $this->log(self::WARNING, $message, $args);
    }


    /**
     * method to log notice messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function notice($message, $args = null)
    {
        $this->log(self::NOTICE, $message, $args);
    }


    /**
     * method to log info messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function info($message, $args = null)
    {
        $this->log(self::INFO, $message, $args);
    }


    /**
     * method to log debug messages
     *
     * @see Setcooki\Wp\Logger::log
     * @param mixed $message expects a valid logger message object
     * @param null|array $args expects optional additional arguments
     * @return void
     */
    public function debug($message, $args = null)
    {
        $this->log(self::DEBUG, $message, $args);
    }


    /**
     * log method expecting a log message object which can be instance of Exception, array with message with placeholders
     * to be replaces with php´s sprintf function or a simple string message. the second argument expects a php recognizable
     * log level as defined by php´s LOG_ constants. the third argument can be an optional array with key => value pairs
     * for extra logging
     *
     * @param string|mixed|Exception $object expects a log object
     * @param null|int $level expects the log level
     * @param null|array $args expects a optional argument array
     * @return null
     * @throws Exception
     */
    public function log($object, $level = null, $args = null)
    {
        $levels = setcooki_get_option(self::LOG_LEVEL, $this);

        if($object instanceof \ErrorException)
        {
            $message = $object->getMessage() . ' in ' . $object->getFile() . ':' . $object->getLine();
            $level = (int)$object->getSeverity();
        }else if($object instanceof \Exception){
            $message = $object->getMessage() . ' in ' . $object->getFile() . ':' . $object->getLine();
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

        //if in wp log mode log message to log file
        if(isset($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_LOG]) && $GLOBALS[SETCOOKI_NS][SETCOOKI_WP_LOG])
        {
            $this->write($data);
        }

        //if in wp debug mode return log message to be send to output stream
        if(isset($GLOBALS[SETCOOKI_NS][SETCOOKI_WP_DEBUG]) && $GLOBALS[SETCOOKI_NS][SETCOOKI_WP_DEBUG])
        {
            return $data;
        }else{
            return;
        }
    }


    /**
     * static logger method assuming logger class has been initialized before and class instance is registered
     *
     * @see Setcooki\Wp\Logger::log
     * @param string|mixed|Exception $object expects a log object
     * @param null|int $level expects the log level
     * @param null|array $args expects a optional argument array
     * @return null
     * @throws Exception
     */
    public static function l($object, $level = null, $args = null)
    {
        if(self::hasInstance())
        {
            return self::instance()->log($object, $level, $args);
        }
        return '';
    }


    /**
     * write a log message to log file
     *
     * @param string $message write log message to file
     * @return void
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
     * reset logger
     *
     * @return void
     */
    public function reset()
    {
        if($this->handle)
        {
            @fclose($this->handle);
        }
        unset($this->_logs);
        $this->_logs = array();
    }


    /**
     * reset logger and flush logs to php output stream
     *
     * @return void
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