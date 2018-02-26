<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Exception;

/**
 * Class Hook
 *
 * @package     Setcooki\Wp
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
abstract class Hook
{
    /**
     * contains the hook tag name
     *
     * @var null|string
     */
    public $tag = null;

    /**
     * contains the callback priority of hook
     *
     * @var int
     */
    public $priority = 10;

    /**
     * contains the max accepted arguments for callback
     *
     * @var int
     */
    public $args = 1;

    /**
     * contains optional parameters
     *
     * @var null
     */
    public $params = null;

    /**
     * contains the callback/closure when hook is used in setcooki hook wrapper
     *
     * @var null
     */
    public $callback = null;


    /**
     * class constructor creates new hook
     *
     * @since 1.2
     * @param string $tag expects the hooks tag name
     * @param int $priority expects the hook priority
     * @param int $args expects the max arguments value
     * @param null|mixed $params expects optional additional parameters
     */
    public function __construct($tag, $priority = 10, $args = 1, $params = null)
    {
        $this->tag = (string)$tag;
        $this->priority = (int)$priority;
        $this->args = (int)$args;
        $this->params = $params;
    }


    /**
     * create a new instance by static method
     *
     * @since 1.2
     * @see Hook::__construct()
     * @param string $tag expects the hooks tag name
     * @param int $priority expects the hook priority
     * @param int $args expects the max arguments value
     * @param null|mixed $params expects optional additional parameters
     * @return Hook
     */
    public static function create($tag, $priority = 10, $args = 1, $params = null)
    {
        return new static($tag, $priority, $args, $params);
    }


    /**
     * add hook or batch multiple hook instances as array via setcooki_hook() which will add the hook (Action or Filter)
     * with respective wordpress add_action() or add_filter() functions
     *
     * @since 1.2
     * @see setcooki_hook()
     * @param array|Hook $hook expects Hook instance of array with Hook instances
     */
    public static function add($hook)
    {
        if(!is_array($hook))
        {
            $hook = [$hook];
        }
        foreach($hook as $h)
        {
            setcooki_hook($h);
        }
    }


    /**
     * remove a hook instance via setcooki_hook() which will remove the hook (Action or Filter) with respective wordpress
     * remove_action() or remove_filter() functions
     *
     * @since 1.2
     * @see setcooki_hook()
     * @param Hook $hook expects instance of Hook
     * @return mixed
     */
    public static function remove(Hook $hook)
    {
        return setcooki_hook($hook, false);
    }


    /**
     * fire/execute a hook via setcooki_hook() which will call wordpress do_action() or apply_filter() functions.
     *
     * @since 1.2
     * @see setcooki_hook()
     * @param Hook $hook expects instance of Hook
     * @param array ...$params expects parameters to pass to hook callback
     * @return mixed
     */
    public static function fire(Hook $hook, ...$params)
    {
        return setcooki_hook($hook, true, $params);
    }


    /**
     * concrete implementation of a hook needs an execute function with variable/dynamic arguments number which is not
     * possible with abstract method so we choose magic method __call() instead to prevent hook does not have a callback
     * /method with the name "execute" implemented
     *
     * @since 1.2
     * @param string $name method name
     * @param mixed $arguments methods arguments
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if($name === 'execute')
        {
            throw new Exception(sprintf(__("Action/filter class with tag: '%s' needs 'execute' method implemented", SETCOOKI_WP_DOMAIN), $this->tag));
        }
    }
}