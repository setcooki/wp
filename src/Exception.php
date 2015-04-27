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
     */
    public function __construct($message = "", $code = 0, $severity = 3)
    {
        parent::__construct($message, $code, $severity);
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