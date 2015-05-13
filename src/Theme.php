<?php

namespace Setcooki\Wp;

/**
 * Class Theme
 * @package Setcooki\Wp
 */
abstract class Theme
{
    /**
     * @var null
     */
    protected static $_instance = null;


    /**
     * init theme and set action hooks
     *
     * @param null⁄mixed $options expects optional class options
     */
    protected function __construct($options = null)
    {
        setcooki_init_options($options, $this);
        add_action('after_setup_theme', array($this, 'afterSetup'));
        add_action('after_switch_theme', array($this, 'afterSwitch'));
        add_action('switch_theme', array($this, 'switchTheme'));
    }


    /**
     * static class singleton instance setter/getter.
     *
     * @see Setcooki\Wp\Theme::__construct
     * @param null⁄mixed $options expects optional class options
     * @return null|Theme
     */
    public static function instance($options = null)
    {
        $class = get_called_class();

        if(self::$_instance === null)
        {
            self::$_instance = new $class($options);
        }
        return self::$_instance;
    }


    /**
     * shortcut method for init method
     *
     * @return void
     */
    public function setup()
    {
        $this->init();
    }


    /**
     * @return void
     */
    abstract public function init();


    /**
     * @return void
     */
    abstract public function switchTheme();


    /**
     * @return void
     */
    abstract public function afterSetup();


    /**
     * @return void
     */
    abstract public function afterSwitch();
}