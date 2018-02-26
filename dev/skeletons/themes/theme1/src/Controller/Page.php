<?php

namespace Test\Theme1\Controller;

use Setcooki\Wp\Controller\View\View;
use Setcooki\Wp\Traits\Wp;
use Test\Theme1\Theme;

/**
 * Class Page
 * @package Test\Theme\Controller
 */
class Page extends Front
{
    /**
     * Header constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }


    /**
     * @return View
     * @throws \Exception
     */
    public function index()
    {
        $view = new View($this, "views/index.php");
        return $view;
    }


    /**
     * @return View
     * @throws \Exception
     */
    public function api()
    {
        $view = new View($this, "views/api.php");
        return $view;
    }
}