<?php
namespace ykey\router;

/**
 * Trait MiddlewareTrait
 *
 * @package ykey\router
 */
trait MiddlewareTrait
{
    /**
     * @var callable[]
     */
    private $middlewares = [];

    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param callable $middleware
     */
    public function middleware(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }
}
