<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  // Mengubah parameter menjadi variadic agar bisa menerima banyak role
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Jika pengguna belum login, arahkan ke login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // 2. Ambil role user yang sedang login
        $userRole = $request->user()->role;

        // 3. Cek apakah role user ada di dalam daftar roles yang diizinkan
        // in_array akan mengecek apakah 'super_role' ada di daftar ['admin', 'super_role']
        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}