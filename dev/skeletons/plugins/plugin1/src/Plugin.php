<?php

namespace Test\Plugin1;

/**
 * Class Plugin
 * @package Test\Plugin1
 */
class Plugin extends \Setcooki\Wp\Plugin
{
	/**
    * @param null $options
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
    }


	/**
    * @return mixed
    */
    public function activate()
    {

    }


    /**
    * @return mixed
    */
	public function deactivate()
	{

	}


    /**
    * @return mixed
    */
    public function uninstall()
    {

    }
}