<?php

namespace Setcooki\Wp;;

/**
 * Class Controller
 * @package Setcooki\Wp
 */
abstract class Controller
{
    /**
     * @var null|Plugin
     */
    public $plugin = null;

    /**
     * @var null|Request
     */
    public $request = null;


    /**
     * class constructor set plugin and request instance
     *
     * @param Plugin $plugin
     * @param Request $request
     */
    public function __construct(Plugin &$plugin, Request $request = null)
    {
        $this->plugin = $plugin;
        if($request === null)
        {
            $request = new Request();
        }
        $this->request = $request;
    }


    /**
     * executes controller by running executing chain of init, action* and teardown method
     *
     * @param string $action
     * @return void
     */
    public function execute($action = 'action')
    {
        $this->init();
        $this->$action();
        $this->teardown();
    }


    /**
     * @return mixed
     */
    abstract public function init();


    /**
     * @return mixed
     */
    abstract public function action();


    /**
     * @return mixed
     */
    abstract public function teardown();
}