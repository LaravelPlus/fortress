<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Contracts;

interface RoleAdapter
{
    /**
     * Check if the user has the given role.
     */
    public function hasRole(string $role): bool;

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool;

    /**
     * Check if the user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool;
} 