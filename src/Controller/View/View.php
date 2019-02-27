<?php

namespace Setcooki\Wp\Controller\View;

use Setcooki\Wp\Wp;
use Setcooki\Wp\Traits\Cache;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Controller\Controller;
use Setcooki\Wp\Content\Template;
use Setcooki\Wp\Exception;

/**
 * Class View
 *
 * @package     Setcooki\Wp\Controller
 * @subpackage  Setcooki\Wp\Controller\View
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class View
{
    use Cache;


    /**
     * view to render which can be a file template or a template instance
     *
     * @var null|string|Template
     */
    public $view = null;

    /**
     * variable container contains all vars available for view
     *
     * @var array
     */
    protected $_vars = [];

    /**
     * contains controller instance
     *
     * @var null|Controller
     */
    public $controller = null;


    /**
     * constructor sets controller instance and optional view and vars which also can be passed in render function
     *
     * @param Controller $controller expects controller instance
     * @param string|Template $view expects view template file or template instance
     * @param null|mixed $vars expects optional vars to assign to view
     * @throws \Exception
     */
    public function __construct(Controller $controller, $view, $vars = null)
    {
        $this->controller = $controller;
        $this->view($view);
        if(!is_null($vars))
        {
            $this->assign($vars);
        }
    }


    /**
     * static function to create view instance
     *
     * @see View::__construct()
     * @param Controller $controller expects controller instance
     * @param string|Template $view expects view template file or template instance
     * @param null|mixed $vars expects optional vars to assign to view
     * @return View
     */
    public static function create(Controller $controller, $view, $vars = null)
    {
        return new self($controller, $view, $vars);
    }


    /**
     * view setter/getter function
     *
     * @param null|Template|string $view expects view
     * @return null|Template|string
     */
    public function view($view = null)
    {
        if(!is_null($view))
        {
            $this->view = $view;
        }
        return $this->view;
    }


    /**
     * set and if already set override all previously set vars and return them
     *
     * @see View::assign()
     * @param null|mixed $vars expects optional vars to set and override
     * @return array
     * @throws \Exception
     */
    public function vars($vars = null)
    {
        if(!is_null($vars))
        {
            $this->reset();
            $this->assign($vars);
        }
        return $this->_vars;
    }


    /**
     * assign vars to view where:
     * - first argument is an array and second not set expecting an array with key => value pairs
     * - first argument is instance of Param and second not set which transfers vars from Params instance to view
     * - first argument is a string expecting normal key => value pair operation.
     *
     * @param mixed $key expects key as explained
     * @param null $value expects value assigned to key
     * @return $this
     * @throws \Exception
     */
    public function assign($key, $value = null)
    {
        if((is_array($key) || $key instanceof \stdClass) && is_null($value))
        {
            foreach($key as $k => $v)
            {
                $this->assign($k, $v);
            }
        }else if($key instanceof Params && is_null($value)){
            foreach($key->__toArray() as $k => $v)
            {
                $this->assign($k, $v);
            }
        }else if(setcooki_stringable($key)){
            $key = (string)$key;
            if(stripos($key, '.') !== false)
            {
                setcooki_object_set($this->_vars, $key, $value);
            }else{
                $this->_vars[$key] = $value;
            }
        }else{
            throw new Exception(__("Can not assign key passed in first argument", SETCOOKI_WP_DOMAIN));
        }
        return $this;
    }


    /**
     * unassign or unset previously assigned var by key which must be a string
     *
     * @param string $key expects var key to unset
     * @return $this
     * @throws \Exception
     */
    public function unassign($key)
    {
        if(setcooki_stringable($key))
        {
            if(stripos($key, '.') !== false)
            {
                setcooki_object_unset($this->_vars, $key);
            }else if($this->__isset($key)){
                $this->__unset($key);
            }
            return $this;
        }else{
            throw new Exception(__("Can not unassign by key passed in first argument", SETCOOKI_WP_DOMAIN));
        }
    }


    /**
     * reset all vars assigned to view
     */
    public function reset()
    {
        $this->_vars = [];
    }


    /**
     * composite nest function that can assign views to a view assuming that second argument is either instance of View
     * or a view template file or template instance.
     *
     * @param string $key expects the var key
     * @param string|View|Template $view expects a value that can create a valid view object
     * @param null $vars expects optional vars to use in nested view
     * @return $this
     * @throws \Exception
     */
    public function nest($key, $view, $vars = null)
    {
        if($view instanceof View)
        {
            $this->assign($key, $view);
        }else{
            $this->assign($key, self::create($this->controller, $view, $vars));
        }
        return $this;
    }


    /**
     * set a var
     *
     * @param string $name expects var name
     * @param mixed $value expects the var value
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->_vars[$name] = $value;
        return $this;
    }


    /**
     * get a var by name/key as reference
     *
     * @param string $name expects var name
     * @return mixed
     * @throws \Exception
     */
    public function &__get($name)
    {
        if(!isset($this->_vars[$name]))
        {
            throw new Exception(setcooki_sprintf(__("Unable to __get: %s from view", SETCOOKI_WP_DOMAIN), $name));
        }
        $var = $this->_vars[$name];
        return ($var instanceof \Closure) ? $var($this) : $var;
    }


    /**
     * check if var isset
     *
     * @param string $name expects var name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_vars[$name]);
    }


    /**
     * unset a var
     *
     * @param string $name expects var name
     * @return $this
     * @throws \Exception
     */
    public function __unset($name)
    {
        if(!isset($this->_vars[$name]))
        {
            throw new Exception(setcooki_sprintf(__("Unable to __unset: %s from view", SETCOOKI_WP_DOMAIN), $name));
        }
        unset($this->_vars[$name]);
        return $this;
    }


    /**
     * render the view passed in constructor. the view buffer or returned string can be cached if first argument is a int
     * value >= which represents the cache lifetime. cache instance must be set with View::cache prior to usage. the second
     * optional argument can be a callable sending view return buffer to that function for further manipulation
     *
     * @param null|int $cache expects optional cache life time
     * @param callable|null $callback expects optional callback
     * @return mixed
     * @throws \Exception
     */
    public function render($cache = null, callable $callback = null)
    {
        $vars = $this->vars();
        $view = $this->view();

        if(!is_null($cache) && ctype_digit($cache) && $this->cache())
        {
            $cache = (int)$cache;
        }else{
            $cache = -1;
        }

        //view is a Template instance
        if($view instanceof Template)
        {
            $key = \Setcooki\Wp\Cache\Cache::key($view->template(), $vars);
            if($cache >= 0 && ($view = $this->cache($key, false)) !== false)
            {
                //do nothing
            }else{
                ob_start();
                $view->add($vars);
                $view->render();
                $view->flush(true);
                $view = ob_get_clean();
                if($cache >= 0)
                {
                    $this->cache($key, $view, $cache);
                }
            }
        //view is a template file
        }else if(($view = $this->lookup($view)) !== false){
            $key = \Setcooki\Wp\Cache\Cache::key($view, $vars);
            if($cache >= 0 && ($view = $this->cache($key, false)) !== false)
            {
                //do nothing
            }else{
                extract($vars);
                ob_start();
                require setcooki_pathify($view);
                $view = ob_get_clean();
                if($cache >= 0)
                {
                    $this->cache($key, $view, $cache);
                }
            }
        }else{
            throw new Exception(sprintf(__("Unable to render view: %s since view data type or value not supported", SETCOOKI_WP_DOMAIN), $view));
        }

        if(!is_null($callback))
        {
            return call_user_func_array($callback, [$view, $this]);
        }else{
            return $view;
        }
    }


    /**
     * include a file and pass array in second argument as local vars
     *
     * @param string $file expects a file path
     * @param array $vars expects optional vars
     * @return string
     * @throws \Exception
     */
    public function inc($file, Array $vars = [])
    {
        if(($file = $this->lookup($file)) !== false)
        {
            extract($vars);
            ob_start();
            require setcooki_pathify($file);
            return ob_get_clean();
        }else{
            throw new Exception(setcooki_sprintf(__("Unable to include file: %s", SETCOOKI_WP_DOMAIN), $file));
        }
    }


    /**
     * lookup/get file by testing first argument for valid absolute file path or relative file path from theme/plugin
     *
     * @param string $file expects a absolute or relative path to a file
     * @return bool|string
     */
    protected function lookup($file)
    {
        if(DIRECTORY_SEPARATOR === '\\')
        {
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        }
        $file = DIRECTORY_SEPARATOR . ltrim(trim($file), DIRECTORY_SEPARATOR);
        if(is_file($file) && is_readable($file))
        {
            return $file;
        }
        $file = Wp::b($file);
        if(is_file($file) && is_readable($file))
        {
            return $file;
        }
        return false;
    }


    /**
     * render view
     *
     * @return string
     * @throws \Exception
     */
    public function __toString()
    {
        return $this->render();
    }


    /**
     * on clone reset
     */
    public function __clone()
    {
        $this->reset();
    }


    /**
     * on serialize keep the following vars
     *
     * @return array
     */
    public function __sleep()
    {
        return ['view', 'vars', 'controller'];
    }
}
