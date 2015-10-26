<?php

namespace Setcooki\Wp;

/**
 * Class Filter
 * @package Setcooki\Wp
 */
abstract class Filter
{
    /**
     * @var null
     */
    public $wp = null;


    /**
     * class constructor expects instance of wp and optional options
     *
     * @param Wp $wp expects instance of wp
     * @param null|mixed $options expects optional options
     */
    public function __construct(Wp $wp, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->wp = $wp;
    }


    /**
     * execute a filter with filter arguments passed from wp´s apply_filter function which args[0] is the expected
     * return value
     *
     * @param array $args expects array of arguments passed from wp´s apply_filter function
     * @param null|mixed $params expects optional parameters
     * @param Request|null $request expects optional request
     * @return mixed
     */
    abstract function execute($args, $params = null, Request $request = null);
}