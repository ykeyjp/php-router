<?php
namespace ykey\router;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ykey\container\Container;
use ykey\router\strategy\JsonStrategy;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class CollectionTest extends TestCase
{
    public function testMatch()
    {
        $container = new Container;
        $route = new Collection($container);
        $route->map('GET', '/path1', null);
        $route->map('GET', '/path1/:name1', null);
        $route->map('GET', '/path1/:name1/:name2/...', null);
        $route->map(
            'GET',
            '/path2/:year/:month/:day',
            null);
        $request = (new ServerRequest)->withMethod('GET');
        $result = $route->match($request->withUri(new Uri('/path1')));
        $this->assertInstanceOf(Route::class, $result);
        $result = $route->match($request->withUri(new Uri('/path1/param1')));
        $this->assertInstanceOf(Route::class, $result);
        $this->assertArrayHasKey('name1', $result->getRouteParams());
        $result = $route->match($request->withUri(new Uri('/path1/param1/param2/param3')));
        $this->assertInstanceOf(Route::class, $result);
        $this->assertArrayHasKey('name1', $result->getRouteParams());
        $this->assertArrayHasKey('name2', $result->getRouteParams());
        $result = $route->match($request->withUri(new Uri('/path2/2017/04/30')));
        $this->assertInstanceOf(Route::class, $result);
        $this->assertArrayHasKey('year', $result->getRouteParams());
        $this->assertArrayHasKey('month', $result->getRouteParams());
        $this->assertArrayHasKey('day', $result->getRouteParams());
    }

    public function testGroupMatch()
    {
        $container = new Container;
        $route = new Collection($container);
        $route->group('/group', function (Collection $route) {
            $route->map('GET', '/path1', null);
            $route->map('GET', '/path1/:name1', null);
            $route->map('GET', '/path1/:name1/:name2/...', null);
            $route->group('/group2', function (Collection $route) {
                $route->map('GET', '/path1', null);
            });
        });
        $request = (new ServerRequest)->withMethod('GET');
        $result = $route->match($request->withUri(new Uri('/group/path1')));
        $this->assertInstanceOf(Route::class, $result);
        $result = $route->match($request->withUri(new Uri('/group/path1/param1')));
        $this->assertInstanceOf(Route::class, $result);
        $this->assertArrayHasKey('name1', $result->getRouteParams());
        $result = $route->match($request->withUri(new Uri('/group/path1/param1/param2/param3')));
        $this->assertInstanceOf(Route::class, $result);
        $this->assertArrayHasKey('name1', $result->getRouteParams());
        $this->assertArrayHasKey('name2', $result->getRouteParams());
        $result = $route->match($request->withUri(new Uri('/group/group2/path1')));
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testDispatch()
    {
        $container = new Container;
        $container->constraint(ClassController::class, []);
        $route = new Collection($container);
        $route->map('GET', '/path1', function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ): ResponseInterface {
            return $response->withStatus(101, 'function');
        });
        $route->map('GET', '/path2', new InvokeController);
        $route->map('GET', '/path3', ClassController::class . '::action');
        $request = (new ServerRequest)->withMethod('GET');
        $response = $route->dispatch($request->withUri(new Uri('/path1')), new Response);
        $this->assertEquals(101, $response->getStatusCode());
        $this->assertEquals('function', $response->getReasonPhrase());
        $response = $route->dispatch($request->withUri(new Uri('/path2')), new Response);
        $this->assertEquals(102, $response->getStatusCode());
        $this->assertEquals('invoker', $response->getReasonPhrase());
        $response = $route->dispatch($request->withUri(new Uri('/path3')), new Response);
        $this->assertEquals(103, $response->getStatusCode());
        $this->assertEquals('action', $response->getReasonPhrase());
    }

    public function testStrategy()
    {
        $container = new Container;
        $route = new Collection($container);
        $route->map('GET', '/path1', function ($req, ResponseInterface $res) {
            return ['item' => 'value'];
        })->setStrategy(new JsonStrategy());
        $request = (new ServerRequest)->withMethod('GET');
        $response = $route->dispatch($request->withUri(new Uri('/path1')), new Response);
        $response->getBody()->rewind();
        $json = $response->getBody()->getContents();
        $this->assertTrue($response->hasHeader('content-type'));
        $this->assertContains('application/json', $response->getHeader('content-type'));
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(['item' => 'value']), $json);
    }

    public function testMiddleware()
    {
        $container = new Container;
        $route = new Collection($container);
        $route->map('GET', '/path1', function ($req, ResponseInterface $res) {
            return ['item' => 'value'];
        })
            ->setStrategy(new JsonStrategy())
            ->middleware(function ($req, ResponseInterface $res, ?callable $next) {
                /* @var ResponseInterface $res */
                if ($next) {
                    $res = $next($req, $res);
                }

                return $res->withStatus(101, 'middleware');
            });
        $request = (new ServerRequest)->withMethod('GET');
        $response = $route->dispatch($request->withUri(new Uri('/path1')), new Response);
        $response->getBody()->rewind();
        $json = $response->getBody()->getContents();
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(['item' => 'value']), $json);
        $this->assertEquals(101, $response->getStatusCode());
        $this->assertEquals('middleware', $response->getReasonPhrase());
    }
}
