<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Laravelplus\Fortress\Adapters\DefaultAdapter;
use Laravelplus\Fortress\Contracts\PermissionAdapter;
use Laravelplus\Fortress\Contracts\RoleAdapter;
use Laravelplus\Fortress\Fortress;
use Laravelplus\Fortress\FortressServiceProvider;
use Tests\TestCase;

final class FortressServiceProviderTest extends TestCase
{
    private FortressServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new FortressServiceProvider($this->app);
    }

    public function test_service_provider_registers_fortress_singleton(): void
    {
        $this->provider->register();

        $this->assertTrue($this->app->bound(Fortress::class));
        $this->assertInstanceOf(Fortress::class, $this->app->make(Fortress::class));
    }

    public function test_service_provider_injects_dependencies(): void
    {
        $this->provider->register();

        $fortress = $this->app->make(Fortress::class);

        $this->assertInstanceOf(Config::class, $fortress->getConfig());
        $this->assertInstanceOf(Cache::class, $fortress->getCache());
        $this->assertInstanceOf(PermissionAdapter::class, $fortress->getPermissionAdapter());
        $this->assertInstanceOf(RoleAdapter::class, $fortress->getRoleAdapter());
    }

    public function test_service_provider_registers_default_adapters(): void
    {
        $this->provider->register();

        $this->assertTrue($this->app->bound(PermissionAdapter::class));
        $this->assertTrue($this->app->bound(RoleAdapter::class));
        $this->assertInstanceOf(DefaultAdapter::class, $this->app->make(PermissionAdapter::class));
        $this->assertInstanceOf(DefaultAdapter::class, $this->app->make(RoleAdapter::class));
    }

    public function test_service_provider_publishes_configuration(): void
    {
        $this->provider->boot();

        $this->assertTrue($this->app['config']->has('fortress'));
        $this->assertEquals('user_id', $this->app['config']->get('fortress.default_owner_key'));
        $this->assertEquals('author_id', $this->app['config']->get('fortress.default_override_key'));
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
} 