<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Interfaces\Logable;

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
     * custom exception handler logs uncaught exception to build in logger if logger is passed in second argument
     *
     * @param \Exception $e expects an exception
     * @param null|Logable $logger expects optional logger
     * @throws \Exception
     */
    public static function handler(\Exception $e, Logable $logger = null)
    {
        if(!is_null($logger))
        {
            $logger->log(LOG_ERR, $e);
        }
        throw $e;
    }
}