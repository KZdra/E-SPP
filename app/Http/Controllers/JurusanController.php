<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\UnitSekolah;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Jurusan::with('unitSekolah')->withCount('kelas');

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        $jurusans = $query->orderBy('unit_sekolah_id')->orderBy('kode_jurusan')->get();
        $units = UnitSekolah::where('is_active', true)->get();

        return view('jurusans.index', compact('jurusans', 'units'));
    }

    public function create()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        return view('jurusans.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kode_jurusan' => 'required|string|max:20',
            'nama_jurusan' => 'required|string|max:150',
            'bidang_keahlian' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
        ]);

        Jurusan::create($validated);

        return redirect()->route('jurusans.index')->with('success', 'Data Jurusan SMK berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        return view('jurusans.edit', compact('jurusan', 'units'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kode_jurusan' => 'required|string|max:20',
            'nama_jurusan' => 'required|string|max:150',
            'bidang_keahlian' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
        ]);

        $jurusan->update($validated);

        return redirect()->route('jurusans.index')->with('success', 'Data Jurusan SMK berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return redirect()->route('jurusans.index')->with('success', 'Data Jurusan SMK berhasil dihapus.');
    }
}
