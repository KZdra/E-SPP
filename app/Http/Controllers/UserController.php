<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UnitSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'unitSekolah'])
            ->orderBy('id', 'asc')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $units = UnitSekolah::where('is_active', true)->get();
        $roles = Role::all();
        return view('users.create', compact('units', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'unit_sekolah_id' => 'nullable|exists:unit_sekolahs,id',
            'role' => 'required|string|exists:roles,name',
            'status_aktif' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'unit_sekolah_id' => $validated['role'] === 'Admin Yayasan' ? null : $validated['unit_sekolah_id'],
            'password' => Hash::make($validated['password']),
            'status_aktif' => $request->has('status_aktif'),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $units = UnitSekolah::where('is_active', true)->get();
        $roles = Role::all();
        return view('users.edit', compact('user', 'units', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'unit_sekolah_id' => 'nullable|exists:unit_sekolahs,id',
            'role' => 'required|string|exists:roles,name',
            'status_aktif' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'unit_sekolah_id' => $validated['role'] === 'Admin Yayasan' ? null : $validated['unit_sekolah_id'],
            'status_aktif' => $request->has('status_aktif'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dinonaktifkan/dihapus.');
    }
}
