<?php
namespace ykey\router\exception;

use ykey\router\Route;

/**
 * Class MethodNotAllowedException
 *
 * @package ykey\router\exception
 */
class MethodNotAllowedException extends \Exception
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route): void
    {
        $this->route = $route;
    }
}
