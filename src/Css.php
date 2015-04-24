<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

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
     * @param null $options
     */
    public function __construct($options = null)
    {
        setcooki_init_options($options, $this);
    }


    /**
     * @param null $options
     * @return Css
     */
    public static function create($options = null)
    {
        return new self($options);
    }


    /**
     * @param $css
     * @param null $target
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
     * @param $string
     * @return mixed
     */
    public function compile($string)
    {
        $this->optimize($string);
        $this->compress($string);

        return $string;
    }


    /**
     * @param $string
     * @return mixed|string
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
     * @param $string
     * @return mixed
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