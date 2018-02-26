<?php

namespace Setcooki\Wp\Response;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Response;

/**
 * Class Xml
 *
 * @package     Setcooki\Wp\Response
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Xml extends Response
{
	/**
	 * define default xml headers values
	 *
	 * @param array $header expects header values
	 * @return array
	 */
	protected function header(&$header = [])
	{
		return array_merge($header, [
			'Content-Type' => 'application/xml'
		]);
	}
}