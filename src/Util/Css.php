<?php

namespace Setcooki\Wp\Util;

/**
 * Class Css
 * @package Setcooki\Wp
 */
class Css
{
    /**
     * @var array
     */
    public $options = array();


    /**
     * class constructor sets class options
     *
     * @param null|array $options expects optional class options
     */
    public function __construct($options = null)
    {
        setcooki_init_options($options, $this);
    }


    /**
     * static shortcut method to create a class instance
     *
     * @param null|array $options expects optional class options
     * @return Css
     */
    public static function create($options = null)
    {
        return new self($options);
    }


    /**
     * join and minify css files to a single compressed css string which can be either returned by method, send to output
     * stream or saved to file depending on value of third parameter. the first parameter must can be a css string, a
     * relative path to a css file or a absolute path to a css file
     *
     * @param string $css expects a single or array of css files/strings
     * @param null|string $target expects a target value as explained in method signature
     * @return string
     */
    public function minify($css, $target = null)
    {
        ob_start();

        foreach((array)$css as $c)
        {
            if(file_exists($c))
            {
                @include $c;
            }else if(file_exists(rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($c, DIRECTORY_SEPARATOR))){
                @include rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($c, DIRECTORY_SEPARATOR);
            }else{
                echo (string)$css;
            }
        }

        $css = ob_get_clean();

        if(!empty($css))
        {
            $css = $this->compile($css);
        }

        if($target !== null)
        {
            if(is_writeable(dirname($target)))
            {
                file_put_contents($target, $css);
            }
            return $css;
        }else{
            header('Content-type: text/css');
            echo $css;
            exit(0);
        }
    }


    /**
     * compile a css string by optimize, removing comments and obsolete/emtpy identifiers, and compressing the css file
     * by removing all whitespace characters and co
     *
     * @param string $string expects the css string
     * @return string
     */
    public function compile($string)
    {
        $this->optimize($string);
        $this->compress($string);

        return $string;
    }


    /**
     * compress/minify css string
     *
     * @param string $string expects the css string
     * @return string
     */
    protected function compress(&$string)
    {
        //remove complex comments
        $string = preg_replace('=/\*[^*]*\*+([^/][^*]*\*+)*/=i', '', $string);
        //remove single comments
        $string = preg_replace('=^\s*//.*$=im', '', $string);
        //remove spaces, tabs and co
        $string = str_replace(array("\r\n", "\r", "\n", "\t"), '', $string);
        //remove double, triple spaces and co
        $string = preg_replace('=\s{2,}=i', '', $string);
        //trim
        $string = trim($string);

        return $string;
    }


    /**
     * optimize a css string by replacing empty decorations and elements
     *
     * @param string $string expects the css string
     * @return string
     */
    protected function optimize(&$string)
    {
        //replace empty decorations
        $string = preg_replace('=^\s*([a-z0-9\-\_]+)\:\s?\;?\s*$=im', '', $string);

        //replace empty elements
        $string = preg_replace('=^\s*((\.|\#)([a-z0-9\-\_]+)\s*\{\s*\})=im', '', $string);

        return $string;
    }
}