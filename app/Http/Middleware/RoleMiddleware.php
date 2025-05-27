<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? $user->level ?? '');
        $requiredRole = strtolower($role);

        if ($userRole === $requiredRole) {
            return $next($request);
        }

        // Redirect berdasarkan role user yang sebenarnya
        switch ($userRole) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            case 'pembeli':
                return redirect()->route('frontend.home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            default:
                return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
    }
}
