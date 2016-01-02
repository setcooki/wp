<?php

namespace Setcooki\Wp\Response;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Response;

/**
 * Class Json
 * @package Setcooki\Wp\Response
 */
class Json extends Response
{
	/**
	 * json encode bitmask options
	 */
	const BITMASK_OPTIONS              = 'BITMASK_OPTIONS';


	/**
	 * encode response body to json
	 *
	 * @param string $body expects the response body string
	 * @return string
	 * @throws Exception
	 */
	protected function body($body = '')
	{
		$body = json_encode($body, (int)setcooki_get_option(self::BITMASK_OPTIONS, $this, 0));
		if($body !== false)
		{
			return $body;
		}else{
			$error = (int)json_last_error();
			switch($error)
			{
			    case JSON_ERROR_NONE:
				    $error = 'no errors';
					break;
			    case JSON_ERROR_DEPTH:
				    $error = 'maximum stack depth exceeded';
					break;
			   case JSON_ERROR_STATE_MISMATCH:
				    $error = 'underflow or the modes mismatch';
					break;
			    case JSON_ERROR_CTRL_CHAR:
				    $error = 'unexpected control character found';
					break;
			    case JSON_ERROR_SYNTAX:
				    $error = 'syntax error, malformed json';
					break;
			    case JSON_ERROR_UTF8:
				    $error = 'malformed utf-8 characters, possibly incorrectly encoded';
					break;
			    default:
				    $error = 'unknown error';
			}
			throw new Exception(setcooki_sprintf("json error: %s", $error));
		}
	}


	/**
	 * define default json headers values
	 *
	 * @param array $header expects header values
	 * @return array
	 */
	protected function header($header = array())
	{
		return array_merge($header, [
			'Content-Type' => 'application/json'
		]);
	}
}