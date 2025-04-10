<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Laravelplus\Fortress\Adapters\DefaultAdapter;
use Laravelplus\Fortress\Contracts\PermissionAdapter;
use Laravelplus\Fortress\Contracts\RoleAdapter;
use Laravelplus\Fortress\Exceptions\AuthenticationException;
use Laravelplus\Fortress\Exceptions\InvalidConfigurationException;
use Laravelplus\Fortress\Exceptions\UnauthorizedException;
use Laravelplus\Fortress\Fortress;
use Mockery;
use Tests\TestCase;

final class FortressTest extends TestCase
{
    private Config $config;
    private Cache $cache;
    private Authenticatable $user;
    private PermissionAdapter $permissionAdapter;
    private RoleAdapter $roleAdapter;
    private Fortress $fortress;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->cache = Mockery::mock(Cache::class);
        $this->user = Mockery::mock(Authenticatable::class);
        $this->permissionAdapter = Mockery::mock(PermissionAdapter::class);
        $this->roleAdapter = Mockery::mock(RoleAdapter::class);

        $this->user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->user->id = 1;

        $this->config->shouldReceive('get')->with('fortress.ownership_key', 'user_id')->andReturn('user_id');
        $this->config->shouldReceive('get')->with('fortress.cache.ttl', 3600)->andReturn(3600);

        $this->user->shouldReceive('hasAnyRole')->byDefault()->andReturn(false);
        $this->user->shouldReceive('hasPermissionTo')->byDefault()->andReturn(false);
        
        $this->fortress = new Fortress($this->config, $this->cache);
        $this->fortress->setPermissionAdapter($this->permissionAdapter);
        $this->fortress->setRoleAdapter($this->roleAdapter);
    }

    public function test_authorize_throws_authentication_exception_for_unauthenticated_user(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->fortress->authorize(null);
    }

    public function test_authorize_passes_public_routes(): void
    {
        $this->fortress->authorize($this->user, public: true);

        $this->assertTrue(true); // No exception thrown
    }

    public function test_authorize_throws_unauthorized_exception_for_invalid_roles(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:roles:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(false);

        $this->fortress->authorize($this->user, roles: ['admin']);
    }

    public function test_authorize_passes_with_valid_roles(): void
    {
        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:roles:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(true);

        $this->fortress->authorize($this->user, roles: ['admin']);

        $this->assertTrue(true); // No exception thrown
    }

    public function test_authorize_throws_unauthorized_exception_for_invalid_permissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:permissions:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(false);

        $this->fortress->authorize($this->user, permissions: 'create-post');
    }

    public function test_authorize_passes_with_valid_permissions(): void
    {
        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:permissions:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(true);

        $this->fortress->authorize($this->user, permissions: 'create-post');

        $this->assertTrue(true); // No exception thrown
    }

    public function test_authorize_throws_unauthorized_exception_for_invalid_ownership(): void
    {
        $this->expectException(UnauthorizedException::class);

        $model = new class {
            public int $user_id = 2;
        };

        app()->instance('TestModel', $model);

        $this->fortress->authorize($this->user, owner: 'TestModel');
    }

    public function test_authorize_passes_with_valid_ownership(): void
    {
        $model = new class {
            public int $user_id = 1;
        };

        app()->instance('TestModel', $model);

        $this->fortress->authorize($this->user, owner: 'TestModel');

        $this->assertTrue(true); // No exception thrown
    }

    public function test_authorize_throws_invalid_configuration_exception_for_invalid_owner_key(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $model = new class {
            public int $user_id = 1;
        };

        app()->instance('TestModel', $model);

        $this->fortress->authorize($this->user, owner: 'TestModel', overrideKey: 'invalid_key');
    }

    public function test_authorize_checks_roles_with_caching(): void
    {
        $roles = ['admin'];
        
        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:roles:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $this->roleAdapter->shouldReceive('hasAnyRole')
            ->once()
            ->with($roles)
            ->andReturn(true);
            
        $result = $this->fortress->authorize($this->user, roles: $roles);
        $this->assertTrue($result);
    }

    public function test_authorize_checks_permissions_with_caching(): void
    {
        $permissions = 'create-post';
        
        $this->cache->shouldReceive('remember')
            ->once()
            ->with('fortress:auth:permissions:1', 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $this->permissionAdapter->shouldReceive('hasAnyPermission')
            ->once()
            ->with([$permissions])
            ->andReturn(true);
            
        $result = $this->fortress->authorize($this->user, permissions: $permissions);
        $this->assertTrue($result);
    }

    public function test_batch_authorize_handles_multiple_resources(): void
    {
        $resources = [
            ['id' => 1, 'roles' => ['admin']],
            ['id' => 2, 'permissions' => 'create-post'],
            ['id' => 3, 'owner' => 'TestModel', 'overrideKey' => 'user_id'],
        ];
        
        $owner = new class {
            public int $user_id = 1;
        };
        app()->instance('TestModel', $owner);
        
        $this->cache->shouldReceive('remember')
            ->times(2)
            ->with(Mockery::pattern('/^fortress:auth:(roles|permissions):1$/'), 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(true);
        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(true);
        
        $results = $this->fortress->batchAuthorize($this->user, $resources);
        
        $this->assertEquals([
            1 => true,
            2 => true,
            3 => true,
        ], $results);
    }

    public function test_batch_authorize_handles_failures(): void
    {
        $resources = [
            ['id' => 1, 'roles' => ['admin']],
            ['id' => 2, 'permissions' => 'create-post'],
        ];
        
        $this->cache->shouldReceive('remember')
            ->times(2)
            ->with(Mockery::pattern('/^fortress:auth:(roles|permissions):1$/'), 3600, Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $this->roleAdapter->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(false);
        $this->permissionAdapter->shouldReceive('hasAnyPermission')->with(['create-post'])->andReturn(false);
        
        $results = $this->fortress->batchAuthorize($this->user, $resources);
        
        $this->assertEquals([
            1 => false,
            2 => false,
        ], $results);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
