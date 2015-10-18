<?php

namespace Setcooki\Wp\Interfaces;

/**
 * Interface Renderable
 * @package Setcooki\Wp\Interfaces
 */
interface Renderable
{
    /**
     * render something and return/echo rendered content
     *
     * @return mixed
     */
    public function render();
}