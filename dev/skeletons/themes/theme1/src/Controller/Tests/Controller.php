<?php

namespace Test\Theme1\Controller\Tests;

use Setcooki\Wp\Request;
use Setcooki\Wp\Util\Params;
use Test\Theme1\Controller\Front;

/**
 * Class Footer
 * @package Test\Theme1\Controller
 */
class Controller extends Front
{
    /**
     * @param Params $params
     * @param Request $request
     * @return mixed
     * @throws \Exception
     * @throws \Setcooki\Wp\Exception
     */
    public function tests(Params $params, Request $request)
    {
        return $this->forwardByPath($request, $params, $request);
    }


    /**
     * @param Params $params
     * @param Request $request
     * @return string
     */
    public function front(Params $params, Request $request)
    {
        return '';
    }
}