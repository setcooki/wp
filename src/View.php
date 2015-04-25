<?php

namespace Setcooki\Wp;

/**
 * Class View
 * @package Setcooki\Wp
 */
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
     * class constructor sets controller and optional class options
     *
     * @param Controller $controller expects optional options
     * @param null $options
     */
    public function __construct(Controller $controller, $options = null)
    {
        setcooki_init_options($options, $this);
        $this->controller = $controller;
    }


    /**
     * @param Template $template
     * @return mixed
     */
    abstract public function render(Template $template);
}