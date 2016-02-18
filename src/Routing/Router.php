<?php

namespace Setcooki\Wp\Routing;

use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Exception;
use Setcooki\Wp\Request;
use Setcooki\Wp\Wp;

/**
 * Class Router
 * @package Setcooki\Wp\Routing
 */
class Router
{
	/**
	 * contains all route object associated with this router
	 *
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * contains the bindings which are callbacks attached to route types
	 *
	 * @var array
	 */
	protected $_bindings = array();

	/**
	 * contains wp theme/plugin base class instance
	 *
	 * @var null|\WP
	 */
	public $wp = null;

	/**
	 * contains class options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * contains optional request object
	 *
	 * @var null|Request
	 */
	public $request = null;

	/**
	 * contains optional resolver instance
	 *
	 * @var null|Resolver
	 */
	public $resolver = null;

	/**
	 * contains fallback action which can be url, callable, event, route
	 *
	 * @var null|mixed
	 */
	public $fallback = null;


	/**
	 * class constructor sets wp theme/plugin base class instance and optional options
	 *
	 * @param Wp $wp expects wp theme/plugin instance
	 * @param null|mixed $options expects optional options
	 */
	public function __construct(Wp $wp, $options = null)
	{
		$this->wp = $wp;
		setcooki_init_options($options, $this);
		$this->wp->store('router', $this);
	}


	/**
	 * create router instance by calling static create method
	 *
	 * @see Router::__constructor
	 * @param Wp $wp expects wp theme/plugin instance
	 * @param null|mixed $options expects optional options
	 * @return Router
	 */
	public static function create(Wp $wp, $options = null)
	{
		return new self($wp, $options);
	}


	/**
	 * fallback setter/getter method. set a fallback which can be an callable, url for redirect, exception or a route
	 * object. if no route matches the fallback will be fired
	 *
	 * @param null|mixed $fallback expects the fallback value in setter mode
	 * @return null|mixed
	 */
	public function fallback($fallback = null)
	{
		if(!is_null($fallback))
		{
			$this->fallback = $fallback;
		}
		return $this->fallback;
	}


	/**
	 * request setter/getter method. the request is passed to route target and also needed in custom route objects in
	 * before/after route execution
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
	 * bind a callable to route type and let the callable handle the execution of the route target
	 *
	 * @param string $type expects supported route type value
	 * @param callable $callable expects a accessible callable
	 * @return $this
	 */
	public function bind($type, callable $callable)
	{
		$type = strtolower(trim((string)$type));
		$this->_bindings[$type] = $callable;
		return $this;
	}


	/**
	 * unbind a callable previously attached to route type
	 *
	 * @param string $type expects supported route type value
	 * @return $this
	 */
	public function unbind($type)
	{
		$type = strtolower(trim((string)$type));
		if(array_key_exists($type, $this->_bindings))
		{
			unset($this->_bindings[$type]);
			$this->_bindings = array_values($this->_bindings);
		}
		return $this;
	}


	/**
	 * set route(s) single or in batch resetting or removing all previously set routes. see Router::add for more details
	 * of expected arguments and ways to pass routes
	 *
	 * @see Router::add
	 * @param string|array|Route $route expects the route value
	 * @param null|string $target expects the routes target value
	 * @param null|array $params expects optional params as array
	 * @return $this
	 * @throws Exception
	 */
	public function set($route, $target = null, $params = null)
	{
		$this->reset();
		return $this->add($route, $target, $params);
	}


