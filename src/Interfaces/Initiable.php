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
     * @param Wp $wp
     */
    public static function init(Wp $wp);
}