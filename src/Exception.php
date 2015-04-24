<?php

namespace Setcooki\Wp;

/**
 * Class Exception
 * @package Setcooki\Wp
 */
class Exception extends \ErrorException
{
    /**
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     * @param null $previous
     */
    public function __construct($message = "", $code = 0, $severity = 3, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }


    /**
     * @param $exception
     * @throws
     */
    public static function handler($exception)
    {
        if(class_exists('Setcooki\\Wp\\Logger', true) && Logger::hasInstance())
        {
            Logger::l($exception);
        }else{
            throw $exception;
        }
    }
}