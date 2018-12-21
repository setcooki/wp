<?php

namespace Setcooki\Wp\Controller;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Request;
use Setcooki\Wp\Response;
use Setcooki\Wp\Response\Html;
use Setcooki\Wp\Response\Json;
use Setcooki\Wp\Response\Text;
use Setcooki\Wp\Response\Xml;
use Setcooki\Wp\Util\Nonce;
use Setcooki\Wp\Util\Params;

/**
 * Class Ajax
 *
 * @since       1.2
 * @package     Setcooki\Wp\Controller
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Ajax extends Controller
{
    /**
     * auto binds all public + protected method as ajax actions
     */
    const AUTO_BIND                 = 'AUTO_BIND';

    /**
     * enables ajax proxy
     */
    const ENABLE_PROXY              = 'ENABLE_PROXY';

    /**
     * proxy hook or action name to be user in js ajax request data
     */
    const PROXY_HOOK_NAME           = 'PROXY_HOOK_NAME';

    /**
     * proxy nonce lifetime
     */
    const PROXY_NONCE_LIFETIME      = 'PROXY_NONCE_LIFETIME';

    /**
     * on auto bind use prefix for all ajax action names
     */
    const ACTION_PREFIX             = 'ACTION_PREFIX';

    /**
     * echo or die on errors
     */
    const ECHO_ERROR                = 'ECHO_ERROR';


    /**
     * boolean flag to prevent more then once auto bind call
     *
     * @var bool
     */
    protected $_bound = false;


    /**
     * internal instance cache
     *
     * @var array
     * @since 1.2.0
     */
    protected static $_instances = [];


    /**
     * the option map
     *
     * @var array
     */
    public static $optionsMap =
    [
        self::AUTO_BIND             => SETCOOKI_TYPE_BOOL,
        self::ENABLE_PROXY          => SETCOOKI_TYPE_BOOL,
        self::PROXY_HOOK_NAME       => SETCOOKI_TYPE_STRING,
        self::PROXY_NONCE_LIFETIME  => SETCOOKI_TYPE_INT,
        self::ACTION_PREFIX         => SETCOOKI_TYPE_STRING,
        self::ECHO_ERROR            => [SETCOOKI_TYPE_BOOL, SETCOOKI_TYPE_CALLABLE]
    ];


    /**
     * default options
     *
     * @var array
     */
    public $options =
    [
        self::AUTO_BIND             => true,
        self::ENABLE_PROXY          => true,
        self::PROXY_NONCE_LIFETIME  => 1800,
        self::ACTION_PREFIX         => '',
        self::ECHO_ERROR            => true
    ];


    /**
     * create ajax controller instance
     *
     * @since 1.2
     * @param null|mixed $options expects optional options
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct($options = null)
   	{
   	    parent::__construct($options);

   	    static::$_instances[setcooki_id(true)] = $this;

   	    if(setcooki_get_option(self::AUTO_BIND, $this))
        {
            $this->bindActions();
        }
        if(setcooki_get_option(self::ENABLE_PROXY, $this))
        {
            $this->bindProxy();
        }
   	}


    /**
     * TODO: implement check that action = method exists and is only public or protected
     *
     * resolve ajax action which represents public or protected ajax controller method.
     *
     * @since 1.2
     * @param string $action expects the action name
     * @param Ajax|null $controller expects optional controller if not $this
     * @throws \Exception
     */
    protected function resolve($action, Ajax $controller = null)
    {
        $self       = __CLASS__;
        $request    = new Request();
        $params     = new Params(((!empty($_REQUEST)) ? $_REQUEST : array_merge($_POST, $_GET)));

        $response = $this->createResponseFrom($request);

        try
        {
            if(!is_null($controller))
            {
                $action = $this->forward([$controller, $action], $params, $request, $response);
            }else{
                if(stripos($action, '::') === false)
                {
                    $resolver = $this->wp()->store('resolver');
                    if($resolver)
                    {
                        foreach((array)$resolver->getController() as $class)
                        {
                            if(is_subclass_of($class, $self))
                            {
                                try
                                {
                                    $action = $resolver->handle(sprintf("%s::%s", get_class($class), $action), $params, $request, $response);
                                }
                                catch(Exception $e)
                                {
                                    if((int)$e->getCode() !== -1) { throw $e; }
                                }
                            }
                        }
                    }else{
                        throw new Exception(setcooki_sprintf(__("Unable to resolve action: %s since no controller specified or no resolver found", SETCOOKI_WP_DOMAIN), $action));
                    }
                }
                $action = $this->forward($action, $params, $request, $response);
            }
            if($action instanceof Response)
            {
                $action->send();
            }else{
                $response->send($action);
            }
        }
        catch(\Exception $e)
        {
            $error = setcooki_get_option(self::ECHO_ERROR, $this);
            if($error === true)
            {
                echo $response->handleError($e);
            }else if(is_callable($error)){
                echo call_user_func_array($error, [$e]);
            }else{
                setcooki_die($e->getMessage(), false);
            }
        }
        exit;
    }


    /**
     * TODO: allow to pass controller class base name as action value class::method just like proxy
     *
     * auto bind actions = controller methods to be used as ajax action targets via wordpress´s add_action() filter. if
     * class option Ajax::AUTO_BIND is set to true will find all public and protected methods of concrete ajax class and
     * make them available as action. NOTE: it is important to understand the special visibility logic:
     * - public method = is available for all user on frontend
     * - protected method = is available for only logged in users
     * that way we can define visibility on level of the controller class itself. if you need to use protected methods for
     * internal use, use private methods or protected methods with "_" prefix. also to make sure there a no duplicate
     * and conflicting actions names you should define Ajax::ACTION_PREFIX as unique identifier for each framework instance.
     * NOTE: that apart from method to add_action binding no more logic is applied.
     * NOTE: if you use more then 1 concrete ajax controller classes you must not have duplicate public and protected method
     * names - subject to change in future
     * NOTE: in order to make use of full framework controller capacities you should register your ajax controller with
     * Resolver::register especially if you want to use pre/post actions and filters
     *
     * @since 1.2
     * @throws \Exception
     */
   	protected function bindActions()
    {
        if(defined('DOING_AJAX') && DOING_AJAX)
        {
            if($this->_bound)
            {
                return;
            }
            try
            {
                $reflection = new \ReflectionClass($this);
                $prefix = ltrim(setcooki_get_option(self::ACTION_PREFIX, $this), ' _');
                $proxy = function(\ReflectionMethod $action) use ($prefix)
                {
                    if($action->isPublic())
                    {
                        add_action(sprintf("wp_ajax_nopriv_%s%s", $prefix, $action->getName()), function() use ($action)
                        {
                            $this->resolve($action->getName());
                        });
                    }
                    add_action(sprintf("wp_ajax_%s%s", $prefix, $action->getName()), function() use ($action)
                    {
                        $this->resolve($action->getName());
                    });
                };
                foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method)
                {
                    if(
                        $method->getDeclaringClass()->getNamespaceName() !== __NAMESPACE__
                        &&
               			!$method->isConstructor()
               			&&
               			!$method->isDestructor()
               			&&
               			!$method->isStatic()
               			&&
                        !$method->isAbstract()
                        &&
                        $method->getName()[0] !== '_'
                    )
                    {
                        $proxy($method);
                    }
                }
            }
            catch(\ReflectionException $e)
            {
                throw new Exception(setcooki_sprintf(__("Unable to init ajax controller: %s", SETCOOKI_WP_DOMAIN), $e->getMessage()));
            }
            $this->_bound = true;
        }
    }


    /**
     * the proxy allows to route all ajax traffic to proxy router method by the using the same js ajax action parameter name and a proxy
     * parameter for its actual action target. if you run the proxy in parent/child or multisite setup its important that the ajax url
     * is set with setcooki_ajax_url() - only then correct framework instance resolving is guaranted! the following example
     * shows usage in fronted jQuery $.ajax implementation for data:
     * <pre>
     *      <?PHP
     *      data: {
     *          action: '<?php echo setcooki_ajax_proxy(); ?>',
     *          proxy: 'foo',
     *          data: {id: 1},
     *          nonce: '<?php echo setcooki_nonce('foo'); ?>'
     *      }
     *      ?>
     * </pre>
     * - action: will be the proxy name set in Ajax::PROXY_HOOK_NAME or generated automatically
     * - proxy: is the actual action (target) name that would be set in 'action' parameter in normal implementation
     * - data: the data that needs to be passed to action target
     * - nonce: the proxy needs a nonce and the action name must be the same as the value in 'proxy' parameter
     * the proxy can be used to route ajax call to any registered ajax controller and its public and protected methods -
     * see Ajax::bindActions() for visibility context. if you register only one ajax class with resolver you can use
     * the method name as action name. if you register multiple classes you need to use the syntax class.method or
     * class::method as ajax parameter where class is the basename of the class since all classes must be sub class of
     * ajax controller no namespace is required
     * NOTE:
     * - in order to make use of full framework controller capacities you should register your ajax controller with
     *   Resolver::register especially if you want to use pre/post actions and filters
     * - its best practice to not set Ajax::PROXY_HOOK_NAME and let the class create a unique action name
     * - the proxy will be bound once per framework instance so there for the proxy action name must be unique if other
     *   instances of the framework are running plugins
     * - if you forget to set a nonce with setcooki_nonce() proxy will not work
     *
     * @since 1.2
     * @see Ajax::bindActions()
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function bindProxy()
    {
        if(defined('DOING_AJAX') && DOING_AJAX)
        {
            $action = (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) ? trim($_REQUEST['action']) : null;
            $proxy = (isset($_REQUEST['proxy']) && !empty($_REQUEST['proxy'])) ? trim((string)$_REQUEST['proxy']) : null;
            $nonce = (isset($_REQUEST['nonce']) && !empty($_REQUEST['nonce'])) ? trim((string)$_REQUEST['nonce']) : null;

            // We need to get the right framework instance, provided ajax call has the "_id" parameter as provided by setcooki_ajax_url()
            if(isset($_REQUEST['_id']) && !empty($_REQUEST['_id']))
            {
                $wp = $this->wp($_REQUEST['_id']);
                $self = static::$_instances[$_REQUEST['_id']];
            }else{
                $wp = $this->wp();
                $self = $this;
            }

            if(!$this->wp()->stored('ajax.proxy'))
            {
                if(!empty($action) && !empty($proxy))
                {
                    $closure = function() use ($wp, $nonce, $proxy)
                    {
                        try
                        {
                            if(!empty($nonce))
                            {
                                $_proxy = (array)$wp->store('ajax.proxy', null, []);
                                if(!empty($_proxy) && sizeof($_proxy) > 0)
                                {
                                    $proxy = str_replace(['.', ':'], '::', trim($proxy, ' ' . NAMESPACE_SEPARATOR));
                                    $proxy = preg_replace('/\:+/i', '::', $proxy);
                                    $proxy = preg_replace('/\\' . NAMESPACE_SEPARATOR . '+/i', NAMESPACE_SEPARATOR, $proxy);
                                    if(stripos($proxy, NAMESPACE_SEPARATOR) !== false)
                                    {
                                        $proxy = explode('::', $proxy);
                                        $action = trim($proxy[1]);
                                        $controller = trim($proxy[0]);
                                        if(array_key_exists($controller, $_proxy))
                                        {
                                            if(!Nonce::verify($action, (int)setcooki_get_option(self::PROXY_NONCE_LIFETIME, $_proxy[$controller], 1800)))
                                            {
                                                throw new Exception(__("Verify nonce failed", SETCOOKI_WP_DOMAIN));
                                            }
                                            $this->authenticateProxy($_proxy[$controller], $action);
                                            $this->resolve($action, $_proxy[$controller]);
                                        }else{
                                            throw new Exception(setcooki_sprintf(__("Ajax controller: %s is not registered or not a subclass of: %s", SETCOOKI_WP_DOMAIN), $controller, __CLASS__));
                                        }
                                    }else{
                                        if(!Nonce::verify($proxy, (int)setcooki_get_option(self::PROXY_NONCE_LIFETIME, ((sizeof($_proxy) === 1) ? current($_proxy) : null), 1800)))
                                        {
                                            throw new Exception(__("Verify nonce failed", SETCOOKI_WP_DOMAIN));
                                        }
                                        $this->authenticateProxy($this, $proxy);
                                        if(stripos($proxy, '::') === false && sizeof($_proxy) === 1)
                                        {
                                            $this->resolve($proxy, current($_proxy));
                                        }else{
                                            $this->resolve($proxy);
                                        }
                                    }
                                }else{
                                    throw new Exception(__("No proxy controllers have been registered", SETCOOKI_WP_DOMAIN));
                                }
                            }else{
                                throw new Exception(__("No nonce parameter or value found in request", SETCOOKI_WP_DOMAIN));
                            }
                        }
                        catch(\Exception $e)
                        {
                            $error = setcooki_get_option(self::ECHO_ERROR, $this);
                            if($error === true)
                            {
                                echo $this->createResponseFrom(new Request())->handleError($e);
                            }else if(is_callable($error)){
                                echo call_user_func_array($error, [$e]);
                            }
                            exit;
                        }
                    };
                    add_action(sprintf("wp_ajax_nopriv_%s", $action), $closure);
                    add_action(sprintf("wp_ajax_%s", $action), $closure);
                }
            }
            $store = (array)$wp->store('ajax.proxy', null, []);
            $store[sprintf("%s", (new \ReflectionClass($self))->getName())] = $self;
            $wp->store('ajax.proxy', $store);
        }else{
            if(!$this->wp()->stored('ajax.proxy.hook'))
            {
                $this->wp()->store('ajax.proxy.hook', trim(setcooki_get_option(self::PROXY_HOOK_NAME, $this, substr(md5(uniqid() . time()), 0, 10)), ' _'));
            }
        }
    }


    /**
     * we test if action does exist and if user is logged in in case a controller action is protected
     *
     * @param Ajax $controller the ajax controller
     * @param string $action the controller method
     */
    protected function authenticateProxy(Ajax $controller, $action)
    {
        try
        {
            if(!(new \ReflectionMethod($controller, $action))->isPublic() && !is_user_logged_in())
            {
                wp_die(sprintf(__("Ajax action: %s requires logged in user", SETCOOKI_WP_DOMAIN), $action), 400);
            }
        }
        catch(\ReflectionException $e)
        {
            wp_die(sprintf(__("Ajax action: %s is not found or valid", SETCOOKI_WP_DOMAIN), $action), 400);
        }
    }


    /**
     * ceate a response object from request object detected by request accept mime type
     *
     * @since 1.2
     * @param Request|null $request expects request object
     * @return Html|Json|Text|Xml
     * @throws \Exception
     */
    private function createResponseFrom(Request $request = null)
    {
        $response = new Text();
        if(is_null($request))
        {
            $request = new Request();
        }

        $type = $request->getAcceptedContentType();

        if(isset($type[0]))
        {
            try
            {
                if(stripos($type[0], 'json') !== false) {
                    $callback = function($body)
                    {
                        return ['success' => 1, 'data' => $body];
                    };
                    $response = new Json([Json::RESPONSE_CALLBACK => $callback]);
                }else if(stripos($type[0], 'xml') !== false) {
                    $response = new Xml();
                }else if(stripos($type[0], 'html') !== false) {
                    $response = new Html();
                }
            }
            catch(\Exception $e)
            {
                setcooki_die($e);
            }
        }

        return $response;
    }
}
