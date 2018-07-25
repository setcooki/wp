<?php

namespace Setcooki\Wp\Events;

use Setcooki\Wp\Exception;

/**
 * Class Listener
 *
 * @since       1.1.2
 * @package     Setcooki\Wp\Events
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Listener
{
	/**
	 * contains event name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * contains callback which is a callable or closure
	 *
	 * @var null|callable|\Closure
	 */
	public $callback = null;

	/**
	 * contains event subject/target
	 *
	 * @var null|mixed
	 */
	public $subject = null;


    /**
     * create a listener for a event name passed in first argument in a callback passed in second argument that gets
     * executed when listener is executed
     *
     * @param string $name expects event name to listen to
     * @param callable|\Closure $callback expects a valid callback value
     * @param null|mixed $subject expects optional subject/target object
     * @throws \Exception
     */
	public function __construct($name, $callback, $subject = null)
	{
		$this->name = $name;
		$this->callback = $this->isCallable($callback);
		$this->subject = $subject;
	}


	/**
	 * create listener by static access
	 *
	 * @param string $name expects event name to listen to
	 * @param callable|\Closure $callback expects a valid callback value
	 * @param null|mixed $subject expects optional subject/target object
	 * @return Listener
	 */
	public static function create($name, $callback, $subject = null)
	{
		return new self($name, $callback, $subject);
	}


	/**
	 * event name setter/getter
	 *
	 * @param null|string $name expects event name as string
	 * @return string
	 */
	public function name($name = null)
	{
		if(!is_null($name))
		{
			$this->name = (string)$name;
		}
		return $this->name;
	}


    /**
     * callback setter/getter
     *
     * @param callable|\Closure $callback expects callback
     * @return null|callable|\Closure
     * @throws \Exception
     */
	public function callback($callback = null)
	{
		if(!is_null($callback))
		{
			$this->callback = $this->isCallable($callback);
		}
		return $this->callback;
	}


	/**
	 * subject setter/getter
	 *
	 * @param null|mixed $subject expects subject/target object
	 * @return mixed|null
	 */
	public function subject($subject = null)
	{
		if(!is_null($subject))
		{
			$this->subject = $subject;
		}
		return $this->subject;
	}


	/**
	 * trigger/execute the callback with optional args
	 *
	 * @param null|mixed $args expects optional arguments
	 * @return mixed
	 */
	public function trigger($args = null)
	{
		if($this->callback instanceof \Closure)
		{
			return $this->callback->__invoke($args);
		}else{
			return call_user_func($this->callback, $args);
		}
	}


	/**
	 * tests a callback if it can be resolved and if so returns the callback if not throws error
	 *
	 * @param mixed $callback expects callback value
	 * @return mixed
     * @throws \Exception
	 */
	protected function isCallable($callback)
	{
		if(setcooki_is_callable($callback))
		{
			return $callback;
		}else{
			throw new Exception(__("Callback is not a callable or closure", SETCOOKI_WP_DOMAIN));
		}
	}


	/**
	 * trigger event by using Listener instance object as function
	 *
	 * @param null|mixed $args expects optional arguments
	 * @return mixed
	 */
	public function __invoke($args = null)
	{
		return $this->trigger($args);
	}


	/**
	 * on string cast return event name
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->name;
	}
}
