<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\UnitSekolah;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Kelas::with(['unitSekolah', 'waliKelas'])->withCount('activeSiswas');

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        $kelas = $query->orderBy('unit_sekolah_id')->orderBy('nama_kelas')->get();
        $units = UnitSekolah::where('is_active', true)->get();

        return view('kelas.index', compact('kelas', 'units'));
    }

    public function create()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $teachers = User::where('status_aktif', true)->get();

        return view('kelas.create', compact('units', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'nullable|string|max:20',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('success', 'Data Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $kelas = $kela;
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $teachers = User::where('status_aktif', true)->get();

        return view('kelas.edit', compact('kelas', 'units', 'teachers'));
    }

    public function update(Request $request, Kelas $kela)
    {
        $kelas = $kela;
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'nullable|string|max:20',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $kelas->update($validated);

        return redirect()->route('kelas.index')->with('success', 'Data Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();
        return redirect()->route('kelas.index')->with('success', 'Data Kelas berhasil dihapus.');
    }
}
