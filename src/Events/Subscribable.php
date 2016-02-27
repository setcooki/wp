<?php

namespace Setcooki\Wp\Events;

/**
 * Interface Subscribable
 * @since 1.1.2
 * @package Setcooki\Wp\Events
 */
interface Subscribable
{
	/**
	 * receives dispatcher instance to be used to register event listeners e.g.
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
	 * @see Dispatcher::subscribe
	 * @param Dispatcher $dispatcher expects dispatcher instance
	 * @return void
	 */
	public function subscribe(Dispatcher &$dispatcher);
}