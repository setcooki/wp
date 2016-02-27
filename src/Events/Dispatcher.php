<?php

namespace Setcooki\Wp\Events;

use Setcooki\Wp\Exception;

/**
 * Class Dispatcher
 * @since 1.1.2
 * @package Setcooki\Wp\Events
 */
class Dispatcher
{
	/**
	 * contains all listener objects contained in a \SplPriorityQueue object
	 *
	 * @var null|\SplPriorityQueue
	 */
	private $_listeners = null;

	/**
	 * contains pool of already triggered events
	 *
	 * @var array
	 */
	protected $_triggering = array();

	/**
	 * contains optional class options
	 *
	 * @var array
	 */
	public $options = array();


	/**
	 * class constructor sets class options and initialized priority queue
	 *
	 * @param null|mixed $options expects optional options
	 */
	public function __construct($options = null)
	{
		setcooki_init_options($options, $this);
		$this->_listeners = new \SplPriorityQueue();
	}


	/**
	 * register listener to event. the listener object passed in second argument will be triggered if event name in first
	 * argument matches when dispatcher is triggered. the event name is expected to be a name that can use "." divider to
	 * define event classes, e.g. "router.running". listenables can be:
	 * - instance of Listenable passed in first argument which must return array of listener objects or arrays which can
	 *   resolve to listener objects
	 * - instance of Listener passed in first argument
	 * - event name and callable/closure passed in first and second argument
	 * the third argument priority defines the priority of listener where a higher number = higher priority
	 *
	 * @param string|Listenable|Listener $event expects
	 * @param null|callable|\Closure $listener expects optional callable/closure
	 * @param int $priority expects optional priority which defaults to 0 = no priority
	 * @return $this
	 * @throws Exception
	 */
	public function listen($event, $listener = null, $priority = 0)
	{
		if($event instanceof Listenable){
			$listeners = (array)$event->listen();
		}else if($event instanceof Listener){
			$listeners = array($event, $event->callback());
		}else{
			$listeners = array((string)$event, $listener);
		}
		if(array_key_exists(0, $listeners) && !is_array($listeners[0]))
		{
			$listeners = array($listeners);
		}
		if(ctype_digit($listener) && $priority === 0)
		{
			$priority = (int)$listener;
		}else{
			$priority = (int)$priority;
		}
		foreach($listeners as $listener)
		{
			$listener = (array)$listener;
			if(!setcooki_array_assoc($listener) && sizeof($listener) >= 2)
			{
				if(array_key_exists(1, $listener) && (is_callable($listener[1]) || $listener[1] instanceof \Closure))
				{
					foreach((array)$listener[0] as $l)
					{
						if(!($l instanceof Listener))
						{
							$l = new Listener($l, $listener[1]);
						}
						$this->_listeners->insert($l, $priority);
					}
				}else{
					throw new Exception(setcooki_sprintf('no valid listener callback supplied for event: %s', array($listener[0])));
				}
			}else{
				throw new Exception('need event name and callback for valid event listener');
			}
		}
		return $this;
	}


	/**
	 * remove listeners by event (and listener object). if first argument is empty will remove all previously registered
	 * event listeners. first argument can be a event name as string or multiple as array and will remove all events by
	 * same name which also can contain "*" wildcard which resolves to regex "=^$name.*=". you can pass a Listener object
	 * or callable/\Closure object in second argument which will remove listeners by name and object
	 *
	 * @param null|string|array $event expects event name(s)
	 * @param null|Listener|callable|\Closure $listener expects optional listener object or callable/closure
	 * @return $this
	 */
	public function remove($event = null, $listener = null)
	{
		$tmp = array();

		if(!is_null($event))
		{
			$this->_listeners->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
			foreach((array)$event as $e)
			{
				$e = trim((string)$e);
				foreach($this->_listeners as $key => $val)
				{
					if(!is_null($listener))
					{
						if($listener instanceof Listener)
						{
							$listener = spl_object_hash($listener->callback());
						}else{
							$listener = spl_object_hash($listener);
						}
						if($this->match($val['data'], $e) && (spl_object_hash($val['data']->callback()) === spl_object_hash($listener))) continue;
					}else{
						if($this->match($val['data'], $e)) continue;
					}
					$tmp[] = $val;
				}
			}
			$this->_listeners = new \SplPriorityQueue();
			foreach($tmp as $listener)
			{
				$this->_listeners->insert($listener['data'], $listener['priority']);
            }
			unset($tmp);
		}else{
			$this->reset();
		}
		return $this;
	}


	/**
	 * get listeners. if first argument is empty will return all listeners registered with listen method. if first argument
	 * is a string or array with event names will return all listeners registered by those names. you can also use wildcard
	 * event names. define default return value in second argument which defaults to array
	 *
	 * @param null|string|array $event expects event name(s)
	 * @param array|mixed $default expects default return value
	 * @return array|mixed
	 */
	public function get($event = null, $default = array())
	{
		$tmp = array();

		if(!is_null($event))
		{
			foreach((array)$event as $e)
			{
				$e = trim((string)$e);
				foreach($this->_listeners as $key => $val)
				{
					if($this->match($val, $e))
					{
						$tmp[] = $val;
					}
				}
			}
			return (sizeof($tmp) > 0) ? $tmp : setcooki_default($default);
		}else{
			foreach($this->_listeners as $key => $val)
			{
				$tmp[] = $val;
			}
			return (sizeof($tmp) > 0) ? $tmp : setcooki_default($default);
		}
	}


