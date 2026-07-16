<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt(['nip' => $credentials['nip'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $user = Auth::user();
            
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors(['nip' => 'Akun Anda dinonaktifkan oleh administrator.']);
            }

            $request->session()->regenerate();

            if ($user->must_change_password) {
                return redirect()->route('password.change');
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'nip' => 'NIP atau password salah.',
        ])->withInput($request->only('nip'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password Anda berhasil diperbarui. Selamat bekerja!');
    }

    public function showProfile()
    {
        return view('auth.profile');
    }
}
