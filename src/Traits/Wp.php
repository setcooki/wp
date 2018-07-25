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
     * get the current wp framework instance. In order to change framework instance on run time the id of any instance
     * can be passed as optional parameter
     *
     * @since 1.2
     * @param null|false|string $id expects the optional id to set
     * @return null|\Setcooki\Wp\Wp
     * @throws \Exception
     */
    public function wp($id = false)
    {
        if($this->_wp === null)
        {
            $this->_wp = setcooki_wp();
        }
        if(!empty($id))
        {
            $this->_wp = setcooki_wp((string)$id);
        }
        if($id === null)
        {
            $this->_wp = setcooki_wp();
        }
        return $this->_wp;
    }
}
