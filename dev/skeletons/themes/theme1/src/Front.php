<?php

namespace Test\Theme1;

use Setcooki\Wp\Action\Action;
use Setcooki\Wp\Content\Document\Html;
use Setcooki\Wp\Traits\Singleton;
use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Routing\Route;
use Setcooki\Wp\Routing\Router;
use Test\Theme1\Controller\Ajax;
use Test\Theme1\Controller\Footer;
use Test\Theme1\Controller\Header;
use Test\Theme1\Controller\Index;
use Test\Theme1\Controller\Page;


/**
 * Class Front
 * @package Test\Theme1
 */
class Front
{
    use Singleton;


    /**
     * Front constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param Theme $theme
     * @return Singleton
     * @throws \Exception
     * @throws \Setcooki\Wp\Exception
     */
    public static function init(Theme $theme)
    {
        $front = self::instance($theme);

        $html = new Html();
        $html->init();
        $theme->store('document', $html);

        Resolver::create()
            ->register(new Index())
            ->register(new Header())
            ->register(new Footer())
            ->register(new Page())
            ->register(new Ajax())
            ->register(new \Test\Theme1\Controller\Tests\Controller());

        $router = Router::create()
            ->add(new Route('url:\/tests\/controller\/.*', '\Test\Theme1\Controller\Tests\Controller::tests'))
            ->add(new Route('url:\/index', '\Test\Theme1\Controller\Index::index'))
            ->add(new Route('url:\/api', 'Page::api'))
            ->add(new Route('url:\/?', 'Page::index'));

        return $front;
    }
}