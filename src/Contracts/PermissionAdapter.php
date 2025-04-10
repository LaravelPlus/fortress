<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Contracts;

interface PermissionAdapter
{
    /**
     * Check if the user has the given permission.
     */
    public function hasPermissionTo(string $permission): bool;

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool;

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool;
} 