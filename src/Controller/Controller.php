<?php

namespace Setcooki\Wp\Controller;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Wp;
use Setcooki\Wp\Traits\Singleton;
use Setcooki\Wp\Response;
use Setcooki\Wp\Controller\Filter\Unit;

/**
 * Class Controller
 * @package Setcooki\Wp\Controller
 */
abstract class Controller
{
	use Singleton;

	/**
	 * const to be used when setting filter to before execution
	 */
	const BEFORE            = 'before';

	/**
	 * const to be used when setting filter to after execution
	 */
	const AFTER             = 'after';

	/**
	 * contains before filters
	 *
	 * @var array
	 */
	protected $_before = array();

	/**
	 * contains after filter
	 *
	 * @var array
	 */
	protected $_after = array();

	/**
	 * contains controller optional options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * contains theme/plugin instance
	 *
	 * @var null|\WP
	 */
	public $wp = null;


	/**
	 * create a controller instance
	 *
	 * @param Wp $wp expects theme/plugin instance
	 * @param null|mixed $options expects optional options
	 */
	public function __construct(Wp $wp, $options = null)
	{
		$this->wp = $wp;
		setcooki_init_options($options, $this);
	}


	/**
	 * add filters to controller to be executed before controller action execution. see Unit::__construct for more details
	 * how to use controller filters. the filter can be passed as filter object or array that can be converted to filter
	 * object. if you pass an array in first argument expects the filter options to be passed in second argument. if no
	 * argument is passed in first argument returns all previously added filter as this function is a setter/getter method
	 *
	 * @see Unit::__constructor
	 * @param null|Unit\array $filter expects a valid filter value
	 * @param null|array $options expects optional filter options
	 * @return $this|array
	 */
	public function before($filter = null, $options = null)
	{
		if(!is_null($filter))
		{
			$this->_before = array_merge($this->_before, $this->parseFilter($filter, $options));
			return $this;
		}else{
			return $this->_before;
		}
	}


	/**
	 * add filters to controller to be executed after controller action execution. see Unit::__construct for more details
	 * how to use controller filters. the filter can be passed as filter object or array that can be converted to filter
	 * object. if you pass an array in first argument expects the filter options to be passed in second argument. if no
	 * argument is passed in first argument returns all previously added filter as this function is a setter/getter method
	 *
	 * @see Unit::__constructor
	 * @param null|Unit\array $filter expects a valid filter value
	 * @param null|array $options expects optional filter options
	 * @return $this|array
	 */
	public function after($filter = null, $options = null)
	{
		if(!is_null($filter))
		{
			$this->_after = array_merge($this->_after, $this->parseFilter($filter, $options));
			return $this;
		}else{
			return $this->_after;
		}
	}


	/**
	 * attach a filter to controller either to before or after controller action execution depending on first argument
	 *
	 * @param string $type expects "before" or "after" as string
	 * @param null|Unit\array $filter expects a valid filter value
	 * @param null|array $options expects optional filter options
	 * @return mixed
	 * @throws Exception
	 */
	public function attachFilter($type, $filter, $options = null)
	{
		$type = strtolower(trim((string)$type));

		if(in_array($type, array(self::BEFORE, self::AFTER)))
		{
			return $this->{$type}($filter, $options);
		}else{
			throw new Exception(setcooki_sprintf("can not attach filter since first argument: %s is not valid", $type));
		}
	}


	/**
	 * detach a filter previously attached for controller before or after action execution depending on first argument.
	 * the second argument must be the same filter value that has been passed in Controller::attachFilter method. in case
	 * of a closure e.g you must pass the closure stored in a variable. if the filter is not valid will silently fail!
	 *
	 * @param string $type expects "before" or "after" as string
	 * @param null|Unit\array $filter expects a valid filter value
	 * @return $this
	 */
	public function detachFilter($type, $filter)
	{
		$type = strtolower(trim((string)$type));

		if(in_array($type, array(self::BEFORE, self::AFTER)))
		{
			$type = "_" . $type;
			for($i = 0; $i < sizeof($this->$type); $i++)
			{
				try
				{
					if($this->{$type}[$i]->name === (call_user_func(array($this, 'parseFilter'), $filter)[0]->name))
					{
						unset($this->{$type}[$i]);
					}
				}
				catch(Exception $e){}
			}
			$this->{$type} = array_values($this->{$type});
		}
		return $this;
	}


	/**
	 * parse a filter value by passing any passed value in first argument to Unit::__constructor which will create a
	 * valid filter unit or throw exception in case the filter value can not be transformed to filter unit.
	 *
	 * @param Unit\array $filter expects a valid filter value
	 * @param null|array $options expects optional filter unit options
	 *
	 * @return array
	 */
	protected function parseFilter($filter, $options = null)
	{
		if(!is_array($filter))
		{
			$filter = array($filter);
		}
		foreach($filter as &$f)
		{
			if(!($f instanceof Unit))
			{
				$f = new Filter\Unit($f, $options);
			}
		}
		return $filter;
	}


	/**
	 * directly execute a controller action/method without any filters applied!
	 *
	 * @param string $action expects the action/method name
	 * @param null|array $params expects optional params to pass to controller action
	 * @param Response|null $response expects optiona response instance
	 * @return mixed
	 * @throws Exception
	 */
	public function call($action, $params = null, Response $response = null)
	{
		$action = trim((string)$action);

		if(is_null($response))
		{
			$response = new Response();
		}
		if(method_exists($this, $action))
		{
			try
			{
				$method = new \ReflectionMethod($this, $action);
				if($method->isPublic() && !$method->isStatic())
				{
					return call_user_func_array(array($this, $action), array($params, $response));
				}else{
					throw new Exception(setcooki_sprintf("can not call controller action: %s since its not public or static", $action));
				}
			}
			catch(\ReflectionException $e)
			{
				throw new Exception(setcooki_sprintf("can not call controller action: %s", $e->getMessage()));
			}
		}else{
			throw new Exception(setcooki_sprintf("can not call controller action since: %s does not exist", $action));
		}
	}


	/**
	 * do not allow access to none existing methods
	 *
	 * @param string $method expects method name
	 * @param mixed $parameters expects parameters
	 * @throws Exception
	 */
	public function __call($name, $parameters)
	{
		throw new Exception(setcooki_sprintf("method: %s does not exist", $name));
	}


	/**
	 * do not allow access to none existing static methods
	 *
	 * @param string $method expects method name
	 * @param mixed $parameters expects parameters
	 * @throws Exception
	 */
	public function __callStatic($name, $arguments)
	{
		throw new Exception(setcooki_sprintf("method: %s does not exist", $name));
	}
}