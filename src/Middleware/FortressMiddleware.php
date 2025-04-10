<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravelplus\Fortress\Exceptions\AuthenticationException;
use Laravelplus\Fortress\Exceptions\UnauthorizedException;
use Laravelplus\Fortress\Fortress;

final class FortressMiddleware
{
    public function __construct(
        private readonly Fortress $fortress
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $fortressConfig = $route->getAction('fortress');
        if (!$fortressConfig) {
            return $next($request);
        }

        try {
            $this->fortress->authorize(
                user: $request->user(),
                public: $fortressConfig['public'] ?? false,
                permissions: $fortressConfig['permissions'] ?? [],
                roles: $fortressConfig['roles'] ?? [],
                owner: $fortressConfig['owner'] ?? null,
                overrideKey: $fortressConfig['overrideKey'] ?? null
            );

            return $next($request);
        } catch (AuthenticationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            throw $e;
        } catch (UnauthorizedException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            throw $e;
        }
    }
} 