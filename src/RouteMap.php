<?php
namespace ykey\router;

/**
 * Class RouteMap
 *
 * @package ykey\router
 */
class RouteMap
{
    /**
     * @var \ArrayObject
     */
    private $maps;

    /**
     * @var \SplStack
     */
    private $stackGroup;

    /**
     * RouteMap constructor.
     */
    public function __construct()
    {
        $this->maps = new \ArrayObject;
        $this->stackGroup = new \SplStack;
    }

    /**
     * @param string $base
     */
    public function startGroup(string $base): void
    {
        $parent = $this->stackGroup->isEmpty() ? null : $this->stackGroup->top();
        $this->stackGroup->push(new Group($base, $parent));
    }

    /**
     * @return Group
     */
    public function endGroup(): Group
    {
        return $this->stackGroup->pop();
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param        $handler
     *
     * @return Route
     */
    public function add(string $method, string $endpoint, $handler): Route
    {
        if (!$this->stackGroup->isEmpty()) {
            $endpoint = $this->stackGroup->top()->getBase() . $endpoint;
        }
        $paths = explode('/', trim($endpoint, '/'));
        $map = $this->maps;
        $params = [];
        foreach ($paths as $path) {
            if (substr($path, 0, 1) === ':') {
                $key = ':';
                $params[] = substr($path, 1);
            } elseif ($path === '...') {
                $key = '...';
            } else {
                $key = '/' . $path;
            }
            if (!isset($map[$key])) {
                $map[$key] = new \ArrayObject;
            }
            $map = $map[$key];
        }
        $route = new Route($method, $handler, $params);
        $map['@'] = $route;
        if (!$this->stackGroup->isEmpty()) {
            $route->setGroup($this->stackGroup->top());
        }

        return $route;
    }

    /**
     * @param string $method
     * @param string $endpoint
     *
     * @return Route
     *
     * @throws exception\MethodNotAllowedException
     * @throws exception\NotFoundException
     */
    public function match(string $method, string $endpoint): Route
    {
        $paths = explode('/', trim($endpoint, '/'));
        $map = $this->maps;
        $params = [];
        /* @var Route $route */
        $route = null;
        foreach ($paths as $path) {
            if (isset($map['/' . $path])) {
                $map = $map['/' . $path];
            } else {
                if (isset($map[':'])) {
                    $params[] = $this->castParamData($path);
                    $map = $map[':'];
                } elseif (isset($map['...'])) {
                    $map = $map['...'];
                }
            }
        }
        if (isset($map['@'])) {
            $route = clone $map['@'];
        }
        if (!$route) {
            throw new exception\NotFoundException;
        } elseif ($route->getMethod() !== $method) {
            $exception = new exception\MethodNotAllowedException;
            $exception->setRoute($route);
            throw $exception;
        }
        $routeParams = array_combine($route->getParams(), $params);
        $route->setRouteParams($routeParams);

        return $route;
    }

    /**
     * @param string $data
     *
     * @return float|int|string
     */
    private function castParamData(string $data)
    {
        if (is_numeric($data)) {
            if (strpos($data, '.') === false) {
                return intval($data);
            } else {
                return floatval($data);
            }
        }

        return $data;
    }
}
