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
        setcooki_init_options($options, $this);
        add_action('after_setup_theme', array($this, '_afterSetup'));
        add_action('after_switch_theme', array($this, '_afterSwitch'));
        add_action('switch_theme', array($this, '_switchTheme'));

        parent::__construct();
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
     * check if theme is instantiated static
     *
     * @since 1.1.2
     * @return bool
     */
    public static function hasInstance()
    {
        return (!is_null(self::$_instance)) ? true : false;
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
    public function _switchTheme()
    {
        $this->switchTheme();
    }


    /**
     * @return void
     * @throws Exception
     */
    public function _afterSetup()
    {
        if(setcooki_has_option(self::THEME_SUPPORT, $this))
        {
            foreach((array)setcooki_get_option(self::THEME_SUPPORT, $this) as $option)
            {
                if(add_theme_support((string)$option) === false)
                {
                    throw new Exception(setcooki_sprintf("unable to set theme support value: %s in theme init", $option));
                }
            }
        }
        $this->afterSetup();
    }


    /**
     * @return void
     */
    public function _afterSwitch()
    {
        $this->afterSwitch();
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