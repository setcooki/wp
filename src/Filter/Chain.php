<?php

namespace Setcooki\Wp\Filter;

use Setcooki\Wp\Exception;

/**
 * Class Chain
 *
 * @package     Setcooki\Wp\Filter
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Chain
{
    /**
     * @var array
     */
    private $_filters = [];

    /**
     * @var array
     */
    private static $_chains = [];


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
     * @return Chain
     */
    public function add(Filter $filter)
    {
        $this->_filters[] = $filter;
        return $this;
    }


    /**
     * reset filter chain
     */
    public function reset()
    {
        $this->_filters = [];
    }


    /**
     * execute the filter chain
     *
     * @since 1.2 removed request parameter
     * @param mixed $params expects variable list of arguments to pass to filter
     * @return mixed
     */
    public function execute(...$params)
    {
        foreach($this->_filters as $filter)
        {
            $params[0] = call_user_func_array([$filter, 'execute'], $params);
        }
        return $params[0];
    }


    /**
     * execute a filter chain by name instantiated with filter chain name. if no filter chain was registered with this
     * name will return filter value in $params
     *
     * @since 1.2 removed request parameter
     * @param mixed $name expects the filter chain name
     * @param mixed $params expects variable list of arguments to pass to filter
     * @return mixed
     */
    public static function e($name, ...$params)
    {
        $name = trim((string)$name);

        if(array_key_exists($name, self::$_chains))
        {
            return call_user_func_array([self::$_chains[$name], 'execute'], $params);
        }else{
            return $params[0];
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