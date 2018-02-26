<?php

namespace Test\Theme1;

use Setcooki\Wp\Traits\Singleton;

/**
 * Class Admin
 * @package Test\Theme1
 */
class Admin
{
    use Singleton;

    /**
     * Admin constructor.
     * @param Theme $theme
     * @param null $options
     */
    public function __construct()
    {
    }


    /**
     * @param Theme $theme
     * @return Singleton
     */
    public static function init(Theme $theme)
    {
        $admin = self::instance($theme);

        return $admin;
    }
}