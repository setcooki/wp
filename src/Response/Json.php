<?php

namespace Setcooki\Wp\Response;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Response;

/**
 * Class Json
 *
 * @package     Setcooki\Wp\Response
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Json extends Response
{
	/**
	 * json encode bitmask options
	 */
	const BITMASK_OPTIONS               = 'BITMASK_OPTIONS';

    /**
     * response call back before data is encoded to json
     */
	const RESPONSE_CALLBACK             = 'RESPONSE_CALLBACK';


    /**
     * class options map
     *
     * @var array
     */
    public static $optionsMap = array
    (
        self::BITMASK_OPTIONS           => SETCOOKI_TYPE_INT,
        self::RESPONSE_CALLBACK         => [SETCOOKI_TYPE_CALLABLE, SETCOOKI_TYPE_NULL]
    );


    /**
     * class options
     *
     * @var array
     */
	public $options = array
    (
        self::BITMASK_OPTIONS           => 0
    );


    /**
	 * encode response body to json
	 *
	 * @param string $body expects the response body string
	 * @return string
     * @throws \Exception
	 */
	protected function body(&$body = '')
	{
	    if(($callback = setcooki_get_option(self::RESPONSE_CALLBACK, $this)) !== null && is_callable($callback))
        {
            $body = call_user_func($callback, $body);
        }
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
			throw new Exception(setcooki_sprintf(__("Json error: %s", SETCOOKI_WP_DOMAIN), $error));
		}
	}


	/**
	 * define default json headers values
	 *
	 * @param array $header expects header values
	 * @return array
	 */
	protected function header(&$header = [])
	{
		return array_merge($header, [
			'Content-Type' => 'application/json'
		]);
	}


    /**
     * return json encoded error object
     *
     * @since 1.2
     * @param string $error expects the error string
     * @return mixed|string
     */
	protected function error($error)
    {
        return json_encode(['success' => 0, 'error' => $error], (int)setcooki_get_option(self::BITMASK_OPTIONS, $this, 0));
    }
}
