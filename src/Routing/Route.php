<?php

namespace Setcooki\Wp\Routing;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Interfaces\Renderable;
use Setcooki\Wp\Request;

/**
 * Class Route
 * @package Setcooki\Wp\Routing
 */
class Route
{
    /**
     * const for route type: include
     */
    const TYPE_INCLUDE                  = 'include';

    /**
     * const for route type: action
     */
    const TYPE_ACTION                   = 'action';

    /**
     * const for route type: route
     */
    const TYPE_ROUTE                    = 'route';

    /**
     * const for route type: url
     */
    const TYPE_URL                      = 'url';

    /**
     * const for route type: callable
     */
    const TYPE_CALLABLE                 = 'callable';

    /**
     * const for route type: closure
     */
    const TYPE_CLOSURE                  = 'closure';

    /**
     * const for route type: renderable
     */
    const TYPE_RENDERABLE               = 'renderable';


    /**
     * contains the route items
     *
     * @var null|array
     */
    public $route = array();

    /**
     * contains the routes target value
     *
     * @var null|array
     */
    public $target = array();

	/**
	 * contains optional parameters
	 *
	 * @var array
	 */
	public $params = array();


	/**
	 * a route object contains 1+n route condition(s) and a singular target. the route can be:
	 * - url = listen to the servers request url
	 * - tpl|template = listen to wordpress current used custom template
	 * - post = listen to request post array values
	 * - get = listen to request get array values
	 * - session = listen to request session array values
	 * the route is defined by routes type prefix or identifier e.g "url|tpl|post" and the route condition which is considered
	 * to be a regex expression or otherwise resolvable query expression separated by ":". e.g. "url:/test/*" will match
	 * an url with a path that starts with "/test". the "*" wildcard symbol works like mysql´s like expression. if multiple
	 * route conditions are passed in first argument all conditions must match positive in order to be considered that the
	 * route object matches!
	 * the second argument defines a target which can be:
	 * - null = for custom routes and target to be contained in Route::execute method
	 * - include = include a file by absolute file path
	 * - url = redirect to url
	 * - route = redirect to another route
	 * - action = execute a controllers action (NOTE! action must be passed in string syntax - see Resolver class for more
	 * - callable = execute a callable
	 * - closure = execute a closure
	 * - renderable = execute a class instance that implements renderable interface
	 * - the target type is auto identified. any none resolvable target will raise an exception
	 * the third argument binds parameter to the target (if target can accept parameter) see the Router class for more
	 *
	 * @param string|array $route expects 1+n route strings
	 * @param null|mixed $target expects a target value
	 * @param null|array|object $params expects optional parameters
	 * @throws Exception
	 */
    public function __construct($route, $target = null, $params = null)
    {
	    $type = null;
	    $types = array
	    (
		    'url',
            'tpl',
            'template',
            'post',
            'get',
            'session'
	    );

	    if(!is_null($params))
	    {
		    $this->params = (array)$params;
	    }
	    if(!is_array($route))
	    {
		    $route = array($route);
	    }
	    foreach($route as &$r)
	    {
		    if(preg_match('=^([a-z]{1,})\:(.*)=i', (string)$r, $m) && in_array(strtolower(trim($m[1])), $types))
		    {
			    array_push($this->route, (object)
			    [
				    'route' => trim($m[2]),
				    'type'  => strtolower(trim($m[1]))
			    ]);
		    }else{
			    throw new Exception(setcooki_sprintf("route: %s has missing or not recognized route type identifier", $r));
		    }
	    }
	    if(!is_null($target))
	    {
		    if(!is_array($target) && !is_object($target))
		    {
			    $target = trim((string)$target);
			    if(preg_match('=\.(php|inc|tpl|phtml|xhtml|html|htm)$=i', $target) && is_file($target)){
				    $type = static::TYPE_INCLUDE;
			    }else if(filter_var($target, FILTER_VALIDATE_URL) !== false) {
					$type = static::TYPE_URL;
				}else if(preg_match('=^([a-z]{1,})\:(.*)=', $target, $m) && in_array($m[1], $types)){
					$type = static::TYPE_ROUTE;
				}else if(preg_match('=^([^:.]*)(\.|\:\:?)([a-z0-9\_]{1,})$=i', $target, $m)){
				    if(is_subclass_of($m[1], '\Setcooki\Wp\Controller\Controller'))
                    {
                        $type = static::TYPE_ACTION;
                    }else{
                        $type = static::TYPE_CALLABLE;
                    }
			    }else if(is_callable($target)){
				    $type = static::TYPE_CALLABLE;
				}else{
					throw new Exception(setcooki_sprintf("route target: %s is not a valid value", $target));
				}
		    }else if(is_object($target) && $target instanceof \Closure){
			    $type = static::TYPE_CLOSURE;
		    }else if(is_array($target) && is_callable($target)){
				$type = static::TYPE_CALLABLE;
		    }else if(is_object($target) && ($target instanceof Renderable)){
			    $type = static::TYPE_RENDERABLE;
		    }else{
				throw new Exception("route target is not resolvable");
		    }
	    }
        $this->target = (object)
        [
	        'target' => $target,
	        'type'   => $type
        ];
    }


	/**
	 * create new route instance by static access - see Route::__construct
	 *
	 * @see Route::__construct
	 * @param string|array $route expects 1+n route strings
	 * @param mixed $target expects a target value
	 * @param null|array|object $params expects optional parameters
	 * @return Route
	 */
	public static function create($route, $target, $params = null)
	{
		return new self($route, $target, $params);
	}


	/**
	 * gets called before route execution and is meant to be used in custom route classes that override this class
	 *
	 * @param Request $request expects request object
	 * @return void
	 */
	public function beforeExecute(Request $request)
	{
	}


	/**
	 * execute the route once route matches by processing target with additional params. the concrete implementation can
	 * be overriden in custom route class and if not will be handled in Router::exec method
	 *
	 * @see Router::exec
	 * @param Request $request expects request object
	 * @return mixed
	 * @throws Exception
	 */
	public function execute(Request $request)
	{
		return Router::exec($this, $request);
	}


	/**
	 * gets called after route execution and is meant to be used in custom route classes that override this class
	 *
	 *  @param Request $request expects request object
	 * @return void
	 */
	public function afterExecute(Request $request)
	{
	}
}