<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Exception;

/**
 * Trait Cache
 *
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Cache
{
	/**
	 * contains cache instance
	 *
	 * @var null|Cache
	 */
	public $cache = null;


	/**
	 * multifunctional cache interface must be called first with first argument = Cache instance. once set the following
	 * operations can be executed:
	 * - if no argument is passed returns cache instance
	 * - if only first argument is passed and value is boolean false or int -1 will purge cache instance
	 * - if only first and second argument are passed works as cache getter function where first argument is cache and second
	 *   argument is the default return value
	 * - if all arguments are passed works as cache setter where second argument is lifetime/expire value and third value
	 *   is the value to cache
	 * if function is called without cache instance set will return boolean false
	 *
	 * @param null|mixed $mixed1 expects value as explained in method signature
	 * @param null|mixed $mixed2 can be either default return value or lifetime value
	 * @param string|mixed $value expects the value to set in cache setter mode
	 * @return null|bool|mixed|Cache
	 */
	public function cache($mixed1 = null, $mixed2 = null, $value = '__NULL__')
	{
		if($mixed1 instanceof \Setcooki\Wp\Cache\Cache)
		{
			$this->cache = $mixed1;
		}
		if(!is_null($this->cache))
		{
			if(!is_null($mixed1) && setcooki_stringable($mixed1) && $value !== '__NULL')
			{
				return $this->cache->set((string)$mixed1, $value, $mixed2);
			}else if(!is_null($mixed1) && setcooki_stringable($mixed1) && $value === '__NULL'){
				return $this->cache->get((string)$mixed1, $mixed2);
			}else if(!is_null($mixed1) && ($mixed1 === false || $mixed1 === -1)){
				return $this->cache->purge();
			}else{
				return $this->cache;
			}
		}else{
			return false;
		}
	}
}