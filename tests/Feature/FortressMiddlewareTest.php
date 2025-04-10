<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravelplus\Fortress\Adapters\DefaultAdapter;
use Laravelplus\Fortress\Contracts\PermissionAdapter;
use Laravelplus\Fortress\Contracts\RoleAdapter;
use Laravelplus\Fortress\Exceptions\AuthenticationException;
use Laravelplus\Fortress\Exceptions\UnauthorizedException;
use Laravelplus\Fortress\Fortress;
use Laravelplus\Fortress\Middleware\FortressMiddleware;
use Mockery;
use Tests\TestCase;

final class FortressMiddlewareTest extends TestCase
{
    private Fortress $fortress;
    private FortressMiddleware $middleware;
    private Authenticatable $user;
    private PermissionAdapter $permissionAdapter;
    private RoleAdapter $roleAdapter;
    private Request $request;
    private Cache $cache;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->cache = Mockery::mock(Cache::class);
        
        $this->config->shouldReceive('get')->with('fortress.cache.ttl', 3600)->andReturn(3600);
        $this->config->shouldReceive('get')->with('fortress.ownership_key', 'user_id')->andReturn('user_id');

        $this->fortress = new Fortress($this->config, $this->cache);
        $this->middleware = new FortressMiddleware($this->fortress);
        $this->request = Mockery::mock(Request::class);

        $this->permissionAdapter = Mockery::mock(PermissionAdapter::class);
        $this->roleAdapter = Mockery::mock(RoleAdapter::class);

        $this->fortress->setPermissionAdapter($this->permissionAdapter);
        $this->fortress->setRoleAdapter($this->roleAdapter);

        $this->user = $this->createUser();
    }

    private function createUser(): Authenticatable
    {
        return new class extends User {
            public function getAuthIdentifier(): int
            {
                return 1;
            }
        };
    }

    public function test_public_route_passes_without_authentication(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => true,
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn(null);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $response = $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_throws_authentication_exception_for_unauthenticated_user(): void
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn(null);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $this->expectException(AuthenticationException::class);

        $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });
    }

    public function test_throws_unauthorized_exception_for_invalid_roles(): void
    {
        $this->cache->shouldReceive('remember')
            ->with("fortress:auth:roles:1", 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(false);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'roles' => ['admin'],
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User does not have required roles');

        $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });
    }

    public function test_passes_with_valid_roles(): void
    {
        $this->cache->shouldReceive('remember')
            ->with("fortress:auth:roles:1", 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(true);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'roles' => ['admin'],
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $response = $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_throws_unauthorized_exception_for_invalid_permissions(): void
    {
        $this->cache->shouldReceive('remember')
            ->with("fortress:auth:permissions:1", 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(false);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'permissions' => ['create-post'],
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User does not have required permissions');

        $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });
    }

    public function test_passes_with_valid_permissions(): void
    {
        $this->cache->shouldReceive('remember')
            ->with("fortress:auth:permissions:1", 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(true);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'permissions' => ['create-post'],
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $response = $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_throws_unauthorized_exception_for_invalid_ownership(): void
    {
        $model = new class {
            public int $user_id = 2;
        };

        app()->instance('TestModel', $model);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'owner' => 'TestModel',
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User does not own this resource');

        $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });
    }

    public function test_passes_with_valid_ownership(): void
    {
        $model = new class {
            public int $user_id = 1;
        };

        app()->instance('TestModel', $model);

        $route = Mockery::mock(Route::class);
        $route->shouldReceive('getAction')
            ->with('fortress')
            ->andReturn([
                'public' => false,
                'owner' => 'TestModel',
            ]);

        $this->request->shouldReceive('route')->andReturn($route);
        $this->request->shouldReceive('user')->andReturn($this->user);
        $this->request->shouldReceive('expectsJson')->andReturn(false);

        $response = $this->middleware->handle($this->request, function ($request) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 