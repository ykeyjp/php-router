<?php
namespace ykey\router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ykey\middleware\Stream;
use ykey\router\exception\MethodNotAllowedException;
use ykey\router\exception\NotFoundException;
use ykey\router\strategy\ApplicationStrategy;

/**
 * Class Collection
 *
 * @package ykey\router
 */
class Collection
{
    use RouteMapTrait;
    use StrategyTrait;
    use MiddlewareTrait;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Collection constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->maps = new RouteMap;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $route = $this->match($request);
            $dispatcher = $this->createDispatcher($route);
            $stream = Stream::create();
            foreach ($this->fetchMiddlewares() as $middleware) {
                $stream = $stream->pipe($middleware);
            }
            $stream = $stream->pipe($dispatcher);
            foreach ($route->getMiddlewares() as $middleware) {
                /* @var Stream $stream */
                $stream = $stream->pipe($middleware);
            }

            return $stream($request, $response);
        } catch (MethodNotAllowedException $exception) {
            if ($strategy = $this->fetchStrategy($exception->getRoute())) {
                $middleware = $strategy->getMethodNotAllowedMiddleware($exception);

                return $middleware($request, $response);
            }
        } catch (NotFoundException $exception) {
            if ($strategy = $this->fetchStrategy()) {
                $middleware = $strategy->getNotFoundMiddleware($exception);

                return $middleware($request, $response);
            }
        }

        return $response;
    }

    /**
     * @param Route $route
     *
     * @return callable
     */
    private function createDispatcher(Route $route): callable
    {
        $handler = $route->getHandler();
        if (is_string($handler) && strpos($handler, '::') !== false) {
            list($className, $actionName) = explode('::', $handler);
            if ($this->container->has($className)) {
                $handler = [$this->container->get($className), $actionName];
            }
        }
        if (!($strategy = $this->fetchStrategy($route))) {
            $strategy = new ApplicationStrategy;
        }

        return $strategy->getMiddleware($handler, $route->getRouteParams());
    }

    /**
     * @param null|Route $route
     *
     * @return null|StrategyInterface
     */
    private function fetchStrategy(?Route $route = null): ?StrategyInterface
    {
        if ($route) {
            if ($strategy = $route->getStrategy()) {
                return $strategy;
            }
            if ($group = $route->getGroup() and $strategy = $group->getStrategy()) {
                return $strategy;
            }
        }

        return $this->getStrategy();
    }

    /**
     * @param null|Route $route
     *
     * @return callable[]
     */
    private function fetchMiddlewares(?Route $route = null): array
    {
        $middlewares = $this->getMiddlewares();
        if ($route and $group = $route->getGroup()) {
            $middlewares += $group->getMiddlewares();
        }

        return $middlewares;
    }
}
