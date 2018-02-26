<?php

namespace Test\Theme1\Controller;

use Setcooki\Wp\Controller\View\View;
use Test\Theme1\Theme;

/**
 * Class Index
 * @package Test\Theme1\Controller
 */
class Index extends Front
{
    /**
     * Index constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }


    public function index()
    {
        $view = new View($this, "views/index.php");
        return $view;
    }
}