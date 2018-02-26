<?php
/*
Plugin Name: Plugin1
Description: wp dev plugin (plugin1)
Version: 0.0.0
Author: setcookie <set@cooki.me>
Author URI: http://set.cooki.me
*/

define('SETCOOKI_WP_AUTOLOAD', true);
require_once dirname(__FILE__) . '/../../../../core.php';

try
{
    setcooki_boot(array
    (
        'wp' => array
        (
            SETCOOKI_WP_AUTOLOAD_DIRS => array(array(dirname(__FILE__) . '/src', 'Test\\Plugin1\\'))
        )
    ));
    if(function_exists('add_action'))
    {
        add_action('init', array(new \Test\Plugin1\Plugin(setcooki_config('plugin.options')), 'init'));
    }
}
catch(Exception $e)
{
    echo (WP_DEBUG_DISPLAY) ? $e->getMessage() : '';
}