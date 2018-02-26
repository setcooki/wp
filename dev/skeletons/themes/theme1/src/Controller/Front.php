<?php

namespace Test\Theme1\Controller;

use Setcooki\Wp\Theme;

abstract class Front extends \Setcooki\Wp\Controller\Front
{
    /**
     * Front constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }
}