<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Proses Autentikasi (Mengecek email & password)
        $request->authenticate();

        // 2. Regenerasi session untuk keamanan (Mencegah session fixation)
        $request->session()->regenerate();

        // 3. Ambil data user yang baru saja login
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 4. Logika Redirect Berdasarkan Role
        |--------------------------------------------------------------------------
        | Menentukan halaman tujuan pertama kali setelah login berhasil.
        */
        
        if ($user->role === 'kepala_desa') {
            // Arahkan ke dashboard khusus Kades
            return redirect()->intended(route('kades.dashboard'));
        } 
        
        if ($user->role === 'super_role') {
            // Arahkan ke dashboard Admin (yang nantinya akan di-render sebagai view super_admin oleh DashboardController)
            return redirect()->intended(route('dashboard'));
        }
        
        // Arahkan ke dashboard Admin (Default untuk role: admin)
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}