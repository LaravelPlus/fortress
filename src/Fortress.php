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

        if (!$user) {
            abort(401, 'User not authenticated.');
        }

        // Roles check
        if (!empty($roles) && !$user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized role.');
        }

        // Permissions check
        if ($permissions && !$user->hasPermissionTo($permissions)) {
            abort(403, 'Unauthorized permission.');
        }

        // Ownership check
        if ($owner) {
            $key = $overrideKey ?? config('fortress.default_override_key');
            $ownerInstance = app($owner);

            if (!property_exists($ownerInstance, $key)) {
                abort(500, "Key '{$key}' does not exist on the model.");
            }

            if ($ownerInstance->{$key} !== $user->id) {
                abort(403, 'Unauthorized ownership.');
            }
        }

        return true;
    }
}