	/**
	 * checks if any events have been registered or events by name(s) are registered
	 *
	 * @see Dispatcher::has
	 * @param null|string|array $event expects event name(s)
	 * @return bool
	 */
	public function has($event = null)
	{
		return ($this->get($event, false) !== false) ? true : false;
	}


	/**
	 * reset all registered listeners and priority queue
	 *
	 * @return $this
	 */
	public function reset()
	{
		$this->_listeners = new \SplPriorityQueue();
		return $this;
	}


	/**
	 * get last event triggered
	 *
	 * @return null|string
	 */
	public function last()
	{
		if(sizeof($this->_triggering) > 0)
		{
			return $this->_triggering[sizeof($this->_triggering - 1)];
		}else{
			return null;
		}
	}


	/**
	 * trigger event(s) by name or Event object. you can pass an event name with optional wildcard in first argument or
	 * Event object containing event name, or event name in first argument and Event object in second argument. all
	 * combinations will be matched against registered listeners to same event and executed. the most common case is passing
	 * event name as string in first argument and optional data in second argument. unless event propagation
	 * is stopped by setting propagation to false in event object will iterate through all event handlers. if you want
	 * to force iteration to stop you can also set third argument to true and return boolean false in event handler
	 *
	 * @param string|Event $event expects event name or Event object
	 * @param null|Event|mixed $mixed expects Event object or mixed data to pass to listeners handler
	 * @param bool $halt expects boolean flag on whether to sop executing on handler return false;
	 * @return mixed
	 */
	public function trigger($event, $mixed = null, $halt = false)
	{
		$result = null;
		$results = array();

		if($event instanceof Event)
		{
			$mixed = $event;
			$event = $mixed->name();
		}else{
			$event = trim((string)$event);
			if(!is_null($mixed) && !($mixed instanceof Event))
			{
				$mixed = new Event(null, $mixed);
			}else if(is_null($mixed)){
				$mixed = new Event();
			}
		}
		if(empty($mixed->name))
		{
			$mixed->name($event);
		}
		$this->_triggering[] = $event;
		foreach($this->_listeners as $listener)
		{
			if($this->match($listener, $event))
			{
				if(is_null($mixed->subject) && !is_null($listener->subject))
				{
					$mixed->subject($listener->subject);
				}
				$result = $listener->trigger($mixed);
				if(!$mixed->isPropagating())
				{
					break;
				}
				if((bool)$halt && $result === false)
				{
					array_pop($this->_triggering);
					return $result;
				}
				$results[] = $result;
			}
		}
		array_pop($this->_triggering);
		return $results;
	}


	/**
	 * pass instance of class that implements Subscribable where in turn the dispatcher is passed in subscribe method where
	 * listeners can be attached to dispatch method. e.g
	 * ```php
	 * class Foo implements Subscribable
	 * {
	 *      public function subscribe(Dispatcher $dispatcher)
	 *      {
	 *          $dispatcher->listen('event1', array($this, 'method1');
	 *      }
	 * }
	 * ```
	 *
	 * @see Subscribable::subscribe
	 * @param Subscribable $subscriber expects class that implements interface
	 * @return $this
	 */
	public function subscribe(Subscribable $subscriber)
	{
		$subscriber->subscribe($this);
		return $this;
	}


	/**
	 * match listener event names to name passed in second argument and return boolean true if the names match. wildcard
	 * matching with * or sql LIKE  matching is allowed
	 *
	 *
	 * @param Listener $listener expects listener object
	 * @param string $event expects event name
	 * @return bool
	 */
	protected function match(Listener $listener, $event)
	{
		if(stripos($event, '%') !== false)
		{
			if($event[0] === '%' && $event[strlen($event)-1] === '%') {
				$event = '@'.trim(setcooki_regex_delimit($event), ' %').'@i';
			}else if($event[0] === '%' && $event[strlen($event)-1] !== '%'){
				$event = '@'.trim(setcooki_regex_delimit($event), ' %').'$@i';
			}else if($event[0] !== '%' && $event[strlen($event)-1] === '%'){
				$event = '@^'.trim(setcooki_regex_delimit($event), ' %').'@i';
			}
			return (preg_match($event, $listener->name())) ? true : false;
		}else{
			if($event[strlen($event)-1] !== '*')
			{
				return ($listener->name() === $event) ? true : false;
			}else{
				return (preg_match('=^('.setcooki_regex_delimit($event, '.*').').*=i', $listener->name())) ? true : false;
			}
		}
	}


	/**
	 * on clone reset triggering pool
	 */
	public function __clone()
	{
		$this->_triggering = array();
	}
}