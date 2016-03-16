<?php

namespace Setcooki\Wp;

/**
 * Class Error
 * @package Setcooki\Wp
 */
class Error
{
    /**
     * @var array
     */
    public static $map = array
    (
        E_ERROR             => LOG_ERR,
        E_WARNING           => LOG_WARNING,
        E_PARSE             => LOG_ERR,
        E_NOTICE            => LOG_NOTICE,
        E_CORE_ERROR        => LOG_ALERT,
        E_CORE_WARNING      => LOG_WARNING,
        E_COMPILE_ERROR     => LOG_ALERT,
        E_COMPILE_WARNING   => LOG_WARNING,
        E_USER_ERROR        => LOG_ERR,
        E_USER_WARNING      => LOG_WARNING,
        E_USER_NOTICE       => LOG_NOTICE,
        E_STRICT            => LOG_NOTICE,
        E_RECOVERABLE_ERROR => LOG_ERR,
        E_DEPRECATED        => LOG_NOTICE,
        E_USER_DEPRECATED   => LOG_NOTICE
    );


    /**
     * build in error handler that will redirect all error to build in error logger if error logger is loaded if not
     * will restore previous set error handler
     *
     * @param int $no expects the error level number
     * @param string $str expects the error string
     * @param null|string $file expects the filename where the error was raised
     * @param null|string $line expects the line number where the error was raised
     * @param null|array $context expects the optional error context
     * @return bool
     */
    public static function handler($no, $str, $file = null, $line = null, $context = null)
    {
        if(($logger = setcooki_conf('LOGGER')) !== null)
        {
            $no = (int)$no;
            $str = trim((string)$str);

            $err = array();
            $err[] = "$str, $no";
            $err[] = "in: $file";
            $err[] = "on line: $line";
            $logger->log((array_key_exists($no, self::$map)) ? self::$map[$no] : LOG_ERR, implode(' ', $err));
            return false;
        }else{
            restore_error_handler();
        }
    }
}