	/**
	 * add routes by passing route objects or route values which will be used to create new route objects. see Route::__construct
	 * for more details which values are allowed and valid. routes can be passed the following ways:
	 * - a route object in first argument and all others = null
	 * - a route value, target value and optional parameters passed in all three arguments
	 * - an array of route objects in first argument an all other = null
	 * - an array with numeric arrays which contain route value, target value and optional params
	 * - an array with assoc arrays which contain route value, target value and optional params by name
	 *
	 * @see Route::__construct
	 * @param string|array|Route $route expects the route value
	 * @param null|string $target expects the routes target value
	 * @param null|array $params expects optional params as array
	 * @return $this
	 * @throws Exception
	 */
	public function add($route, $target = null, $params = null)
	{
		if($route instanceof Route)
        {
            array_push($this->_routes, $route);
        }else if(is_array($route)){
			$i = 0;
            foreach($route as $r)
            {
	            if($r instanceof Route)
	            {
		            $this->add($r, null, null);
	            }else if(is_array($r) && array_keys($r) === range(0, count($r) - 1) && sizeof($r) >= 2){
		            $this->add($r[0], $r[1], ((array_key_exists(2, $r)) ? $r[2] : null));
	            }else if(is_array($r) && array_key_exists('route', $r) && array_key_exists('target', $r)){
					$this->add($r['route'], $r['target'], ((array_key_exists('params', $r)) ? $r['params'] : null));
	            }else{
		            throw new Exception(setcooki_sprintf('route passed at index: %d is not a valid route object', $i));
	            }
	            $i++;
            }
        }else{
			array_push($this->_routes, new Route($route, $target, $params));
		}
		return $this;
	}


	/**
	 * add routes from file which is a php file which must return array with valid routes objects/values as expected by
	 * Router::add method. if the file does not exist will either throw exception or return false according to second
	 * argument boolean value
	 *
	 * @param string $file expects routes config file absolute file location
	 * @param bool $throw expects boolean value for whether to throw exception or return false on failure
	 * @return bool|Router
	 * @throws Exception
	 */
	public function addFrom($file, $throw = true)
	{
		if(is_file($file))
		{
			$file = require $file;
            if($file === 1)
            {
                $file = array_slice(get_defined_vars(), 1);
            }
			return $this->set($file);
		}else{
			if((bool)$throw)
			{
				throw new Exception(setcooki_sprintf("routes config file under: %s does not exist", $file));
			}else{
				return false;
			}
		}
	}


	/**
	 * auto add routes config file by looking for router.php file in themes or plugins base path, there where styles.css
	 * , is located. the router.php must return array with valid routes objects/values as expected by Router::add method.
	 * if the file does not exist will either throw exception or return false according to second argument boolean value
	 *
	 * @param bool $throw expects boolean value for whether to throw exception or return false on failure
	 * @return bool|Router
	 * @throws Exception
	 */
	public function autoAdd($throw = false)
	{
		return $this->addFrom($this->wp->base() . DIRECTORY_SEPARATOR . 'router.php', $throw);
	}


	/**
	 * get all added routes if first argument is empty or get routes by regex expression matching the routes route value
	 * . e.g. if all routes of type url must be returned pass first argument as 'url:'. see Route::__constructor for
	 * all available route types
	 *
	 * @see Route::__constructor
	 * @param null|string $route expects regex expression to get routes by route value
	 * @return array
	 */
	public function get($route = null)
	{
		$tmp = array();

		if(!is_null($route))
		{
			foreach($this->_routes as $r)
			{
				if(preg_match('@^'.setcooki_regex_delimit((string)$route, ' ^').'@i', $r->route->route))
				{
					$tmp[] = $r;
				}
			}
			return $tmp;
		}else{
			return $this->_routes;
		}
	}


	/**
	 * get routes by a combination of available route properties and values. e.g. route or target type values. the first
	 * argument expects either "route" or "target". the second the type which depends on the first argument an can be looked
	 * up in Route::__constructor
	 *
	 * @see Route::__constructor
	 * @param string $what expects "route" or "target"
	 * @param string $with expects the type value to match - see Route::__constructor
	 * @return array
	 * @throws Exception
	 */
	public function getBy($what, $with)
	{
		$tmp = array();

		$what = strtolower(trim((string)$what));
		if(property_exists('\Setcooki\Wp\Controller\Route', $what))
		{
			foreach($this->_routes as $route)
			{
				if($route->{$what}->type === strtolower((string)$with))
				{
					$tmp[] = $route;
				}
			}
			return $tmp;
		}else{
			throw new Exception(setcooki_sprintf("can not get route by unknown property: %s", $what));
		}
	}


