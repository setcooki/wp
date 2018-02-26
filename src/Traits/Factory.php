<?php

namespace Setcooki\Wp\Traits;

use Setcooki\Wp\Exception;

/**
 * Trait Factory
 *
 * @package     Setcooki\Wp\Traits
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
trait Factory
{
    /**
     * factory create sub class of get called class when using static factory create function expecting string class name
     * of subclass in first argument and optional options in second argument
     *
     * @param string $name expects subclass name
     * @param null|mixed $options expects optional options
     * @return mixed
     * @throws Exception
     */
    public static function create($name, $options = null)
    {
        $class = get_called_class();
        $class = $class . NAMESPACE_SEPARATOR . ltrim(ucfirst((string)$name), NAMESPACE_SEPARATOR);
        if(class_exists($class, true))
        {
            return new $class($options);
        }else{
            throw new Exception(setcooki_sprintf(__("Factory unable to create class: %s", SETCOOKI_WP_DOMAIN), $class));
        }
    }
}