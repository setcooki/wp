<?php

namespace Setcooki\Wp;

/**
 * Class Exception
 * @package Setcooki\Wp
 */
class Exception extends \ErrorException
{
    /**
     * passes exception handling back to base class
     *
     * @param string $message the exception message
     * @param int $code the exception code
     * @param int $severity the exception severity
     * @param string $filename expects the file name
     * @param int $lineno expects the line number
     * @param null|Exception $previous exception if set
     */
    public function __construct($message = "", $code = 0, $severity = 3, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }


    /**
     * shortcut function to throw an exception
     *
     * @param \Exception $e expects an exception
     * @throws \Exception
     */
    public static function t(\Exception $e)
    {
        throw $e;
    }


    /**
     * custom exception handler logs uncaught exception to build in logger if logger is instantiated
     *
     * @param \Exception $e expects an exception
     * @throws \Exception
     */
    public static function handler(\Exception $e)
    {
        if(class_exists('Setcooki\\Wp\\Logger', true) && Logger::hasInstance())
        {
            Logger::l($e);
        }else{
            throw $e;
        }
    }
}