	/**
	 * check if instance has any routes set if first argument is not set or or check if any routes by route value passed
	 * in first argument are set. see Router::get for what to pass in first argument
	 *
	 * @see Router::get
	 * @param null|string $route expects optional route value to check
	 * @return bool
	 */
	public function has($route = null)
	{
		if(!is_null($route))
		{
			return (sizeof($this->get($route)) > 0) ? true : false;
		}else{
			return (sizeof($this->_routes) > 0) ? true : false;
		}
	}


	/**
	 * remove all routes if first argument is not set or remove all routes by route value as explained in Router::get
	 *
	 * @see Router::get
	 * @param null|string $route expects optional route value to check
	 * @return $this
	 */
	public function remove($route = null)
	{
		$tmp = array();

		if(!is_null($route))
		{
			foreach($this->get($route) as $route)
			{
				foreach($this->_routes as $r)
				{
					if($r->route->route !== $route->route->route)
					{
						$tmp[] = $r;
					}
				}
			}
			$this->_routes = array_values($tmp);
		}else{
			$this->reset();
		}
		return $this;
	}


	/**
	 * remove all routes by combination of route property which can be "route" or "target and type value. see Router::getBy
	 *
	 * @see Router::getBy
	 * @param string $what expects "route" or "target"
	 * @param string $with expects the type value to match - see Route::__constructor
	 * @return $this
	 * @throws Exception
	 */
	public function removeBy($what, $with)
	{
		$tmp = array();

		$what = strtolower(trim((string)$what));
		foreach($this->getBy($what, $with) as $route)
		{
			foreach($this->_routes as $r)
			{
				if($r->{$what}->type !== $route->{$what}->type)
				{
					$tmp[] = $r;
				}
			}
		}
		$this->_routes = array_values($tmp);
		return $this;
	}


	/**
	 * reset = remove all routes previously added to class instance
	 *
	 * @return $this
	 */
	public function reset()
	{
		$this->_routes = array();
		return $this;
	}


