<?php

namespace Setcooki\Wp\Controller;

use Setcooki\Wp\Wp;
use Setcooki\Wp\Exception;
use Setcooki\Wp\Request;
use Setcooki\Wp\Response;
use Setcooki\Wp\Template;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Routing\Router;

use Setcooki\Wp\Controller\Filter\Unit;
use Setcooki\Wp\Controller\View\View;

/**
 * Class Resolver
 * @package Setcooki\Wp\Controller
 */
class Resolver
{
	/**
	 * const to be used when setting filter to before execution
	 */
	const BEFORE            = 'before';

	/**
	 * const to be used when setting filter to after execution
	 */
	const AFTER             = 'after';

	/**
	 * class option request
	 */
	const REQUEST           = 'REQUEST';

	/**
	 * class option response
	 */
	const RESPONSE          = 'RESPONSE';


	/**
	 * contains all registered controller instances
	 *
	 * @var array
	 */
	private $_controllers = array();

	/**
	 * contains all attached global filters
	 *
	 * @var array
	 */
	protected $_filters = array();

	/**
	 * contain the controller:action map
	 *
	 * @var array
	 */
	protected $_map = array();

	/**
	 * can contain the controller actions return buffered
	 *
	 * @var null|string
	 */
	public $buffer = null;

	/**
	 * contains response instance
	 *
	 * @var null|Response
	 */
	public $response = null;

	/**
	 * contains the request instance
	 *
	 * @var null|Request
	 */
	public $request = null;

	/**
	 * contains theme/plugin instance
	 *
	 * @var null|WP
	 */
	public $wp = null;

	/**
	 * contains optional class options
	 *
	 * @var array
	 */
	public $options = array();


	/**
	 * resolver constructor expects instance of theme or plugin an optional options
	 *
	 * @param Wp $wp expects wp theme/plugin instance
	 * @param null|mixed $options expects optional options
	 */
	public function __construct(Wp $wp, $options = null)
	{
		$this->wp = $wp;

		setcooki_init_options($options, $this);
		if(setcooki_has_option(self::REQUEST, $this))
		{
			$this->request(setcooki_get_option(self::REQUEST, $this));
		}
		if(setcooki_has_option(self::RESPONSE, $this))
		{
			$this->response(setcooki_get_option(self::RESPONSE, $this));
		}

		$this->wp->store('resolver', $this);
	}


	/**
	 * create router instance statically
	 *
	 * @param Wp $wp expects wp theme/plugin instance
	 * @param null|mixed $options expects optional options
	 * @return Resolver
	 */
	public function create(Wp $wp, $options = null)
	{
		return new self($wp, $options);
	}


	/**
	 * request instance setter/getter method
	 *
	 * @param Request|null $request expects request instance in setter mode
	 * @return null|Request
	 */
	public function request(Request $request = null)
	{
		if(!is_null($request))
		{
			$this->request = $request;
		}
		return $this->request;
	}


	/**
	 * response instance setter/getter method
	 *
	 * @param Response|null $response expects response instance in setter mode
	 * @return null|Response
	 */
	public function response(Response $response = null)
	{
		if(!is_null($response))
		{
			$this->response = $response;
		}
		return $this->response;
	}


	/**
	 * setter/getter method. in setter mode needs to be called prior to handle methods and will redirect return to buffer
	 * to get get buffer call method again after handle method
	 *
	 * @param null|string $buffer expects buffer value
	 * @return null|string
	 */
	public function buffer($buffer = null)
	{
		if(is_null($this->buffer))
		{
			$this->buffer = (string)$buffer;
		}
		return (string)$this->buffer;
	}


