<?php

namespace Test\Theme1\Controller;

use Setcooki\Wp\Controller\View\View;
use Test\Theme1\Theme;

/**
 * Class Header
 * @package Test\Theme1\Controller
 */
class Header extends Front
{
    /**
     * Header constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }


    /**
     *
     */
    public function action()
    {
        $view = new View($this, 'partials/header.php');
        return $view;
    }


    /**
     * @return View
     */
    public function mainnav()
    {
        $view = new View($this, 'partials/mainnav.php');
        return $view;
    }
}