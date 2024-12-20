<?php

declare(strict_types=1);

namespace Laravelplus\Fortress;

use Illuminate\Support\Facades\Auth;

final class Fortress
{
    /**
     * Authorize a user based on roles, permissions, or ownership.
     */
    public static function authorize(
        array $roles = [],
        array|string|null $permissions = null,
        ?string $owner = null,
        ?string $overrideKey = null
    ): bool {
        $user = Auth::user();

        abort_if(!$user, 401, 'User not authenticated.');

        // Roles check
        abort_if(!empty($roles) && !$user->hasAnyRole($roles), 403, 'Unauthorized role.');

        // Permissions check
        abort_if($permissions && !$user->hasPermissionTo($permissions), 403, 'Unauthorized permission.');

        // Ownership check
        if (!$owner) {
            return true;
        }

        $key = $overrideKey ?? config('fortress.default_override_key');
        $ownerInstance = app($owner);

        abort_if(!property_exists($ownerInstance, $key), 500, "Key '{$key}' does not exist on the model.");
        abort_if($ownerInstance->{$key} !== $user->id, 403, 'Unauthorized ownership.');

        return true;
    }
}
