<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        // Cek role mana saja yang sudah terdaftar di database
        $usedRoles = \App\Models\User::pluck('role')->toArray();
        
        // Daftar semua role yang ada di sistem
        $allRoles = [
            'super_role' => 'Super Role',
            'kepala_desa' => 'Kepala Desa',
            'admin' => 'Admin'
        ];

        // Jika semua role sudah terisi, arahkan kembali ke login
        if (count($usedRoles) >= count($allRoles)) {
            return redirect()->route('login')->with('error', 'Pendaftaran sudah ditutup.');
        }

        return view('auth.register', compact('allRoles', 'usedRoles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Definisikan role yang sah (Konsisten dengan routes/web.php)
        $validRoles = ['admin', 'kepala_desa', 'super_role'];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', $validRoles)], 
        ]);

        // 2. Proteksi Double Check
        $isRoleTaken = \App\Models\User::where('role', $request->role)->exists();
        if ($isRoleTaken) {
            return back()->withErrors(['role' => 'Mohon maaf, akun untuk role ini sudah terdaftar.']);
        }

        // 3. Simpan User
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        \Illuminate\Support\Facades\Auth::login($user);

        // 4. LOGIKA REDIRECT DINAMIS (Mencegah Error 403)
        if ($user->role === 'kepala_desa') {
            return redirect()->route('kades.dashboard');
        }

        // Default untuk admin dan super_role
        return redirect()->route('dashboard');
    }
}