	/**
	 * TODO: exclude all magic methods that start with "__"
	 *
	 * register controller actions = methods by passing the controller instance as object or string in first argument.
	 * all public and non-static methods that are not abstract, constructor and destructor are considered to be public
	 * accessible actions. all found actions are available by string key once registered. the syntax is "{$controller}::{$action}
	 * where $controller is the class name and $action is the method name. the second argument is expected to be a options
	 * array that can contain exclude rules, before and after filters
	 *
	 * @param Controller|string $controller expects controller class instance or string name
	 * @param null|array $options expects optional options
	 * @return $this
	 * @throws Exception
	 */
	public function register($controller, $options = null)
	{
		if(is_string($controller) && class_exists($controller))
		{
			$controller = new $controller();
		}
		$options = new Params($options);
		if(is_object($controller) && $controller instanceof Controller)
		{
			try
			{
				$reflection = new \ReflectionClass($controller);
				foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
				{
					if(
						$method->getDeclaringClass()->getName() === get_class($controller)
						&&
						!$method->isConstructor()
						&&
					    !$method->isDestructor()
						&&
						!$method->isStatic()
						&&
					    !$method->isAbstract()
					)
					{
						if($options->is('excludes'))
						{
							foreach((array)$options->get('excludes') as $e)
							{
								if(preg_match('='.setcooki_regex_delimit(trim($e)).'=i', $method->getName()))
								{
									break 2;
								}
							}
						}
						$key = str_replace(array('\\', NAMESPACE_SEPARATOR), array('.'), strtolower(get_class($controller)));
						$ref = setcooki_sprintf("%s::%s", array($key, strtolower($method->getName())));
						$this->_map[$ref] = array($key, $method->getName());
						$this->_controllers[$key] = $controller;
					}
				}
				if($options->is('before'))
				{
					foreach((array)$options->get('before') as $before)
					{
						$controller->before($before[0], ((array_key_exists(1, $before)) ? $before[0] : null));
					}
				}
				if($options->is('after'))
				{
					foreach((array)$options->get('after') as $after)
					{
						$controller->after($after[0], ((array_key_exists(1, $after)) ? $after[0] : null));
					}
				}
			}
			catch(\ReflectionException $e)
			{
				throw new Exception(setcooki_sprintf("unable to capture controller methods due to reflection error: %s", $e->getMessage()));
			}
			return $this;
		}else{
			throw new Exception("passed controller object/string is not a instance of controller");
		}
	}


	/**
	 * TODO: allow unregister at method level also
	 *
	 * unregister a controller previously registered with Resolver::register. pass the controller class name or instance
	 * in first argument. if method is called without any argument will remove all controllers
	 *
	 * @param null|string|Controller $controller expects optional controller name/instance
	 * @return $this
	 */
	public function unregister($controller = null)
	{
		if(!is_null($controller))
		{
			if(is_object($controller))
			{
				$controller = get_class($controller);
			}
			$key = self::normalize($controller);
			if(array_key_exists($key, $this->_controllers))
			{
				unset($this->_controllers[$key]);
				foreach($this->_map as $k => $v)
				{
					if($v[0] === $key)
					{
						unset($this->_map[$k]);
					}
				}
			}
		}else{
			$this->_controllers = array();
			$this->_map = array();
		}
		return $this;
	}


	/**
	 * check if a controller or controller.method is already registered which will return boolean value (true|false). if
	 * checking for controller method use the appropriate syntax: "{$controller}::{$action}". the controller in first
	 * argument can be a controller class name as string or class instance as object
	 *
	 * @param string|Controller $controller expects controller class name as string or class instance
	 * @param null|string $method expects options method/action name
	 * @return bool
	 */
	public function registered($controller, $method = null)
	{
		if(is_object($controller))
		{
			$controller = get_class($controller);
		}
		$key = self::normalize($controller);
		if(stripos($controller, '::') !== false)
		{
			return (array_key_exists($key, $this->_map)) ? true : false;
		}else{
			if(!is_null($method))
			{
				return (array_key_exists(setcooki_sprintf("%s::%s", array($key, strtolower((string)$method))), $this->_map)) ? true : false;
			}else{
				return (array_key_exists($key, $this->_controllers)) ? true : false;
			}
		}
	}


