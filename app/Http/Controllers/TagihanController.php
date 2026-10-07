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

        // Search siswa (for non-ajax standard GET requests)
        if (!$request->ajax() && $request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('siswa_info', function ($q, $keyword) {
                    $q->whereHas('siswa', function ($sq) use ($keyword) {
                        $sq->where('nama', 'like', "%{$keyword}%")
                          ->orWhere('nis', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('unit_name', function ($q, $keyword) {
                    $q->whereHas('unitSekolah', function ($uq) use ($keyword) {
                        $uq->where('nama_unit', 'like', "%{$keyword}%")
                          ->orWhere('kode_unit', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('periode', function ($q, $keyword) {
                    $q->where(function ($pq) use ($keyword) {
                        $pq->where('bulan', 'like', "%{$keyword}%")
                          ->orWhere('tahun', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('periode', fn($row) => '<strong>' . e($row->nama_bulan) . ' ' . e($row->tahun) . '</strong>')
                ->addColumn('siswa_info', function ($row) {
                    $nama = e($row->siswa?->nama ?? '-');
                    $nis = e($row->siswa?->nis ?? '-');
                    $kelas = e($row->siswa?->kelas?->nama_kelas ?? '-');
                    return "<div><strong>{$nama}</strong><br><small class='text-muted'>NIS: {$nis} | Kelas: {$kelas}</small></div>";
                })
                ->addColumn('unit_name', fn($row) => e($row->unitSekolah?->nama_unit ?? '-'))
                ->addColumn('nominal_format', fn($row) => 'Rp ' . number_format($row->nominal, 0, ',', '.'))
                ->addColumn('terbayar_format', fn($row) => 'Rp ' . number_format($row->nominal_terbayar, 0, ',', '.'))
                ->addColumn('sisa_format', function ($row) {
                    $sisa = $row->nominal - $row->nominal_terbayar;
                    return '<strong class="text-danger">Rp ' . number_format($sisa, 0, ',', '.') . '</strong>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = match($row->status) {
                        'lunas' => 'success',
                        'sebagian' => 'warning text-dark',
                        default => 'danger'
                    };
                    return '<span class="badge bg-' . $badge . '">' . strtoupper(str_replace('_', ' ', $row->status)) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('tagihans.edit', $row->id);
                    return '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-warning" title="Edit Tagihan"><i class="bi bi-pencil"></i></a>';
                })
                ->rawColumns(['periode', 'siswa_info', 'sisa_format', 'status_badge', 'action'])
                ->make(true);
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

    /**
     * Export Tagihan to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportExcel(Request $request, \App\Services\ExcelExportService $excelService)
    {
        $user = auth()->user();
        $query = Tagihan::with(['siswa.kelas', 'unitSekolah', 'tahunAjaran']);

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $tagihans = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        $unit = $request->filled('unit_id') ? UnitSekolah::find($request->unit_id) : null;
        $kelas = $request->filled('kelas_id') ? Kelas::find($request->kelas_id) : null;

        return $excelService->exportTunggakan($tagihans, [
            'unit_name' => $unit?->nama_unit ?? 'Semua Unit',
            'kelas_name' => $kelas?->nama_kelas ?? 'Semua Kelas',
        ]);
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
