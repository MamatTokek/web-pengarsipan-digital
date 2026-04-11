<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManualResetPasswordController extends Controller
{
    // Menampilkan halaman form lupa password manual
    public function create()
    {
        return view('auth.forgot-password');
    }

    // Memproses reset password tanpa token email
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ], [
            'email.exists' => 'Email tidak terdaftar di sistem kami.'
        ]);

        $user = User::where('email', $request->email)->first();

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Password berhasil diperbarui']);
        }

        return redirect()->route('login')->with('success_reset', 'Password Anda telah berhasil diperbarui!');
    }
}