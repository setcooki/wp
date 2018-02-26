<?php

/**
 * DO NOT OVERRIDE THIS FILE - ONLY VIA SKELETON
 */

define('SETCOOKI_WP_AUTOLOAD', true);
define('SETCOOKI_WP_DEV', true);

require_once dirname(__FILE__) . '/../../../../boot.php';

try
{
    setcooki_boot(array
    (
        'wp' => array
        (
            SETCOOKI_WP_AUTOLOAD_DIRS => array(array(dirname(__FILE__) . '/src', 'Test\\Theme1\\'))
        )
    ));
    if(function_exists('add_action'))
    {
        add_action('init', array(new \Test\Theme1\Theme(setcooki_config('theme.options')), 'init'));
    }
}
catch(Exception $e)
{
    echo (WP_DEBUG_DISPLAY) ? $e->getMessage() : '';
}