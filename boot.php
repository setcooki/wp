<?php

/**
 * in multi plugin or parent/child theme setup the preferred way of bootstrapping the framework is by including boot.php
 * instead of core.php
 *
 * @since 1.1.4
 */

call_user_func(function()
{
    if(!isset($GLOBALS['SETCOOKI_WP_LOADED']) || $GLOBALS['SETCOOKI_WP_LOADED'] === false)
    {
        $GLOBALS['SETCOOKI_WP_LOADED'] = true;
        require_once dirname(__FILE__) . ((defined('DIRECTORY_SEPARATOR')) ? DIRECTORY_SEPARATOR : '/') . 'core.php';
    }
});