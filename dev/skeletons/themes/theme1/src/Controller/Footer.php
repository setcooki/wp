<?php

namespace Test\Theme1\Controller;

use Setcooki\Wp\Controller\View\View;
use Test\Theme1\Theme;

/**
 * Class Footer
 * @package Test\Theme1\Controller
 */
class Footer extends Front
{
    /**
     * Footer constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }


    /**
     *
     */
    public function action()
    {
        $view = new View($this, 'partials/footer.php');
        return $view;
    }
}