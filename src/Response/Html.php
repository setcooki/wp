<?php

namespace Setcooki\Wp\Response;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Response;

/**
 * Class Html
 *
 * @package     Setcooki\Wp\Response
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Html extends Response
{
	/**
	 * define default html headers values
	 *
	 * @param array $header expects header values
	 * @return array
	 */
	protected function header(&$header = [])
	{
		return array_merge($header, [
			'Content-Type' => 'text/html; charset=UTF-8'
		]);
	}
}