	/**
	 * attach filter objects to controller or controller actions or even too multiple controllers depending on filter options
	 * which if empty = null will attach a filter that will be called on each controller.action on before. see filter
	 * unit for more details on valid and accepted options. the first argument can be a filter class as string, filter
	 * object or closure. if the filter object in first argument is not recognized will throw exception
	 *
	 * @see \Setcooki\Wp\Controller\Filter\Unit::__construct
	 * @param string|Unit|\Closure $filter expects a filter object
	 * @param null|array $options expects optional filter options
	 * @throws Exception
	 */
	public function attachFilter($filter, $options = null)
	{
		if(!is_array($filter))
		{
			$filter = array($filter);
		}
		foreach($filter as &$f)
		{
			if(!($f instanceof Unit))
			{
				$f = new Unit($f, $options);
			}
		}
		$this->_filters = array_values(array_merge($this->_filters, $filter));
	}


	/**
	 * detach a filter previously attached by passing the filter class name as string, a filter object or a closure
	 *
	 * @param string|Unit|\Closure $filter expects a filter object
	 * @return $this
	 */
	public function detachFilter($filter)
	{
		try
		{
			$filter = new Unit($filter);
			$i = 0;
			foreach($this->_filters as $f)
			{
				if($f->name === $filter->name)
				{
					unset($this->_filters[$i]);
				}
			}
			$this->_filters = array_values($this->_filters);
			unset($filter);
		}
		catch(\Exception $e){}
		return $this;
	}


	/**
	 * get filters by type which can be "before" or "after" in first argument returning all before or after filter or if
	 * no value is passed will return all attached filters
	 *
	 * @param null|string $type expects optional filter type
	 * @return array
	 */
	public function getFilter($type = null)
	{
		$tmp = array();

		if(!is_null($type) && in_array(strtolower((string)$type), array(self::BEFORE, self::AFTER)))
		{
			foreach($this->_filters as $filter)
			{
				if(!empty($filter->options[strtolower((string)$type)]))
				{
					$tmp[] = $filter;
				}
			}
			return $tmp;
		}else{
			return $this->_filters;
		}
	}


