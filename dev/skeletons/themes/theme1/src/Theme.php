<?php

namespace Test\Theme1;

/**
 * Class Theme
 * @package Test\Theme
 */
class Theme extends \Setcooki\Wp\Theme
{
    /**
     * @var null
     */
    public $front = null;

    /**
     * @var null
     */
    public $admin = null;


    /**
     * Theme constructor.
     * @param null $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
	    parent::__construct($options);
    }

    /**
     *
     */
    public function init()
    {
        $this->front = Front::init($this);
        $this->admin = Admin::init($this);
    }

    /**
     *
     */
    public function switchTheme()
    {
    }

    /**
     *
     */
    public function afterSetup()
    {
    }

    /**
     *
     */
    public function afterSwitch()
    {
    }
}