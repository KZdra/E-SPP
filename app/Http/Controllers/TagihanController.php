<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\UnitSekolah;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Services\BillingGeneratorService;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    protected BillingGeneratorService $billingService;

    public function __construct(BillingGeneratorService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Tagihan::with(['siswa.kelas', 'unitSekolah', 'tahunAjaran']);

        // Filter unit
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter bulan & tahun
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Search siswa
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $tagihans = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $units = UnitSekolah::where('is_active', true)->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('tagihans.index', compact('tagihans', 'units', 'kelasList'));
    }

    public function generate()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $tahunAjarans = TahunAjaran::where('is_active', true)->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('tagihans.generate', compact('units', 'tahunAjarans', 'kelasList'));
    }

    public function processGenerate(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2035',
            'kelas_id' => 'nullable|exists:kelas,id',
            'jatuh_tempo' => 'nullable|date',
            'nominal_default' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        try {
            $result = $this->billingService->generateMonthlyBilling($validated);

            return redirect()->route('tagihans.index')->with('success', 
                "Generate tagihan berhasil! {$result['created']} tagihan baru dibuat, {$result['skipped']} siswa dilewati (sudah memiliki tagihan)."
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat tagihan: ' . $e->getMessage());
        }
    }

    public function edit(Tagihan $tagihan)
    {
        return view('tagihans.edit', compact('tagihan'));
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:0',
            'jatuh_tempo' => 'nullable|date',
        ]);

        $tagihan->update($validated);

        return redirect()->route('tagihans.index')->with('success', 'Data tagihan berhasil disesuaikan.');
    }
}
