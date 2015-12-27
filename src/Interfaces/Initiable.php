<?php

namespace Setcooki\Wp\Interfaces;

use Setcooki\Wp\Wp;

/**
 * Interface Initiable
 * @package Setcooki\Wp\Interfaces
 */
interface Initiable
{
    /**
     * init a class with wp instance
     *
     * @param Wp $wp expects instance of wp
     * @param mixed $options expects optional options
     */
    public static function init(Wp &$wp, $options = null);
}