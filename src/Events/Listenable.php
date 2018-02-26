<?php

namespace Setcooki\Wp\Events;

/**
 * Interface Listenable
 *
 * @since       1.1.2
 * @package     Setcooki\Wp\Events
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
interface Listenable
{
	/**
	 * return array of listeners like:
	 * ```php
	 * class Foo implements Listenable
	 * {
	 *      public function listen()
	 *      {
	 *          return [
	 *              new Listener('event1', array($this, 'method1')),
	 *              new Listener('event2', array($this, 'method2'))),
	 *              ...
	 *          ];
	 *      }
	 * }
	 *
	 * @return array
	 */
	public function listen();
}