<?php
namespace ykey\router\strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ykey\router\exception\MethodNotAllowedException;
use ykey\router\exception\NotFoundException;
use ykey\router\StrategyInterface;

/**
 * Class ApplicationStrategy
 *
 * @package ykey\router\strategy
 */
class ApplicationStrategy implements StrategyInterface
{
    /**
     * @param callable $handler
     * @param array    $args
     *
     * @return callable
     */
    public function getMiddleware(callable $handler, array $args): callable
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            ?callable $next
        ) use (
            $handler,
            $args
        ) {
            $response = $handler($request, $response, $args);
            if (!($response instanceof ResponseInterface)) {
                throw new \RuntimeException('must return an instance of [Psr\Http\Message\ResponseInterface]');
            }
            if ($next) {
                $response = $next($request, $response);
            }

            return $response;
        };
    }

    /**
     * @param NotFoundException $exception
     *
     * @return callable
     */
    public function getNotFoundMiddleware(NotFoundException $exception): callable
    {
        return function () use ($exception) {
            throw $exception;
        };
    }

    /**
     * @param MethodNotAllowedException $exception
     *
     * @return callable
     */
    public function getMethodNotAllowedMiddleware(MethodNotAllowedException $exception): callable
    {
        return function () use ($exception) {
            throw $exception;
        };
    }

    /**
     * @param \Exception $exception
     *
     * @return callable
     */
    public function getExceptionMiddleware(\Exception $exception): callable
    {
        return function () use ($exception) {
            throw $exception;
        };
    }
}
