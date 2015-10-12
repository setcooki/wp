<?php

namespace Setcooki\Wp;

/**
 * Class Wp
 * @package Setcooki\Wp
 */
abstract class Wp
{
    /**
     * @return mixed
     */
    abstract public function init();


    /**
     * build in autoloader will load setcooki/wp from vendor
     *
     * @param string $class expects the class name to load
     * @return false
     */
    public static function autoload($class)
    {
        $ext = '.php';
        $src = rtrim(realpath(dirname(__FILE__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $class = trim((string)$class, ' \\');

        //setcooki/wp ns
        if(stripos(trim($class, ' \\/'), substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\'))) !== false)
        {
            $file = trim(str_ireplace(__NAMESPACE__, '', $class), ' \\');
            $file = str_replace(array('\\'), DIRECTORY_SEPARATOR, $file);
            require_once $src . $file . $ext;
        //others dirs/ns set with global options
        }else if(setcooki_conf(SETCOOKI_WP_AUTOLOAD_DIRS)){
            foreach((array)setcooki_conf(SETCOOKI_WP_AUTOLOAD_DIRS) as $dir)
            {
                if(is_array($dir))
                {
                    $dir = (array_key_exists(0, $dir)) ? $dir[0] : '';
                    if(array_key_exists(1, $dir))
                    {
                        $class = trim(str_ireplace(trim($dir[1], ' \//'), '', $class), ' \\');
                    }
                }
                $class = str_replace(array('\\'), DIRECTORY_SEPARATOR, $class);
                $file = DIRECTORY_SEPARATOR . trim((string)$dir, ' \\/') . DIRECTORY_SEPARATOR . $class . $ext;
                if(file_exists($file))
                {
                    require_once $file;
                }
            }
        }
        return false;
    }
}