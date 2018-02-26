<?php

namespace Setcooki\Wp\Events;

use Setcooki\Wp\Exception;
use Setcooki\Wp\Traits\Data;

/**
 * Class Event
 * @since 1.1.2
 * @package Setcooki\Wp\Events
 */
class Event
{
	use Data;

	/**
	 * propagation boolean flag
	 *
	 * @var bool
	 */
	private $_propagation = true;

	/**
	 * contains event name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * contains event subject/target
	 *
	 * @var null|mixed
	 */
	public $subject = null;


	/**
	 * generic event that can contain a subject which is the caller object and optional data.
	 *
	 * @param null|mixed $subject expects optional subject object
	 * @param null|mixed $data expects optional data to attach to event
	 * @param null|string $name expects optional event name
	 */
	public function __construct($subject = null, $data = null, $name = null)
	{
        $this->subject = $subject;
		$this->data = $data;
		$this->name = (string)$name;
    }


	/**
	 * create event by static access
	 *
	 * @param null|mixed $subject expects optional subject object
	 * @param null|mixed $data expects optional data to attach to event
	 * @param null|string $name expects optional event name
	 * @return Event
	 */
	public static function create($subject = null, $data = null, $name = null)
	{
		return new self($subject, $data, $name);
	}


	/**
	 * event name setter/getter
	 *
	 * @param null|string $name expects optional event name
	 * @return string
	 */
	public function name($name = null)
	{
		if(!is_null($name))
		{
			$this->name = (string)$name;
		}
		return $this->name;
	}


	/**
	 * event subject setter/getter
	 *
	 * @param null|mixed $subject expects optional subject
	 * @return mixed|null
	 */
	public function subject($subject = null)
	{
		if(!is_null($subject))
		{
			$this->subject = $subject;
		}
		return $this->subject;
	}


	/**
	 * returns boolean value for if the evnt is still propagating or not
	 *
	 * @return bool
	 */
	public function isPropagating()
	{
		return $this->_propagation;
	}


	/**
	 * stop propagation
	 */
	public function stopPropagation()
	{
		$this->_propagation = false;
	}


	/**
	 * on string cast return event name
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->name;
	}
}