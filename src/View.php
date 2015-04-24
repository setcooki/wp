<?php

namespace Setcooki\Wp;

use Setcooki\Wp\Controller;
use Setcooki\Wp\Template;

abstract class View
{
    /**
     * @var null|Controller
     */
    public $controller = null;

    /**
     * @var array
     */
    public $options = array();


    /**
     * @param Template $template
     * @return mixed
     */
    abstract public function render(Template $template);


    /**
     * @param Controller $controller
     * @param null $options
     */
    public function __construct(Controller $controller, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->controller = $controller;
    }
}