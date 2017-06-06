<?php
namespace ykey\router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait RouteMapTrait
 *
 * @package ykey\router
 */
trait RouteMapTrait
{
    /**
     * @var RouteMap
     */
    private $maps;

    /**
     * @param string   $base
     * @param callable $decorator
     *
     * @return Group
     */
    public function group(string $base, callable $decorator): Group
    {
        $this->maps->startGroup($base);
        $decorator($this);

        return $this->maps->endGroup();
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function map(string $method, string $endpoint, $handler): Route
    {
        return $this->maps->add($method, $endpoint, $handler);
    }

    /**
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function get(string $endpoint, $handler): Route
    {
        return $this->map('GET', $endpoint, $handler);
    }

    /**
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function post(string $endpoint, $handler): Route
    {
        return $this->map('POST', $endpoint, $handler);
    }

    /**
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function put(string $endpoint, $handler): Route
    {
        return $this->map('PUT', $endpoint, $handler);
    }

    /**
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function delete(string $endpoint, $handler): Route
    {
        return $this->map('DELETE', $endpoint, $handler);
    }

    /**
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function options(string $endpoint, $handler): Route
    {
        return $this->map('OPTIONS', $endpoint, $handler);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return null|Route
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        return $this->maps->match($request->getMethod(), $request->getUri()->getPath());
    }
}
