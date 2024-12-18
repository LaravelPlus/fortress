<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravelplus\Fortress\Middleware\AttributeAuthorizationMiddleware;
use Mockery;
use Tests\TestCase;

final class AttributeAuthorizationMiddlewareTest extends TestCase
{
    private AttributeAuthorizationMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AttributeAuthorizationMiddleware();
    }

    public function test_handle_without_authorize_attribute(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getController')->andReturn(new TestControllerWithoutAuthorize());
        $route->shouldReceive('getActionMethod')->andReturn('testMethod');

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('route')->andReturn($route);

        $next = fn ($req) => 'next-called';

        $result = $this->middleware->handle($request, $next);

        $this->assertEquals('next-called', $result);
    }

    public function test_handle_with_valid_authorize_attribute(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getController')->andReturn(new TestControllerWithAuthorize());
        $route->shouldReceive('getActionMethod')->andReturn('testMethod');

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('route')->andReturn($route);

        $next = fn ($req) => 'next-called';

        $result = $this->middleware->handle($request, $next);

        $this->assertEquals('next-called', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
