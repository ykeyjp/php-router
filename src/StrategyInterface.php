<?php
namespace ykey\router;

use ykey\router\exception\MethodNotAllowedException;
use ykey\router\exception\NotFoundException;

/**
 * Interface StrategyInterface
 *
 * @package ykey\router
 */
interface StrategyInterface
{
    /**
     * @param callable $delegate
     * @param array    $args
     *
     * @return callable
     */
    public function getMiddleware(callable $delegate, array $args): callable;

    /**
     * @param NotFoundException $exception
     *
     * @return callable
     */
    public function getNotFoundMiddleware(NotFoundException $exception): callable;

    /**
     * @param MethodNotAllowedException $exception
     *
     * @return callable
     */
    public function getMethodNotAllowedMiddleware(MethodNotAllowedException $exception): callable;

    /**
     * @param \Exception $exception
     *
     * @return callable
     */
    public function getExceptionMiddleware(\Exception $exception): callable;
}
