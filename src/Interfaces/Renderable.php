<?php

namespace Setcooki\Wp\Interfaces;

/**
 * Interface Renderable
 *
 * @package     Setcooki\Wp\Interfaces
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
interface Renderable
{
    /**
     * render something and return/echo rendered content
     */
    public function render();
}