	/**
	 * run the router by trying to find a route that matches and execute the found route or executing optional fallback
	 * if no route was found. the rule on how the router class finds a match is straight forward - the first route of added
	 * routes that matches is executed so the priority is defined by the order on how routes are added. if more then one
	 * route matches the first in stack will be fired! the route value is a string value that needs to specify route type
	 * and route matching string. the syntax is: "{$identifier}:{$match}" where $identifier can be of the following:
	 * - url = match against servers request request uri value
	 * - tpl|template = match against wordpress custom page template value
	 * - post = match against a post value
	 * - get = match against a get value
	 * - session = match against a session value
	 * $match must be a regex valid match string so that "url:/foo.*" e.g will match an url that contains "/foo". the
	 * fallback passed in first argument can be:
	 * - callable
	 * - closure
	 * - exception
	 * - route object
	 * - url for redirect
	 *
	 * @see Route::__construct
	 * @param null|mixed $fallback expects a recognizable fallback value
	 * @param Request|null $request expects a optional request object instance
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function run($fallback = null, Request $request = null)
	{
		if(is_null($fallback))
		{
			$fallback = $this->fallback();
		}
		if(is_null($request))
		{
			$request = ($this->request() !== null) ? $this->request() : new Request();
		}
		foreach($this->_routes as $route)
		{
			$i = 0;
			foreach($route->route as $r)
			{
				if(method_exists('match', $route))
				{
					$i += (int)$route->match();
				}else{
					$i += (int)$this->match($r);
				}
			}
			if($i === sizeof($route->route))
			{
				return $this->execute($route, $request);
			}
		}
		if(!is_null($fallback))
		{
			return self::fail($fallback);
		}
		return false;
	}


	/**
	 * execute a route passed as route object in first argument or passing a integer value = index of routes previousl
	 * added to class instance or passing a route value string to let Router::get search for a valid route.before executing
	 * route will call before and after route execution handler. a route object can contain custom execution logic by
	 * simply overriding the Route::execute method
	 *
	 * @see Router::get
	 * @param mixed $route expects recognizable route value
	 * @param Request|null $request expects optional request instance
	 * @return mixed
	 * @throws Exception
	 */
	public function execute($route, Request $request = null)
	{
		if($route instanceof Route)
		{
			//do nothing
		}else if(ctype_digit($route) && array_key_exists((int)$route, $this->_routes)){
			$route = $this->_routes[(int)$route];
		}else{
			$route = $this->get($route);
			$route = (array_key_exists(0, $route)) ? $route[0] : null;
		}
		if(is_null($request))
		{
			$request = $this->request();
		}
		if(is_null($request))
		{
			$request = new Request();
		}
		if(!empty($route))
		{
			$type = strtolower((string)$route->target->type);
			if(array_key_exists($type, $this->_bindings))
			{
				return call_user_func_array($this->_bindings[$type], array($route->target->target, $route->params, $request));
			}else{
				switch($type)
				{
					case 'route':
						return $this->redirectToRoute($route->target->target, $route->params);
					default:
						$route->beforeExecute($request);
						if(method_exists($route, 'execute'))
						{
							$return = $route->execute($request);
						}else{
							$return = self::execute($route, $request);
						}
						$route->afterExecute($request);
						return $return;
				}
			}
		}else{
			throw new Exception("route passed in first argument is not a valid route value");
		}
	}


	/**
	 * execute a route statically. this method contains the logic how a route target is executed. see target types for
	 * possible and accepted route types. if route target type is not recognized will throw exception else return the target
	 * type value returned after execution
	 *
	 * @param Route $route expects route object
	 * @param Request|null $request expects optional request instance
	 * @return bool|mixed
	 * @throws Exception
	 */
	public static function exec(Route $route, Request $request = null)
	{
		$type = strtolower((string)$route->target->type);
		$target = $route->target->target;

		if(is_null($request))
		{
			$request = new Request();
		}

		if(!empty($type))
		{
			switch($type)
			{
				case 'include':
					ob_start();
					if(!is_null($route->params))
					{
						@extract((array)$route->params, EXTR_SKIP);
					}
					include $target;
					return ob_get_clean();
				case 'url':
					return self::redirect($target);
				case 'route':
					throw new Exception("route type: route is not supported with static access");
				case 'closure':
					return $target($request, (array)$route->params);
				case 'callable':
					return call_user_func_array($target, array_merge(array($request), array((array)$route->params)));
				case 'renderable':
					return call_user_func_array(array($target, 'render'), array_merge(array($request), array((array)$route->params)));
				case 'action':
					throw new Exception("route type: action is not supported with static access");
				default:
					throw new Exception(setcooki_sprintf("unable to execute route target type: %s", $route->target->type));
			}
		}
		return false;
	}


	/**
	 * execute a fallback which can be callable, closure, exception, route object or url for redirect
	 *
	 * @param null|mixed $fallback expects a valid fallback value
	 * @return mixed
	 * @throws \Exception
	 * @throws null
	 */
	public static function fail($fallback = null)
	{
		if(!is_null($fallback))
		{
			if($fallback instanceof Route)
			{
				return self::exec($fallback);
			}else if($fallback instanceof \Exception){
				throw $fallback;
			}else if(is_object($fallback) && $fallback instanceof \Closure){
				return $fallback();
			}else if(is_callable($fallback)){
				return call_user_func($fallback);
			}else if(is_string(filter_var($fallback, FILTER_VALIDATE_URL) !== false)){
				return self::redirect($fallback);
			}else if($fallback === false){
				return false;
			}else if(setcooki_stringable($fallback)){
				return (string)$fallback;
			}
		}
		return false;
	}


