<?php

namespace App\Http\Controllers;

use App\Models\UnitSekolah;
use Illuminate\Http\Request;

class UnitSekolahController extends Controller
{
    public function index()
    {
        $units = UnitSekolah::withCount(['users', 'siswas', 'kelas'])
            ->orderBy('id', 'asc')
            ->get();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_unit' => 'required|string|max:20|unique:unit_sekolahs,kode_unit',
            'nama_unit' => 'required|string|max:100',
            'jenjang' => 'required|in:SD,SMP,SMA,SMK',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        UnitSekolah::create($validated);

        return redirect()->route('units.index')->with('success', 'Unit Sekolah berhasil ditambahkan.');
    }

    public function edit(UnitSekolah $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, UnitSekolah $unit)
    {
        $validated = $request->validate([
            'kode_unit' => 'required|string|max:20|unique:unit_sekolahs,kode_unit,' . $unit->id,
            'nama_unit' => 'required|string|max:100',
            'jenjang' => 'required|in:SD,SMP,SMA,SMK',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Data Unit Sekolah berhasil diperbarui.');
    }

    public function destroy(UnitSekolah $unit)
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit Sekolah berhasil dihapus (Soft Delete).');
    }
}
