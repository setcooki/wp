<?php

namespace Setcooki\Wp\Response;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Response;

/**
 * Class Html
 * @package Setcooki\Wp\Response
 */
class Html extends Response
{
	/**
	 * define default html headers values
	 *
	 * @param array $header expects header values
	 * @return array
	 */
	protected function header($header = array())
	{
		return array_merge($header, [
			'Content-Type' => 'text/html'
		]);
	}
}