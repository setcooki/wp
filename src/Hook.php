<?php

namespace Setcooki\Wp;

/**
 * Class Hook
 * @package Setcooki\Wp
 */
abstract class Hook
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
     * execute a hook with arguments passed from wp´s hook functions
     *
     * @param array $args expects array of arguments passed from wp´s hook function
     * @param null|mixed $params expects optional parameters
     * @param Request|null $request expects optional request
     * @return mixed
     */
    abstract function execute($args, $params = null, Request $request = null);
}