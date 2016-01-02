<?php

namespace Setcooki\Wp;

/**
 * Class Theme
 * @package Setcooki\Wp
 */
abstract class Theme extends Wp
{
    /**
     * option to register auto theme support
     */
    const THEME_SUPPORT                 = 'THEME_SUPPORT';


    /**
     * @var array
     */
    public $options = array();


    /**
     * @var null
     */
    protected static $_instance = null;


    /**
     * init theme and set action hooks
     *
     * @param null|mixed $options expects optional class options
     * @throws Exception
     */
    protected function __construct($options = null)
    {
        $class = get_called_class();
        setcooki_init_options($options, $this);
        add_action('after_setup_theme', array($class, '_afterSetup'));
        add_action('after_switch_theme', array($class, '_afterSwitch'));
        add_action('switch_theme', array($class, '_switchTheme'));
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
    public static function _switchTheme()
    {
        self::instance()->switchTheme();
    }


    /**
     * @return void
     * @throws Exception
     */
    public static function _afterSetup()
    {
        $self = self::instance();

        if(setcooki_has_option(self::THEME_SUPPORT, $self))
        {
            foreach((array)setcooki_get_option(self::THEME_SUPPORT, $self) as $option)
            {
                if(add_theme_support((string)$option) === false)
                {
                    throw new Exception(setcooki_sprintf("unable to set theme support value: %s in theme init", $option));
                }
            }
        }
        self::instance()->afterSetup();
    }


    /**
     * @return void
     */
    public static function _afterSwitch()
    {
        self::instance()->afterSwitch();
    }


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