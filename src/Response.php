<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Traits\Factory;
use Setcooki\Wp\Traits\Data;

/**
 * Class Response
 * @package Setcooki\Wp
 */
class Response
{
	use Factory, Data;


	/**
	 * option to set http response version value
	 */
	const VERSION               = 'VERSION';

	/**
	 * option to copy headers from request by either passing a request instance or a boolean true value. defaults to
	 * boolean false
	 */
	const REQUEST               = 'REQUEST';

	/**
	 * array of all possible http response status codes
	 *
	 * @var array
	 */
	public static $status = array
	(
		100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
	);


	/**
	 * can contain key > value pairs of response headers
	 *
	 * @var array
	 */
	public $headers = array();


	/**
	 * option array
	 *
	 * @var array
	 */
	public $options = array
	(
		self::VERSION           => '1.0',
		self::REQUEST           => false
	);


	/**
	 * creates instance with optional options
	 *
	 * @param null|mixed $options expects optional class options
	 */
	public function __construct($options = null)
	{
		setcooki_init_options($options, $this);
	}


	/**
	 * generates response body which in response base class does nothing. this method needs to be used and overriden for
	 * concrete response classes that extend this class. the body thus can be manipulated before sending/flushing of response.
	 * e.g. in case of a json response class this method would return a json encoded string
	 *
	 * @param string $body expects the response body string
	 * @return string
	 */
	protected function body(&$body = '')
	{
		return $body;
	}


	/**
	 * add response headers as key => value pairs which in base class do not apply. this methods need to be used and overriden
	 * concrete response classes that extend this class. e.g. in case of a json response class this function would return an
	 * array of json response specific header values like "content-type" and such.
	 *
	 * @param array $header expects the the response headers
	 * @return array
	 */
	protected function header(&$header = array())
	{
		return $header;
	}


	/**
	 * add global response headers as key => value pairs
	 *
	 * @param array $headers expects response headers
	 * @return $this
	 */
	public function headers($headers = array())
	{
		foreach($headers as $key => $val)
		{
			$this->headers[trim((string)$key, ' :')] = (string)$val;
		}
		return $this;
	}


	/**
	 * simply "echo" output of response data - if necessary to send a complete http response use Response::send method.
	 * the first argument can be either:
	 * - NULL which expects that a string value has been set to response data
	 * - string that can be a data key (see Data trait) or any string value
	 * - any value that can be transformed/casted to string in Response::body method
	 * if the second argument is a string the response data will not be outputted to output stream - instead its concated
	 * to buffer variable and returned
	 *
	 * @param null|mixed $data expects data as explained in method signature
	 * @param null|string $buffer expects options return buffer
	 * @throws Exception
	 * @return $this|null|string
	 */
	public function flush($data = null, &$buffer = null)
	{
		if(is_null($data))
		{
			$data = $this->data();
		}else if(is_string($data)){
			$data = ($this->has($data)) ? $this->get($data, '') : $data;
		}

		$data = $this->body($data);

		if(setcooki_stringable($data))
		{
			$data = (string)$data;
		}else{
			throw new Exception("response data is not a string data type value");
		}

		//TODO: allow for response filters to manipulate response data
		$data = apply_filters('setcooki_response_data', $data);

		if($buffer !== null && is_string($buffer))
		{
			$buffer .= $data;
			return $buffer;
		}else{
			echo $data;
			return $this;
		}
	}


	/**
	 * set request object if not set in class options to copy request headers to response headers like content-type, charset
	 * and server protocol.
	 *
	 * @param Request|null $request expects request object
	 * @return $this
	 */
	public function request(Request $request = null)
	{
		if($request !== null)
		{
			setcooki_set_option(self::REQUEST, $request, $this);
		}
		if(setcooki_has_option(self::REQUEST, $this) && (bool)setcooki_get_option(self::REQUEST, $this))
		{
			$request = setcooki_get_option(self::REQUEST, $this);
			if(!($request instanceof Request))
			{
				$request = new Request();
			}
			$content_type = $request->getContentType();
			if(!empty($content_type))
			{
				$charset = $request->getCharset();
				if(!empty($charset))
				{
					$this->headers(array('Content-Type' => setcooki_sprintf("%s; charset=%s", array(strtolower(trim((string)$content_type)), strtolower(trim((string)$charset))))));
				}else{
					$this->headers(array('Content-Type' => strtolower(trim((string)$content_type))));
				}
			}
			$protocol = $request->get('SERVER_PROTOCOL', 'SERVER', '');
			if(!empty($protocol) && preg_match('=HTTP\/([0-9]\.[0-9])=i', $protocol, $match))
			{
				setcooki_set_option(self::VERSION, $match[1], $this);
			}
		}
		return $this;
	}


