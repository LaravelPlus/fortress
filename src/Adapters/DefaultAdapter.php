<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Adapters;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravelplus\Fortress\Contracts\PermissionAdapter;
use Laravelplus\Fortress\Contracts\RoleAdapter;

final class DefaultAdapter implements PermissionAdapter, RoleAdapter
{
    public function __construct(
        private ?Authenticatable $user = null
    ) {
    }

    public function setUser(?Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function hasPermissionTo(string $permission): bool
    {
        return method_exists($this->user, 'hasPermissionTo') 
            ? $this->user->hasPermissionTo($permission)
            : false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return method_exists($this->user, 'hasAllPermissions') 
            ? $this->user->hasAllPermissions($permissions)
            : false;
    }

    public function hasRole(string $role): bool
    {
        return method_exists($this->user, 'hasRole') 
            ? $this->user->hasRole($role)
            : false;
    }

    public function hasAnyRole(array $roles): bool
    {
        return false;
    }

    public function hasAllRoles(array $roles): bool
    {
        return method_exists($this->user, 'hasAllRoles') 
            ? $this->user->hasAllRoles($roles)
            : false;
    }
} 