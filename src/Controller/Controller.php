<?php

namespace Setcooki\Wp\Controller;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Request;
use Setcooki\Wp\Traits\Singleton;
use Setcooki\Wp\Response;
use Setcooki\Wp\Controller\Filter\Unit;
use Setcooki\Wp\Traits\Wp;

/**
 * Class Controller
 *
 * @package     Setcooki\Wp\Controller
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
abstract class Controller
{
    use Wp;
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
	protected $_before = [];

	/**
	 * contains after filter
	 *
	 * @var array
	 */
	protected $_after = [];


    /**
     * create a controller instance
     *
     * @param null|mixed $options expects optional options
     * @throws \Exception
     */
	public function __construct($options = null)
	{
        setcooki_init_options($options, $this);
	}


	/**
	 * add filters to controller to be executed before controller action execution. see Unit::__construct() for more details
	 * how to use controller filters. the filter can be passed as filter object or array that can be converted to filter
	 * object. if you pass an array in first argument expects the filter options to be passed in second argument. if no
	 * argument is passed in first argument returns all previously added filter as this function is a setter/getter method
	 *
	 * @see Unit::__constructor()
	 * @param null|Unit|array $filter expects a valid filter value
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
	 * add filters to controller to be executed after controller action execution. see Unit::__construct() for more details
	 * how to use controller filters. the filter can be passed as filter object or array that can be converted to filter
	 * object. if you pass an array in first argument expects the filter options to be passed in second argument. if no
	 * argument is passed in first argument returns all previously added filter as this function is a setter/getter method
	 *
	 * @see Unit::__constructor()
	 * @param null|Unit|array $filter expects a valid filter value
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
	 * @param null|Unit|array $filter expects a valid filter value
	 * @param null|array $options expects optional filter options
	 * @return mixed
     * @throws Exception
	 */
	public function attachFilter($type, $filter, $options = null)
	{
		$type = strtolower(trim((string)$type));

		if(in_array($type, [self::BEFORE, self::AFTER]))
		{
			return $this->{$type}($filter, $options);
		}else{
			throw new Exception(setcooki_sprintf(__("Can not attach filter since first argument: %s is not valid", SETCOOKI_WP_DOMAIN), $type));
		}
	}


	/**
	 * detach a filter previously attached for controller before or after action execution depending on first argument.
	 * the second argument must be the same filter value that has been passed in Controller::attachFilter method. in case
	 * of a closure e.g you must pass the closure stored in a variable. if the filter is not valid will silently fail!
	 *
	 * @param string $type expects "before" or "after" as string
	 * @param null|Unit|array $filter expects a valid filter value
	 * @return $this
	 */
	public function detachFilter($type, $filter)
	{
		$type = strtolower(trim((string)$type));

		if(in_array($type, [self::BEFORE, self::AFTER]))
		{
			$type = "_" . $type;
			for($i = 0; $i < sizeof($this->$type); $i++)
			{
				try
				{
					if($this->{$type}[$i]->name === (call_user_func([$this, 'parseFilter'], $filter)[0]->name))
					{
						unset($this->{$type}[$i]);
					}
				}
				catch(\Exception $e){}
			}
			$this->{$type} = array_values($this->{$type});
		}
		return $this;
	}


    /**
     * parse a filter value by passing any passed value in first argument to Unit::__construct() which will create a
     * valid filter unit or throw exception in case the filter value can not be transformed to filter unit.
     *
     * @param array|mixed $filter expects a valid filter value
     * @param null|array $options expects optional filter unit options
     * @return array
     * @throws Exception
     */
	protected function parseFilter($filter, $options = null)
	{
		if(!is_array($filter))
		{
			$filter = [$filter];
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
     * forward to another controller by passing the forward target controller in first argument. the controller value
     * can be a string with class::method syntax a class method callable as array. If a resolver instance is found will
     * execute the controller action via the resolver handle method firing registered filters. If not this method will
     * try to resolve the first argument and try to call the appropriate controller if the the controller is a callable
     * or any other value that can be resolved. Will throw exception if unresolvable
     *
     * @since 1.2
     * @param string|callable|mixed $controller expects the controller value
     * @param null|mixed $params optional parameters to pass to target
     * @param Request|null $request optional request object to pass to target
     * @param Response|null $response optional response object to pass to target
     * @return mixed
     * @throws Exception
     */
	public function forward($controller, $params = null, Request $request = null, Response $response = null)
    {
        $resolver = static::wp()->store('resolver');

        if($resolver && $resolver->registered($controller))
        {
            //translate controller callable to controller::action string
            if(is_array($controller) && sizeof($controller) >= 2)
            {
                if(is_object($controller[0]))
                {
                    $controller = sprintf("%s::%s", get_class($controller[0]), trim($controller[1]));
                }else{
                    $controller = sprintf("%s::%s", trim((string)$controller[0]), trim($controller[1]));
                }
            }
            return $resolver->handle($controller, $params, $request, $response);
        }else{
            if($request === null)
            {
                $request = new Request();
            }
            if($response === null)
            {
                $response = new Response();
            }
            if(is_callable($controller))
            {
                return call_user_func_array($controller, [$params, $request, $response]);
            }
            if(is_string($controller))
            {
                if(stripos($controller, '::') !== false)
                {
                    $controller = explode('::', trim($controller));
                    if(class_exists($controller[0]) && is_subclass_of($controller[0], __CLASS__))
                    {
                        $controller[0] = new $controller[0](static::wp());
                    }else{
                        throw new Exception(setcooki_sprintf(__("Controller class: %s passed in first argument does not exist or is not a sub class of controller", SETCOOKI_WP_DOMAIN), $controller[0]));
                    }
                }else{
                    throw new Exception(__("Controller passed as string in first argument must contain :: class::method separator", SETCOOKI_WP_DOMAIN));
                }
            }
            if(sizeof($controller) >= 2 && $controller[0] instanceof Controller && method_exists($controller[0], $controller[1]))
            {
                return call_user_func_array([$controller[0], $controller[1]], [$params, $request, $response]);
            }else{
                throw new Exception(__("Controller passed in first argument is not a valid controller", SETCOOKI_WP_DOMAIN));
            }
        }
    }


    /**
     * forward to another controller by path which can be from an URL or path variable with "/" separator. unless the path
     * does not have a wildcard pattern ".*" will assume the last part of path as action and the rest as namespace path if
     * action is not found in current controller. forwarding by path has 2 passes. 1) look for action in current controller $this.
     * 2) lookup and try if path matches any other controller action. This method also accepts a request object in first
     * argument (the preferred way) as it contains the url and possible a route object if resolver is used in framework
     * instance.
     *
     * @since 1.2
     * @param string|callable|mixed $path expects the controller value
     * @param null|mixed $params optional parameters to pass to target
     * @param Request|null $request optional request object to pass to target
     * @param Response|null $response optional response object to pass to target
     * @return mixed
     * @throws Exception
     */
    protected function forwardByPath($path, $params = null, Request $request = null, Response $response = null)
    {
        $route = null;

        if(is_object($path) && $path instanceof Request)
        {
            if($path->hasRoute())
            {
                $route = trim((string)$path->getRoute()->route[0]->route);
            }
            $path = $path->path();
        }
        if(($path = parse_url(trim((string)$path, ' /'), PHP_URL_PATH)) !== false && !empty($path))
        {
            if(stripos($path, '::') !== false)
            {
                return $this->forward($path, $params, $request, $response);
            }else{
                try
                {
                    if(!empty($route) && stripos($route, '.*') !== false)
                    {
                        $route = trim($route, '\/');
                        $route = preg_replace('=(\(?(\.\*)\)?)=i', '(\\2)', $route);
                        if(preg_match('=' . setcooki_regex_delimit($route) . '=i', $path, $m))
                        {
                            return $this->forward([$this, trim($m[1])], $params, $request, $response);
                        }else{
                            throw new Exception(setcooki_sprintf(__("Route: %s does not match anything in path: %s", SETCOOKI_WP_DOMAIN), $route, $path));
                        }
                    }else{
                        return $this->forward([$this, trim(basename($path), ' /')], $params, $request, $response);
                    }
                }
                catch(Exception $e)
                {
                    //try to find any matching callable or controller
                    return $this->forward(sprintf('%s::%s', NAMESPACE_SEPARATOR . trim(str_replace('/', NAMESPACE_SEPARATOR, dirname($path)), NAMESPACE_SEPARATOR), basename($path)), $params, $request, $response);
                }
            }
        }else{
            throw new Exception(setcooki_sprintf(__("Path: %s in first argument is not a forwardable path", SETCOOKI_WP_DOMAIN), $path));
        }
    }


    /**
     * redirect shortcut
     *
     * @since 1.2
     * @param string $url expects redirect url
     * @param int $status expects redirect status
     * @throws Exception
     */
    public function redirect($url, $status = 302)
    {
        Request::redirect($url, $status);
    }


	/**
	 * directly execute a controller action/method without any filters applied!
	 *
	 * @param string $action expects the action/method name
	 * @param null|array $params expects optional params to pass to controller action
     * @param Request|null $request expects optional request instance
	 * @param Response|null $response expects optional response instance
	 * @return mixed
     * @throws Exception
	 */
	public function call($action, $params = null, Request $request = null, Response $response = null)
	{
		$action = trim((string)$action);

        if(is_null($request))
        {
            $request = new Request();
    	}
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
					return call_user_func_array([$this, $action], [$params, $request, $response]);
				}else{
					throw new Exception(setcooki_sprintf(__("Can not call controller action: %s since its not public or static", SETCOOKI_WP_DOMAIN), $action));
				}
			}
			catch(\ReflectionException $e)
			{
				throw new Exception(setcooki_sprintf(__("Can not call controller action: %s", SETCOOKI_WP_DOMAIN), $e->getMessage()));
			}
		}else{
			throw new Exception(setcooki_sprintf(__("Can not call controller action since: %s does not exist", SETCOOKI_WP_DOMAIN), $action));
		}
	}


	/**
	 * do not allow access to none existing methods
	 *
	 * @param string $name expects method name
	 * @param mixed $parameters expects parameters
     * @throws Exception
	 */
	public function __call($name, $parameters)
	{
		throw new Exception(setcooki_sprintf(__("Method: %s does not exist", SETCOOKI_WP_DOMAIN), $name));
	}


	/**
	 * do not allow access to none existing static methods
	 *
	 * @param string $name expects method name
	 * @param mixed $arguments expects parameters
     * @throws Exception
	 */
	public static function __callStatic($name, $arguments)
	{
		throw new Exception(setcooki_sprintf(__("Method: %s does not exist", SETCOOKI_WP_DOMAIN), $name));
	}
}