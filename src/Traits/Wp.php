<?php

namespace Setcooki\Wp\Traits;


/**
 * Trait Wp
 *
 * @since       1.2
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Wp
{
    /**
     * contains wp instance
     *
     * @var null|\Setcooki\Wp\Wp
     */
    private $_wp = null;


    /**
     * get the current wp framework instance
     *
     * @since 1.2
     * @return null|\Setcooki\Wp\Wp
     * @throws \Exception
     */
    public function wp()
    {
        if($this->_wp === null)
        {
            $this->_wp = setcooki_wp();
        }
        return $this->_wp;
    }
}
