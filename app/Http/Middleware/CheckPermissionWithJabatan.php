<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckPermissionWithJabatan
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $user = $authGuard->user();
        
        // Cek permission langsung dari user
        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }
        
        // Cek permission dari role yang dimiliki user
        if ($user->hasRole($user->getRoleNamesAttribute()->toArray())) {
            // Cek apakah role memiliki permission yang diminta
            $roles = $user->getRoleNamesAttribute()->toArray();
            foreach ($roles as $role) {
                $roleModel = \App\Models\Role::findByName($role);
                if ($roleModel->hasPermissionTo($permission)) {
                    return $next($request);
                }
            }
        }

        throw UnauthorizedException::forPermissions([$permission]);
    }
}
