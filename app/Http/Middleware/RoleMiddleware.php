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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $user = Auth::user();

        // Cek apakah role pengguna ada di daftar role yang diizinkan
        if (! in_array($user->role, $roles, true)) {
            // Arahkan otomatis ke dashboard pengguna masing-masing jika salah masuk
            return redirect()->route($user->getDashboardRoute())
                ->with('error', 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka halaman tersebut.');
        }

        return $next($request);
    }
}
