<?php

namespace Setcooki\Wp\Filter;

use Setcooki\Wp\Filter;
use Setcooki\Wp\Request;

/**
 * Class Chain
 * @package Setcooki\Wp\Filter
 */
class Chain
{
    /**
     * @var array
     */
    private $_filters = array();

    /**
     * @var array
     */
    private static $_chains = array();


    /**
     * class constructor expects filter name chain to store chain instance for later static execution call
     *
     * @param null|mixed $name expects optional filter chain name
     */
    public function __construct($name = null)
    {
        if(!is_null($name))
        {
            self::$_chains[trim((string)$name)] = &$this;
        }
    }


    /**
     * static class constructor
     *
     * @param null|mixed $name expects optional filter chain name
     * @return Chain
     */
    public static function create($name = null)
    {
        return new self($name);
    }


    /**
     * adds filter to chain
     *
     * @param Filter $filter
     */
    public function add(Filter $filter)
    {
        $this->_filters[] = $filter;
    }


    /**
     * reset filter chain
     */
    public function reset()
    {
        $this->_filters = array();
    }


    /**
     * execute the filter chain
     *
     * @param array $args expects array of arguments passed from wp´s apply_filter function
     * @param null|mixed $params expects optional parameters
     * @param Request|null $request expects optional request
     * @return mixed
     */
    public function execute($args, $params = null, Request $request = null)
    {
        if(is_null($request))
        {
            $request = new Request();
        }
        foreach($this->_filters as $filter)
        {
           $args[0] = $filter->execute($args, $params, $request);
        }
        return $args[0];
    }


    /**
     * execute a filter chain by name instantiated with filter chain name. if no filter chain was registered with this
     * name will return filter value in $args
     *
     * @param mixed $name expects the filter chain name
     * @param array $args expects array of arguments passed from wp´s apply_filter function
     * @param null|mixed $params expects optional parameters
     * @param Request|null $request expects optional request
     * @return mixed
     */
    public static function e($name, $args, $params = null, Request $request = null)
    {
        $name = trim((string)$name);

        if(array_key_exists($name, self::$_chains))
        {
            return self::$_chains[$name]->execute($args, $params, $request);
        }else{
            return $args[0];
        }
    }


    /**
     * on serialize
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_filters');
    }
}