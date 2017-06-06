<?php
namespace ykey\router\strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ykey\router\exception\MethodNotAllowedException;
use ykey\router\exception\NotFoundException;
use ykey\router\StrategyInterface;

/**
 * Class JsonStrategy
 *
 * @package ykey\router\strategy
 */
class JsonStrategy implements StrategyInterface
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
            $newResponse = $handler($request, $response, $args);
            if ($newResponse instanceof ResponseInterface) {
                $response = $newResponse;
            } else {
                $body = $response->getBody();
                $body->rewind();
                $body->write(json_encode($newResponse));
                $body->eof();
            }
            if ($next) {
                $response = $next($request, $response);
            }

            return $response->withAddedHeader('content-type', 'application/json');
        };
    }

    /**
     * @param NotFoundException $exception
     *
     * @return callable
     */
    public function getNotFoundMiddleware(
        NotFoundException $exception
    ): callable {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            if ($request->hasHeader('accept')) {
                in_array('application/json', $request->getHeader('accept'));
            }
            $body = $response->getBody();
            $body->rewind();
            $body->write(json_encode([
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]));
            $body->eof();

            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withStatus(404, 'not found.');
        };
    }

    /**
     * @param MethodNotAllowedException $exception
     *
     * @return callable
     */
    public function getMethodNotAllowedMiddleware(MethodNotAllowedException $exception): callable
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use ($exception) {
            if ($request->hasHeader('accept')) {
                in_array('application/json', $request->getHeader('accept'));
            }
            $body = $response->getBody();
            $body->rewind();
            $body->write(json_encode([
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]));
            $body->eof();

            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withStatus(405, 'method not allowed.');
        };
    }

    /**
     * @param \Exception $exception
     *
     * @return callable
     */
    public function getExceptionMiddleware(\Exception $exception): callable
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use ($exception) {
            if ($request->hasHeader('accept')) {
                in_array('application/json', $request->getHeader('accept'));
            }
            $body = $response->getBody();
            $body->rewind();
            $body->write(json_encode([
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]));
            $body->eof();

            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withStatus(500, $exception->getMessage());
        };
    }
}