	/**
	 * handle an action passed in first argument. the resolver is designed to execute the controller actions previously
	 * registered - see Resolver::register. its also possible to pass other action like:
	 * - instance of Router which will run the router
	 * - instance of Closure which will execute the closure
	 * - a callable passed as array
	 * - action(s) as string or array that previously have been registered
	 * NOTE: multiple actions can be passed which then will be executed in loop passing the response from action call
	 * to action call. in this case the actions return will be added to the response and the response will be return
	 * NOTE: if first argument is empty will run all registered action!
	 * a optional fallback can be passed also in case action is not valid or not registered - see Router::fail for more
	 * info of allowed fallback values. if no fallback has been defined will throw exception. a callback can also be
	 * passed optional which will pipe the action result to the callback. you can also pass optional string buffer.
	 * NOTE: instead of using the buffer via the handle method use the global buffer method which will add actions return
	 * in buffer and return nothing
	 *
	 * @param null|mixed $action expects optional allowed action
	 * @param null|object|array|Params $params expects optional params
	 * @param Request|null $request expects optional request instance
	 * @param Response|null $response expects optional response instance
	 * @param null|mixed $fallback expects optional fallback - see Router::fail
	 * @param null|mixed $callback expects optional callback - see Resolver::callback
	 * @param null|string $buffer expects optional string buffer
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle($action = null, $params = null, Request $request = null, Response $response = null, $fallback = null, $callback = null, &$buffer = null)
	{
		$return = null;
		$actions = array();

		if(is_null($params))
		{
			$params = new Params();
		}else{
			$params = new Params((array)$params);
		}
		if(is_null($response))
		{
			$response = ($this->response() !== null) ? $this->response() : new Response();
		}
		if(is_null($request))
		{
			$request = ($this->request() !== null) ? $this->request() : new Request();
		}
		try
		{
			if(is_object($action) && $action instanceof Router)
			{
				$return = $action->bind('action', array($this, 'handle'))->run($fallback, $request);
			}else if(is_object($action) && $action instanceof \Closure){
				$return = $action($params, $request, $response);
			}else if(is_array($action) && is_callable($action)){
				$return = $this->callback($action, array($params, $request, $response));
			}else{
				if(!is_null($action))
				{
					foreach((array)$action as $a)
					{
						$actions = array_merge($actions, $this->lookup($a));
					}
					$actions = array_values($actions);
				}else{
					$actions = array_values($this->_map);
				}
				if(!empty($actions))
				{
					if(sizeof($actions) === 1)
					{
						$return = $this->execute($actions[0][0], $actions[0][1], $params, $request, $response);
					}else{
						foreach($actions as $a)
						{
							$return = $this->execute($a[0], $a[1], $params, $request, $response);
							if($return instanceof Response)
							{
								$response &= $return;
							}else{
								$response->add(null, $return);
							}
						}
						return $return;
					}
				}else{
					throw new Exception("no action found to handle");
				}
			}
		}
		catch(\Exception $e)
		{
			if(!is_null($fallback))
			{
				return Router::fail($fallback);
			}else{
				throw $e;
			}
		}
		if(!is_null($callback))
		{
			$return = $this->callback($callback, array($return, $this));
		}
		if(!is_null($buffer) && is_string($buffer))
		{
			$buffer .= (string)$return;
		}
		if(!is_null($this->buffer))
		{
			$this->buffer .= (string)$return;
			return null;
		}
		if($return instanceof Response)
		{
			return $return->flush();
		}else{
			return (string)$return;
		}
	}


	/**
	 * execute a controller action running pre/post filters registered with resolver or controller
	 *
	 * @param string $controller expects the controller path/name
	 * @param string $action expects the action/method name
	 * @param null|mixed $params expects optional parameters
	 * @param Request $request expects the request instance
	 * @param Response $response expects the response instance
	 * @return mixed
	 * @throws \Exception
	 */
	protected function execute($controller, $action, $params = null, $request, $response)
	{
		if(array_key_exists($controller, $this->_controllers))
		{
			foreach(array_merge($this->getFilter('before'), $this->_controllers[$controller]->before()) as $filter)
			{
				if($filter->match("$controller::$action"))
				{
					$filter->execute($this, $request, $response, $params);
				}
			}

			$return = $this->resolve($this->callback(array($this->_controllers[$controller], $action), array($params, $request, $response)));

			foreach(array_merge($this->getFilter('after'), $this->_controllers[$controller]->after()) as $filter)
			{
				if($filter->match("$controller::$action"))
				{
					$filter->execute($this, $request, $response, $params);
				}
			}
			return $return;
		}else{
			throw new Exception(setcooki_sprintf("controller: %s is not registered", $controller));
		}
	}


	/**
	 * resolve the return of called controller action which usually would return NULL since action should echo out the
	 * action result. however in cases, e.g. ajax controllers, the action result not necessarily needs to be echoed out.
	 * a controller action can return the following values:
	 * - instance of Exception will throw exception an break handling
	 * - instance of View will render the view and return the view as string
	 * - instance of Template will render the template and return template as string
	 * - instance of Response will return the response for further handling
	 * - instance of Closure will execute the closure and stringify and return the result!
	 * - boolean false or null will return NULL
	 * any other value will throw exception since its not valid
	 *
	 * @param mixed $return expects the return value from controller action
	 * @return null|string
	 * @throws \Exception
	 */
	protected function resolve($return)
	{
		if($return instanceof \Exception)
		{
			throw $return;
		}else if($return instanceof View){
			return (string)$return;
		}else if($return instanceof Template){
			return (string)$return;
		}else if($return instanceof Response){
			return $return;
		}else if($return instanceof \Closure){
			return (string)$return();
		}else if($return === false || $return === null){
			return null;
		}else if(setcooki_stringable($return)){
			return (string)$return;
		}else{
			throw new Exception("controller action returns non interpretable value");
		}
	}


