<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display a listing of user accounts.
     */
    public function index()
    {
        $users = User::with('bidang')->where('role', '!=', 'admin')->orderBy('name')->get();
        $bidangs = Bidang::orderBy('nama')->get();
        return view('admin.users', compact('users', 'bidangs'));
    }

    /**
     * Store a newly created user account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip',
            'jabatan' => 'required|string|max:255',
            'bidang_id' => 'nullable|exists:bidangs,id',
            'role' => 'required|in:sekretaris_master,ketua_master,sekretaris_bidang,ketua_bidang,staff',
        ], [
            'nip.unique' => 'NIP sudah terdaftar di sistem.',
            'name.required' => 'Nama pegawai wajib diisi.',
            'nip.required' => 'NIP pegawai wajib diisi.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
            'role.required' => 'Role pegawai wajib diisi.',
        ]);

        // Auto-generate initial password
        $initialPassword = 'password'; // Standard initial password for demo/default
        
        User::create([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
            'jabatan' => $validated['jabatan'],
            'bidang_id' => $validated['bidang_id'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($initialPassword),
            'must_change_password' => true, // must change on first login!
            'active' => true,
        ]);

        return back()->with('success', 'Akun pegawai baru berhasil dibuat. Password awal default: "password". Pegawai wajib mengubahnya saat pertama kali masuk.');
    }

    /**
     * Update user account details or reassign role (Succession path).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip,' . $user->id,
            'jabatan' => 'required|string|max:255',
            'bidang_id' => 'nullable|exists:bidangs,id',
            'role' => 'required|in:sekretaris_master,ketua_master,sekretaris_bidang,ketua_bidang,staff',
        ]);

        $user->update([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
            'jabatan' => $validated['jabatan'],
            'bidang_id' => $validated['bidang_id'] ?? null,
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Akun pegawai ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Reset user's password to default.
     */
    public function resetPassword(Request $request, User $user)
    {
        $user->update([
            'password' => Hash::make('password'),
            'must_change_password' => true, // force change again
        ]);

        return back()->with('success', 'Password pegawai ' . $user->name . ' berhasil di-reset ke default: "password".');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'active' => !$user->active,
        ]);

        $statusStr = $user->active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Akun pegawai ' . $user->name . ' berhasil ' . $statusStr . '.');
    }

    /**
     * Display listing of Bidangs.
     */
    public function bidangIndex()
    {
        $bidangs = Bidang::withCount('users')->orderBy('nama')->get();
        return view('admin.bidang', compact('bidangs'));
    }

    /**
     * Store new Bidang.
     */
    public function bidangStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:bidangs,nama',
            'singkatan' => 'required|string|max:50',
        ], [
            'nama.unique' => 'Nama bidang sudah ada.',
        ]);

        Bidang::create($validated);

        return back()->with('success', 'Bidang baru berhasil ditambahkan.');
    }

    /**
     * Update Bidang.
     */
    public function bidangUpdate(Request $request, Bidang $bidang)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:bidangs,nama,' . $bidang->id,
            'singkatan' => 'required|string|max:50',
        ]);

        $bidang->update($validated);

        return back()->with('success', 'Data bidang berhasil diperbarui.');
    }
}
