<?php

namespace Setcooki\Wp\Interfaces;

/**
 * Interface Logable
 *
 * @package     Setcooki\Wp\Interfaces
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
interface Logable
{
	/**
	 * psr-3 conform log interface with arbitrary level logging
	 *
	 * @since 1.1.3
	 * @param mixed $level expects the log level
	 * @param mixed $message expects the log message
	 * @param array $context expects additional args
	 * @return null
	 */
	public function log($level, $message, array $context = []);
}