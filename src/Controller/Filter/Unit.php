<?php

namespace Setcooki\Wp\Controller\Filter;

use Setcooki\Wp\Controller\Filter;
use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Exception;
use Setcooki\Wp\Request;
use Setcooki\Wp\Response;

/**
 * Class Unit
 * @package Setcooki\Wp\Controller\Filter
 */
class Unit
{
	/**
	 * contains the filter name
	 *
	 * @var null|string
	 */
	public $name = null;

	/**
	 * contains the filter object
	 *
	 * @var null|\Closure|Filterable
	 */
	public $filter = null;

	/**
	 * contains filter unit options
	 *
	 * @var array|null
	 */
	public $options = null;


	/**
	 * create a new filter unit by passing valid filter expression or object in first argument which can be a closure
	 * or class that implements the Filter::Filterable interface. the second argument can contain filter options which
	 * in array key => value pairs:
	 * - before = bool|string|array tells if the filter should be executed before controller action execution
	 * - after = bool|string|array tells if the filter should be executed after controller action execution
	 * - only = string|array contains controller and/or actions names where this filter only should be executed
	 * - except = string|array contains controller and/or actions names where this filter is not executed
	 * - on = string|array only is executed on the specified server request methods (post, get, ...)
	 * NOTE:
	 * before and after can contain a boolean value to generally tell where to use the filter but also can contain string
	 * or array values of controller and/or action names. the names can be passed in a regex and mysql like fashion
	 * where
	 * - "action" and "%action% will by a complete wildcard match
	 * - "action%" will create a regex like "/^action/i
	 * - "%action" will create a regex like "/action$/i
	 * you can also pass any other valid regex expression
	 *
	 * @param mixed $filter expects the filter object
	 * @param null|array $options expects optional filter unit options
	 * @throws Exception
	 */
	public function __construct($filter, $options = null)
	{
		if($filter instanceof \Closure)
		{
			$name = spl_object_hash($filter);
		}else if(is_object($filter) && $filter instanceof Filterable){
			$name = get_class($filter);
		}else if(is_string($filter) && class_exists($filter) && in_array('Setcooki\Wp\Controller\Filter\Filterable', class_implements($filter))){
			$name = $filter;
			$filter = new $name();
		}else{
			throw new Exception("filter in first argument is not a valid filter value");
		}

		$this->name = $name;
		$this->filter = $filter;
		$this->options = $this->parse($options);
	}


	/**
	 * execute the filter unit if not overriden by executing filter closure or callable
	 *
	 * @param Resolver $resolver expects resolver object
	 * @param Request|null $request expects optional request object
	 * @param Response|null $response expects optiona response object
	 * @param null|array $params expects optional parameters
	 * @return mixed
	 */
	public function execute(Resolver $resolver, Request $request = null, Response $response = null, $params = null)
	{
		if(is_null($request))
		{
			$request = new Request();
		}
		if(is_null($response))
		{
			$response = new Response();
		}
		if(is_object($this->filter) && $this->filter instanceof \Closure)
		{
			$filter = $this->filter;
			return $filter($resolver, $request, $response, (array)$params);
		}else if(is_callable($this->filter)){
			return call_user_func_array($this->filter, array($resolver, $request, $response, (array)$params));
		}else{
			return $this->filter->execute($resolver, $request, $response, $params);
		}
	}


	/**
	 * match controller::action name to filter unit options to determine if filter is executable for the given controller
	 * action or not - see Unit::__constructor for allowed match patterns
	 *
	 * @param string|array $match expects value to match
	 * @return bool
	 */
	public function match($match)
	{
		if(!is_array($match))
		{
			$match = array($match);
		}
		if(!empty($this->options['on']))
		{
			if(!preg_match('=('.implode('|', (array)$this->normalize($this->options['on'])).')=i', trim((string)$_SERVER['REQUEST_METHOD'])))
			{
				return false;
			}
		}
		if(!empty($this->options['only']))
		{
			foreach($match as $m)
			{
				if(!preg_match('=('.implode('|', (array)$this->normalize($this->options['only'])).')=i', trim((string)$m)))
				{
					return false;
				}
			}
		}
		if(!empty($this->options['except']))
		{
			foreach($match as $m)
			{
				if(preg_match('=('.implode('|', (array)$this->normalize($this->options['except'])).')=i', trim((string)$m)))
				{
					return false;
				}
			}
		}
		return true;
	}


	/**
	 * normalize filter expressions by translating mysql style like % placeholder to php regex expressions
	 *
	 * @param string|array $expr
	 * @return array
	 */
	protected function normalize($expr)
	{
		if(!is_array($expr))
		{
			$expr = array($expr);
		}
		foreach($expr as &$e)
		{
			$e = trim((string)$e);
			if($e[0] === '%' && $e[strlen($e)-1] === '%')
			{
				$e = trim(setcooki_regex_delimit($e), ' %');
			}else if($e[0] === '%' && $e[strlen($e)-1] !== '%'){
				$e = trim(setcooki_regex_delimit($e), ' %').'$';
			}else if($e[0] !== '%' && $e[strlen($e)-1] === '%'){
				$e = '^'.trim(setcooki_regex_delimit($e), ' %');
			}
		}
		return $expr;
	}


	/**
	 * parse filter options so a filter object always has normalized filter options to work with
	 *
	 * @param array $options
	 * @return array
	 */
	protected function parse($options)
	{
		$options = (array)$options;
		if(empty($options))
		{
			$options['before'] = true;
		}
		$options = $options + [
			'before'    => false,
			'after'     => false,
			'only'      => array(),
			'except'    => array(),
			'on'        => array()
		];
		foreach($options as $k => &$v)
		{
			if(is_string($v))
			{
				$v = array_unique(preg_split('=\s*\|\s*=i', $v));
			}else if(is_array($v)){
				//do nothing
			}else{
				$v = (bool)$v;
			}
		}
		return $options;
	}


	/**
	 * return filter name on string cast
	 *
	 * @return null|string
	 */
	public function __toString()
	{
		return $this->name;
	}


	/**
	 * convert unit to array
	 *
	 * @return array
	 */
	public function __toArray()
	{
		return array('name' => $this->name, 'filter' => $this->filter, 'options' => $this->options);
	}


	/**
	 * save filter vars on serialize
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return array('name', 'filter', 'options');
	}
}