	/**
	 * redirect to url by php´ header location method. if url is not valid or headers can not be send will return false
	 * or return/execute fallback value - see Router::fail for more
	 *
	 * @param string $url expects a url to redirect to
	 * @param int $code expects a response code value
	 * @param null|mixed $fallback expects a resolvable callback value
	 * @return mixed
	 * @throws \Exception
	 */
	public static function redirect($url, $code = 302, $fallback = null)
	{
		if(filter_var($url, FILTER_VALIDATE_URL) !== false)
		{
			header('Location: ' . trim((string)$url), true, (int)$code);
			if(!headers_sent())
			{
				return self::fail($fallback);
			}
		}else{
			return self::fail($fallback);
		}
	}


	/**
	 * redirect to another previously added route by passing route target value in first argument and
	 *
	 * @param string $target expects the target value to match
	 * @param null|array $params expects optional params to overwrite in matched route object
	 * @return bool|mixed
	 * @throws Exception
	 */
	protected function redirectToRoute($target, $params = null)
	{
		$target = preg_replace('=^([a-z]{1,}\:)=', '', (string)$target);

		foreach($this->_routes as $r)
		{
			if($r->route[0]->route === (string)$target)
			{
				if(!is_null($params))
				{
					$r->params = (array)$params;
				}
				return self::execute($r);
			}
		}
		return false;
	}


	/**
	 * match a route value - see concrete implementation by route type
	 *
	 * @param object $route expects the route object part
	 * @return bool
	 */
	protected function match($route)
	{
		switch($route->type)
		{
			/**
			 * match the wordpress custom page template
			 */
			case ($route->type === 'tpl' || $route->type === 'template'):
				if(function_exists('get_page_template') && ($template = get_page_template()) !== '' && preg_match($this->regex($route->route), trim($template)))
				{
					return true;
				}
				break;
			/**
			 * match session variable
			 */
			case 'session':
				if(isset($_SESSION) && !empty($_SESSION))
				{
					//TODO: not implemented yet
				}
				break;
			/**
			 * match post variable
			 */
			case 'post':
				if(isset($_POST) && !empty($_POST))
				{
					//TODO: not implemented yet
				}
				break;
			/**
			 * match get variable
			 */
			case 'get':
				if(isset($_GET) && !empty($_GET))
				{
					//TODO: not implemented yet
				}
				break;
			/**
			 * match url by server request uri value
			 */
			case 'url':
				if(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) && preg_match($this->regex($route->route), trim($_SERVER['REQUEST_URI'])))
				{
					return true;
				}
				break;
		}
		return false;
	}


	/**
	 * normalize regex expression. NOTE: % is a wildcard character and works like mysql LIKE syntax. e.g. "%foo%" will
	 * match and string that contains "foo". "%foo" will match similar to a regex expression like ".*foo$"
	 *
	 * @param string $regex expects regex expression
	 * @return string
	 */
	protected function regex($regex)
	{
		$regex = trim((string)$regex);
		if($regex[0] === '%' && $regex[strlen($regex)-1] === '%')
		{
			$regex = '@'.trim(setcooki_regex_delimit($regex), ' %').'@i';
		}else if($regex[0] === '%' && $regex[strlen($regex)-1] !== '%'){
			$regex = '@'.trim(setcooki_regex_delimit($regex), ' %').'$@i';
		}else if($regex[0] !== '%' && $regex[strlen($regex)-1] === '%'){
			$regex = '@^'.trim(setcooki_regex_delimit($regex), ' %').'@i';
		}
		return $regex;
	}


	/**
	 * on sleep serialize the following vars
	 *
	 * @return array
	 */
	public function __sleep()
    {
        return array('wp', '_routes');
    }
}