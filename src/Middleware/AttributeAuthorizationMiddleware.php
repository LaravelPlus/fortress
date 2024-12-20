<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravelplus\Fortress\Attributes\Authorize;
use ReflectionException;
use ReflectionMethod;

final class AttributeAuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @throws ReflectionException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        $controller = $route->getController();
        $method = $route->getActionMethod();

        $authorizeAttribute = $this->getAuthorizeAttribute($controller, $method);

        if ($authorizeAttribute !== null) {
            $this->processAuthorization($authorizeAttribute->newInstance(), $request);
        }

        return $next($request);
    }

    /**
     * Get the Authorize attribute from the specified method.
     *
     * @throws ReflectionException
     */
    private function getAuthorizeAttribute(object $controller, string $method): ?object
    {
        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(Authorize::class);

        return $attributes[0] ?? null;
    }

    /**
     * Process authorization based on the Authorize attribute.
     */
    private function processAuthorization(Authorize $attribute, Request $request): void
    {
        if ($this->isPublic($attribute)) {
            return;
        }

        abort_unless($this->passesGateCheck($attribute), 403, __('authorization.unauthorized_action'));

        abort_unless($this->passesRoleCheck($attribute), 403, __('authorization.unauthorized_action'));

        abort_unless($this->passesPermissionCheck($attribute), 403, __('authorization.unauthorized_action'));

        abort_unless($this->passesOwnershipCheck($attribute, $request), 403, __('authorization.not_owner'));
    }

    /**
     * Check if the method is marked as public.
     */
    private function isPublic(Authorize $attribute): bool
    {
        return $attribute->public;
    }

    /**
     * Validate gates if specified in the Authorize attribute.
     */
    private function passesGateCheck(Authorize $attribute): bool
    {
        return !$attribute->gates || Gate::allows($attribute->gates);
    }

    /**
     * Validate roles if specified in the Authorize attribute.
     */
    private function passesRoleCheck(Authorize $attribute): bool
    {
        return !$attribute->roles || auth()->user()?->hasAnyRole((array) $attribute->roles);
    }

    /**
     * Validate permissions if specified in the Authorize attribute.
     */
    private function passesPermissionCheck(Authorize $attribute): bool
    {
        return !$attribute->permissions || auth()->user()?->hasAnyPermission((array) $attribute->permissions);
    }

    /**
     * Validate ownership if specified in the Authorize attribute.
     */
    private function passesOwnershipCheck(Authorize $attribute, Request $request): bool
    {
        if (!$attribute->owner) {
            return true;
        }

        $model = $this->resolveResourceModel($attribute->owner, $request);
        if (!$model) {
            return false;
        }

        $ownerKey = $attribute->overrideKey ?? config('fortress.default_override_key');

        return auth()->user()?->id === $model->{$ownerKey};
    }

    /**
     * Resolve the resource model based on the owner parameter.
     */
    private function resolveResourceModel(string|object $resource, Request $request): ?object
    {
        return is_object($resource) || class_exists($resource)
            ? $this->resolveModelFromClass($resource, $request)
            : $this->resolveModelFromTable($resource, $request);
    }

    /**
     * Resolve the model from a class name.
     */
    private function resolveModelFromClass(string|object $resource, Request $request): ?object
    {
        $resourceClass = is_object($resource) ? $resource::class : $resource;
        $resourceId = $request->route($this->extractResourceNameFromClass($resourceClass));

        return $resourceClass::find($resourceId);
    }

    /**
     * Resolve the model from a table name.
     */
    private function resolveModelFromTable(string $resource, Request $request): ?object
    {
        $resourceId = $request->route($resource);
        $modelClass = 'App\\Models\\' . ucfirst(Str::singular($resource));

        return class_exists($modelClass) ? $modelClass::find($resourceId) : null;
    }

    /**
     * Extract the resource name from a class name.
     */
    private function extractResourceNameFromClass(string $class): string
    {
        return Str::camel(class_basename($class));
    }
}
