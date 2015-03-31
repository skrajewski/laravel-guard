<?php

namespace Szykra\Guard\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Szykra\Guard\Exceptions\InsufficientPermissionsException;

class ProtectRoutes implements Middleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     * @throws InsufficientPermissionsException
     */
    public function handle($request, Closure $next)
    {
        $next($request);

        $route = $request->route();
        $needs = $this->routeNeedsPermission($route);

        if (!$needs) {
            return $next($request);
        }

        $user = $this->resolveUser();

        foreach ($needs as $tag) {
            if (!$user->can($tag)) {
                throw new InsufficientPermissionsException("You doesn't have insufficient permissions to access to {$route->getName()}. This route needs " . implode(', ', $needs) . ".");
            }
        }

        return $next($request);
    }

    /**
     * Return array of permission tags needs for route
     *
     * @param $route
     * @return array
     */
    private function routeNeedsPermission($route)
    {
        $actions = $route->getAction();

        if (isset($actions['needs'])) {
            return $this->parseNeedsPermissions($actions['needs']);
        }

        return [];
    }

    /**
     * Parse input argument to array of tags
     *
     * @param $permissions
     * @return array
     */
    private function parseNeedsPermissions($permissions)
    {
        if ($permissions && is_string($permissions)) {
            $permissions = explode('|', $permissions);
        }

        return $permissions;
    }

    private function resolveUser()
    {
        $user = \App::make('Szykra\Guard\Contracts\Permissible');

        if (!$user) {
            throw new InsufficientPermissionsException("You must be authenticate if you want to check permissions.");
        }

        return $user;
    }

}