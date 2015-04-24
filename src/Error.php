<?php

namespace Setcooki\Wp;

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
     * @param $no
     * @param $str
     * @param null $file
     * @param null $line
     * @param null $context
     * @return bool
     */
    public static function handler($no, $str, $file = null, $line = null, $context = null)
    {
        if(class_exists('Setcooki\\Wp\\Logger', true) && Logger::hasInstance())
        {
            $no = (int)$no;
            $str = trim((string)$str);

            $err = array();
            $err[] = "$str, $no";
            $err[] = "in: $file";
            $err[] = "on line: $line";
            Logger::l(implode(' ', $err), (array_key_exists($no, self::$map)) ? self::$map[$no] : LOG_ERR);
        }else{
            return false;
        }
    }
}