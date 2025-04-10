<?php

declare(strict_types=1);

namespace Laravelplus\Fortress;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Laravelplus\Fortress\Adapters\DefaultAdapter;
use Laravelplus\Fortress\Contracts\PermissionAdapter;
use Laravelplus\Fortress\Contracts\RoleAdapter;
use Laravelplus\Fortress\Exceptions\AuthenticationException;
use Laravelplus\Fortress\Exceptions\InvalidConfigurationException;
use Laravelplus\Fortress\Exceptions\UnauthorizedException;

final class Fortress
{
    private PermissionAdapter $permissionAdapter;
    private RoleAdapter $roleAdapter;

    public function __construct(
        private readonly Config $config,
        private readonly Cache $cache
    ) {
        $this->permissionAdapter = new DefaultAdapter();
        $this->roleAdapter = new DefaultAdapter();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getCache(): Cache
    {
        return $this->cache;
    }

    public function getPermissionAdapter(): PermissionAdapter
    {
        return $this->permissionAdapter;
    }

    public function getRoleAdapter(): RoleAdapter
    {
        return $this->roleAdapter;
    }

    public function setPermissionAdapter(PermissionAdapter $adapter): void
    {
        $this->permissionAdapter = $adapter;
    }

    public function setRoleAdapter(RoleAdapter $adapter): void
    {
        $this->roleAdapter = $adapter;
    }

    public function authorize(
        ?Authenticatable $user,
        bool $public = false,
        array|string $permissions = [],
        array|string $roles = [],
        ?string $owner = null,
        ?string $overrideKey = null
    ): bool {
        if ($public === true) {
            return true;
        }

        if (!$user) {
            throw new AuthenticationException('User not authenticated');
        }

        $permissions = is_string($permissions) ? [$permissions] : $permissions;
        $roles = is_string($roles) ? [$roles] : $roles;

        if (!empty($roles)) {
            $userId = $user->getAuthIdentifier();
            $cacheKey = "fortress:auth:roles:{$userId}";
            $ttl = $this->config->get('fortress.cache.ttl', 3600);

            $hasRole = $this->cache->remember($cacheKey, $ttl, function () use ($roles) {
                return $this->roleAdapter->hasAnyRole($roles);
            });

            if (!$hasRole) {
                throw new UnauthorizedException('User does not have required roles');
            }
        }

        if (!empty($permissions)) {
            $userId = $user->getAuthIdentifier();
            $cacheKey = "fortress:auth:permissions:{$userId}";
            $ttl = $this->config->get('fortress.cache.ttl', 3600);

            $hasPermission = $this->cache->remember($cacheKey, $ttl, function () use ($permissions) {
                return $this->permissionAdapter->hasAnyPermission($permissions);
            });

            if (!$hasPermission) {
                throw new UnauthorizedException('User does not have required permissions');
            }
        }

        if ($owner) {
            $model = app($owner);
            $key = $overrideKey ?? $this->config->get('fortress.ownership_key', 'user_id');

            if (!property_exists($model, $key)) {
                throw new InvalidConfigurationException("Invalid ownership key: {$key}");
            }

            if ($model->{$key} !== $user->getAuthIdentifier()) {
                throw new UnauthorizedException('User does not own this resource');
            }
        }

        return true;
    }

    public function batchAuthorize(Authenticatable $user, array $resources): array
    {
        $results = [];

        foreach ($resources as $resource) {
            try {
                $this->authorize(
                    user: $user,
                    public: $resource['public'] ?? false,
                    permissions: $resource['permissions'] ?? [],
                    roles: $resource['roles'] ?? [],
                    owner: $resource['owner'] ?? null,
                    overrideKey: $resource['overrideKey'] ?? null
                );

                $results[$resource['id']] = true;
            } catch (AuthenticationException|UnauthorizedException) {
                $results[$resource['id']] = false;
            }
        }

        return $results;
    }
}
