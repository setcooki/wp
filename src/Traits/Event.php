<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Events\Dispatcher;
use Setcooki\Wp\Events\Listener;
use Setcooki\Wp\Exception;

/**
 * Trait Event
 *
 * @since       1.1.2
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Event
{
	/**
	 * contains the optional event dispatcher instance
	 *
	 * @var null|Dispatcher
	 */
	protected $_eventDispatcher = null;


    /**
     * attach event listener to this class by passing event name in first argument and callback in second argument. since
     * the trait should be used in context of a class instance its also allowed to just pass a method name as listener
     * argument. the listener is stored in \Setcooki\Wp\Wp theme/plugin dispatcher instance since events are global to
     * the theme/plugins instance. see \Setcooki\Wp\Events\dispatcher::listen() for more
     *
     * @see \Setcooki\Wp\Events\dispatcher::listen()
     * @param string|\Setcooki\Wp\Events\Listenable|\Setcooki\Wp\Events\Listener $event expects
     * @param null|callable|\Closure $listener expects optional callable/closure
     * @param int $priority expects optional priority which defaults to 0 = no priority
     * @return $this
     * @throws Exception
     * @throws \Exception
     */
	public function on($event, $listener, $priority = 0)
	{
		$this->getEventDispatcher()->listen(new Listener($event, $this->getEventListener($listener), $this), (int)$priority);
		return $this;
	}


    /**
     * remove event listener. since the trait should be used in context of a class instance its also allowed to just pass
     * a method name as listener argument. see \Setcooki\Wp\Events\dispatcher::remove() for more
     *
     * @see \Setcooki\Wp\Events\dispatcher::remove()
     * @param null|string|array $event expects event name(s)
     * @param null|\Setcooki\Wp\Events\Listener|callable|\Closure $listener expects optional listener object or callable/closure
     * @return $this
     * @throws Exception
     * @throws \Exception
     */
	public function off($event = null, $listener = null)
	{
		$this->getEventDispatcher()->remove($event, (!is_null($listener) ? $this->getEventListener($listener) : null));
		return $this;
	}


	/**
	 * trigger event listener shortcut function. see \Setcooki\Wp\Events\dispatcher::trigger()
	 *
	 * @see \Setcooki\Wp\Events\dispatcher::trigger()
	 * @param string|Event $event expects event name or Event object
	 * @param null|Event|mixed $mixed expects Event object or mixed data to pass to listeners handler
	 * @param bool $halt expects boolean flag on whether to sop executing on handler return false;
	 * @return mixed
	 */
	public function trigger($event, $mixed = null, $halt = false)
	{
		return $this->getEventDispatcher()->trigger($event, $mixed, $halt);
	}


	/**
	 * set event dispatcher instance. if called without any argument will create a standard event dispatcher instance
	 *
	 * @param Dispatcher|null $dispatcher expects optional dispatcher instance
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $dispatcher = null)
	{
		if(!is_null($dispatcher))
		{
			$this->_eventDispatcher = $dispatcher;
		}else{
			$this->_eventDispatcher = new Dispatcher();
		}
	}


    /**
     * returns the event dispatcher attached to this class that if not dispatcher has been attached the global dispatcher
     * create in theme/plugin creation is used
     *
     * @return Dispatcher
     * @throws \Exception
     */
	public function getEventDispatcher()
	{
		return (!is_null($this->_eventDispatcher)) ? $this->_eventDispatcher : setcooki_wp()->store('dispatcher');
	}


	/**
	 * validate a listener = callback object passed in first argument which can also be a method name of $this
	 *
	 * @param string|callable|\Closure $listener expects a resolvable callback object
	 * @return callable|\Closure
     * @throws Exception
	 */
	protected function getEventListener($listener)
	{
		if(is_callable($listener) || ($listener instanceof \Closure))
		{
			return $listener;
		}else{
			try
			{
				$listener = (string)$listener;
				$method = new \ReflectionMethod($this, $listener);
				if($method->isStatic())
				{
					throw new Exception(setcooki_sprintf(__("Listener method: %s is static", SETCOOKI_WP_DOMAIN), $listener));
				}
				if($method->isAbstract())
				{
					throw new Exception(setcooki_sprintf(__("Listener method: %s is abstract", SETCOOKI_WP_DOMAIN), $listener));
				}
				if(!$method->isPublic())
				{
					throw new Exception(setcooki_sprintf(__("Listener method: %s is not public", SETCOOKI_WP_DOMAIN), $listener));
				}
				return [$this, $listener];
			}
			catch(\ReflectionException $e)
			{
				throw new Exception(setcooki_sprintf(__("Listener method: %s does not exist or is not accessible: %s", SETCOOKI_WP_DOMAIN), [$listener, $e->getMessage()]));
			}
		}
	}
}