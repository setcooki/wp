<?php

namespace Setcooki\Wp\Controller\Filter;

use Setcooki\Wp\Controller\Resolver;
use Setcooki\Wp\Request;
use Setcooki\Wp\Response;

/**
 * Interface Filterable
 *
 * @package     Setcooki\Wp\Controller
 * @subpackage  Setcooki\Wp\Controller\Filter
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
Interface Filterable
{
	/**
	 * controller filter interface
	 *
	 * @param Resolver $resolver expects the resolver object
	 * @param Request $request expects request object
	 * @param Response $response expects response object
	 * @param null|array $params expects optional parameters
	 * @return mixed
	 */
	public function execute(Resolver $resolver, Request $request, Response $response, $params = null);
}