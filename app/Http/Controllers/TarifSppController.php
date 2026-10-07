<?php

namespace App\Http\Controllers;

use App\Models\TarifSpp;
use App\Models\UnitSekolah;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;

class TarifSppController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = TarifSpp::with(['unitSekolah', 'tahunAjaran', 'kelas']);

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        $tarifs = $query->orderBy('unit_sekolah_id')->orderBy('nominal', 'desc')->get();
        $units = UnitSekolah::where('is_active', true)->get();

        return view('tarifs.index', compact('tarifs', 'units'));
    }

    public function create()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $tahunAjarans = TahunAjaran::where('is_active', true)->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('tarifs.create', compact('units', 'tahunAjarans', 'kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'nominal' => 'required|numeric|min:0',
            'kategori' => 'required|in:reguler,beasiswa,khusus',
            'keterangan' => 'nullable|string|max:255',
        ]);

        TarifSpp::create($validated);

        return redirect()->route('tarifs.index')->with('success', 'Tarif SPP berhasil ditambahkan.');
    }

    public function edit(TarifSpp $tarif)
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $tahunAjarans = TahunAjaran::all();
        $kelas = Kelas::where('unit_sekolah_id', $tarif->unit_sekolah_id)->get();

        return view('tarifs.edit', compact('tarif', 'units', 'tahunAjarans', 'kelas'));
    }

    public function update(Request $request, TarifSpp $tarif)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'nominal' => 'required|numeric|min:0',
            'kategori' => 'required|in:reguler,beasiswa,khusus',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tarif->update($validated);

        return redirect()->route('tarifs.index')->with('success', 'Tarif SPP berhasil diperbarui.');
    }

    public function destroy(TarifSpp $tarif)
    {
        $tarif->delete();
        return redirect()->route('tarifs.index')->with('success', 'Tarif SPP berhasil dihapus.');
    }
}
