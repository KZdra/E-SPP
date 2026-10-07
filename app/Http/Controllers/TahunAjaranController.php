<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\UnitSekolah;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = TahunAjaran::with('unitSekolah');

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        $tahunAjarans = $query->orderBy('unit_sekolah_id')->orderBy('tahun', 'desc')->get();
        $units = UnitSekolah::where('is_active', true)->get();

        return view('tahun-ajarans.index', compact('tahunAjarans', 'units'));
    }

    public function create()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        return view('tahun-ajarans.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'tahun' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'is_active' => 'boolean',
        ]);

        $isActive = $request->has('is_active');

        if ($isActive) {
            // Deactivate existing active years for this unit
            TahunAjaran::where('unit_sekolah_id', $validated['unit_sekolah_id'])->update(['is_active' => false]);
        }

        TahunAjaran::create([
            'unit_sekolah_id' => $validated['unit_sekolah_id'],
            'tahun' => $validated['tahun'],
            'semester' => $validated['semester'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('tahun-ajarans.index')->with('success', 'Tahun Ajaran berhasil ditambahkan.');
    }

    public function activate(TahunAjaran $tahunAjaran)
    {
        TahunAjaran::where('unit_sekolah_id', $tahunAjaran->unit_sekolah_id)->update(['is_active' => false]);
        $tahunAjaran->update(['is_active' => true]);

        return redirect()->route('tahun-ajarans.index')->with('success', "Tahun ajaran {$tahunAjaran->tahun} ({$tahunAjaran->semester}) berhasil diaktifkan.");
    }
}