	/**
	 * send http response according to method arguments where first argument is the response data value which can be any
	 * value that is valid - see Response::flush method. the second argument defines the http response status value. the
	 * third argument can be any array of response header key => value pairs. NOTE: there are three options that headers
	 * value are set - the order is:
	 * - see Response::header function where header values come from concrete response class
	 * - the header values set with Response::headers
	 * - the headers passed in third argument
	 *
	 * @see Response::flush
	 * @param null|mixed $data expects data - see Response::flush
	 * @param int $status expects the http response status code
	 * @param array $headers expects optional response header key => value pairs
	 * @return $this
	 * @throws Exception
	 */
	public function send($data = null, $status = 200, $headers = array())
	{
		$buffer = '';
		$cache = array();

		$this->flush($data, $buffer);

		if(!headers_sent())
		{
			$this->request();

			$version = setcooki_get_option(self::VERSION, $this);
			$headers = array_merge((array)$this->header(), $this->headers, $headers);
			$status_text = (isset(self::$status[(int)$status])) ? self::$status[(int)$status] : 'unknown status';
			foreach($headers as $key => $val)
			{
				$key = trim((string)$key, ' :');
				$cache[] = strtolower(trim($key));
				header($key . ': ' . (string)$val, false, (int)$status);
			}
			header(vsprintf('HTTP/%s %s %s', array(
				(string)$version,
				(string)$status,
				(string)$status_text
			)), true, (int)$status);

			if(ob_get_contents() === '' && (!in_array('content-length', $cache) || !in_array('transfer-encoding', $cache)))
			{
				header("Content-Length: " . strlen($buffer));
			}
		}

		$buffer = apply_filters('setcooki_response_body', $buffer);

		echo $buffer;

	    if(function_exists('fastcgi_finish_request'))
	    {
		    fastcgi_finish_request();
	    }else if(PHP_SAPI !== 'cli'){
			if(count(ob_get_status(true)) > 0)
			{
				ob_end_flush();
			}
	    }
	    return $this;
	}


	/**
	 * send response to string instead of outputting response. see Response::send for further explanation
	 *
	 * @see Response::send
	 * @param null|mixed $data expects data - see Response::flush
	 * @param int $status expects the http response status code
	 * @param array $headers expects optional response header key => value pairs
	 * @return string
	 * @throws Exception
	 */
	public function sendToString($data = null, $status = 200, $headers = array())
    {
	    $header = '';
	    $buffer = '';
	    $cache = array();

	    $this->request();

	    $version            = setcooki_get_option(self::VERSION, $this);
	    $status_text        = (isset(self::$status[(int)$status])) ? self::$status[(int)$status] : 'unknown status';
	    $headers            = array_merge((array)$this->header(), $this->headers, $headers);

	    $this->flush($data, $buffer);

	    $buffer = apply_filters('setcooki_response_body', $buffer);

	    foreach($headers as $key => $val)
        {
            $key = trim((string)$key, ' :');
            $cache[] = strtolower($key);
	        $header .= $key . ': ' . (string)$val . "\n";
        }
	    if(ob_get_contents() === '' && (!in_array('content-length', $cache) || !in_array('transfer-encoding', $cache)))
	    {
	 	    header("Content-Length: " . strlen($buffer));
	 	}

        return
            sprintf('HTTP/%s %s %s', (string)$version, (string)$status, $status_text)."\r\n".
            $header."\r\n".
            $buffer;
    }


	/**
	 * static function to send a response on one shot. see Response::send for further explanation
	 *
	 * @see Response::send
	 * @param null|mixed $data expects data - see Response::flush
	 * @param int $status expects the http response status code
	 * @param array $headers expects optional response header key => value pairs
	 * @return $this
	 */
	public static function s($data, $status = 200, $headers = array())
	{
		return (new Response())->send($data, $status, $headers);
	}


	/**
	 * quit program after calling options callback
	 *
	 * @param null|callable $callback expects optional callback
	 */
	public function quit($callback = null)
	{
		if(!is_null($callback) && is_callable($callback))
		{
			call_user_func($callback, $this);
		}
		exit(0);
	}
}