	/**
	 * execute a callable
	 *
	 * @param callable $callable expects the callable
	 * @param array $params expects optional parameters
	 * @return mixed
	 */
	protected function callback(callable $callable, Array $params = [])
	{
		return call_user_func_array($callable, $params);
	}


	/**
	 * tries to lookup the right controller action(s) according to value uses which can be a precise controller value as
	 * stored in class controller action or a regex or mysql like style regex expression. its also allowed not to use
	 * the complete controller namespace path but also using just the class name. e.g. if a controller with the namespace
	 * \Foo\Base\Class is passed its sufficient to only use "Class::action". NOTE: if however there are multiple class
	 * names with the same name a ambiguous exception is thrown
	 *
	 * @param string $action expects the action to lookup
	 * @return array
	 * @throws Exception
	 */
	protected function lookup($action)
	{
		$return = array();
		$action = self::normalize($action);

		if(stripos($action, '%') !== false)
		{
			if($action[0] === '%' && $action[strlen($action)-1] === '%')
			{
				$action = trim(setcooki_regex_delimit($action), ' %');
			}else if($action[0] === '%' && $action[strlen($action)-1] !== '%'){
				$action = trim(setcooki_regex_delimit($action), ' %').'$';
			}else if($action[0] !== '%' && $action[strlen($action)-1] === '%'){
				$action = '^'.trim(setcooki_regex_delimit($action), ' %');
			}
			$matches = function($a) use ($action)
			{
				return (bool)preg_match("@$action@i", $a);
			};
		}else{
			//fix * wildcard right
			$action = rtrim($action, ' .:*');
			//fix * wildcard left
			if(preg_match('=^\*(?:\.|\\\)?(.*)$=i', $action, $m))
			{
				$action = '.*' . trim($m[1], ' .*');
			}
			$matches = function($a) use ($action)
			{
				if(stripos($action, '::') !== false)
				{
					return (bool)preg_match("@$action$@i", $a);
				}else{
					return (bool)preg_match("@$action\:\:.*$@i", $a);
				}
			};
		}

		foreach($this->_map as $k => $v)
		{
			if($action === $k || $matches($k))
			{
				$return[] = $v;
			}
		}
		$return = array_filter(array_unique($return));
		if(stripos($action, '::') !== false && sizeof($return) > 1)
		{
			throw new Exception(setcooki_sprintf("action: %s is ambiguous and not resolvable", $action));
		}
		return $return;
	}


	/**
	 * flush the resolver which will execute all actions and return result as string. NOTE: this method should only
	 * be used for debugging purposes
	 *
	 * @param null|object|array|Params $params expects optional params
	 * @throws \Exception
	 */
	public function flush($params = null)
	{
		echo (string)$this->handle(null, $params);
		exit(0);
	}


	/**
	 * reset the resolver
	 *
	 * @return $this
	 */
	public function reset()
	{
		$this->_controllers = array();
		$this->_filters = array();
		$this->_map = array();
		$this->buffer = null;
		return $this;
	}


	/**
	 * normailze and clean a action name
	 *
	 * @param string $action
	 * @return string
	 */
	public static function normalize($action)
	{
		$action = (string)$action;
		$action = trim($action, ' \\\\');
		$action = trim($action, ' \\');
		$action = trim($action, NAMESPACE_SEPARATOR);
		$action = str_replace(array('\\\\', '\\', NAMESPACE_SEPARATOR), '.', strtolower($action));
		return $action;
	}


	/**
	 * prevent overloading
	 *
	 * @param string $name expects method name
	 * @param array $arguments contains passed arguments
	 * @throws Exception
	 */
	public function __call($name, $arguments)
	{
		throw new Exception("overloading methods not allowed - use handle method instead");
	}


	/**
	 * prevent cloning
	 *
	 * @throws Exception
	 */
	public function __clone()
	{
		throw new Exception("cloning not allowed since there should be only one resolver");
	}


	/**
	 * on destruct reset
	 */
	public function __destruct()
	{
		$this->reset();
	}
}