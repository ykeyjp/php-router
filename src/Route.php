<?php
namespace ykey\router;

/**
 * Class Route
 *
 * @package ykey\router
 */
class Route
{
    use StrategyTrait;
    use MiddlewareTrait;
    /**
     * @var string
     */
    private $method;
    /**
     * @var null|callable|string
     */
    private $handler;
    /**
     * @var string[]
     */
    private $params;
    /**
     * @var array
     */
    private $routeParams = [];
    /**
     * @var null|Group
     */
    private $group;

    /**
     * Route constructor.
     *
     * @param string $method
     * @param        $handler
     * @param array  $params
     */
    public function __construct(string $method, $handler, array $params = [])
    {
        $this->method = $method;
        $this->handler = $handler;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return callable|null|string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @param array $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * @return null|Group
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
}
