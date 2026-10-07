<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\UnitSekolah;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Pembayaran::with(['siswa.kelas', 'petugas', 'unitSekolah', 'details.tagihan']);

        // Scope unit
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        // Filter date range
        if ($request->filled('start_date')) {
            $query->whereDate('tgl_bayar', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tgl_bayar', '<=', $request->end_date);
        }

        // Filter payment method
        if ($request->filled('metode_bayar')) {
            $query->where('metode_bayar', $request->metode_bayar);
        }

        // Search invoice or student (for non-ajax standard GET requests)
        if (!$request->ajax() && $request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
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
                ->filterColumn('kasir_name', function ($q, $keyword) {
                    $q->whereHas('petugas', function ($pq) use ($keyword) {
                        $pq->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('tgl_format', fn($row) => $row->tgl_bayar ? $row->tgl_bayar->format('d/m/Y') : '-')
                ->addColumn('siswa_info', function ($row) {
                    $nama = e($row->siswa?->nama ?? '-');
                    $nis = e($row->siswa?->nis ?? '-');
                    $kelas = e($row->siswa?->kelas?->nama_kelas ?? '-');
                    return "<div><strong class='text-dark'>{$nama}</strong><br><small class='text-muted'>NIS: {$nis} | Kelas: {$kelas}</small></div>";
                })
                ->addColumn('metode_badge', function ($row) {
                    $badge = $row->metode_bayar === 'tunai' ? 'success' : 'primary';
                    return '<span class="badge bg-' . $badge . '">' . strtoupper($row->metode_bayar) . '</span>';
                })
                ->addColumn('total_format', fn($row) => '<strong class="text-success">Rp ' . number_format($row->total_bayar, 0, ',', '.') . '</strong>')
                ->addColumn('kasir_name', fn($row) => e($row->petugas?->name ?? '-'))
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'void') {
                        return '<span class="badge bg-danger">VOID (Batal)</span>';
                    }
                    return '<span class="badge bg-success">BERHASIL</span>';
                })
                ->addColumn('action', function ($row) {
                    $detailUrl = route('pembayarans.show', $row->id);
                    $printUrl = route('pembayarans.kuitansi', $row->id);
                    $voidBtn = '';
                    if (auth()->user()->can('pembayaran.void') && $row->status !== 'void') {
                        $voidUrl = route('pembayarans.void', $row->id);
                        $csrf = csrf_field();
                        $voidBtn = '<form action="' . $voidUrl . '" method="POST" class="d-inline" onsubmit="return confirm(\'Batalkan transaksi ini (VOID)? Tagihan siswa akan dikembalikan ke status belum lunas.\')">
                            ' . $csrf . '
                            <button type="submit" class="btn btn-outline-danger" title="Void Pembayaran"><i class="bi bi-x-circle"></i></button>
                        </form>';
                    }
                    return '<div class="btn-group btn-group-sm">
                        <a href="' . $detailUrl . '" class="btn btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="' . $printUrl . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Kwitansi"><i class="bi bi-printer"></i></a>
                        ' . $voidBtn . '
                    </div>';
                })
                ->rawColumns(['siswa_info', 'metode_badge', 'total_format', 'status_badge', 'action'])
                ->make(true);
        }

        $pembayarans = $query->orderBy('tgl_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $units = UnitSekolah::where('is_active', true)->get();

        return view('pembayarans.index', compact('pembayarans', 'units'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $selectedSiswa = null;
        $unpaidBills = collect();

        // Get students scoped by unit
        $siswaQuery = Siswa::with('kelas')
            ->where('status', 'aktif')
            ->orderBy('nama');

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $siswaQuery->where('unit_sekolah_id', $user->unit_sekolah_id);
        }

        $students = $siswaQuery->get();

        if ($request->filled('siswa_id')) {
            $selectedSiswa = Siswa::with(['kelas', 'unitSekolah'])->find($request->siswa_id);
            if ($selectedSiswa) {
                $unpaidBills = Tagihan::with('tahunAjaran')
                    ->where('siswa_id', $selectedSiswa->id)
                    ->where('status', 'belum_lunas')
                    ->orderBy('tahun', 'asc')
                    ->orderBy('bulan', 'asc')
                    ->get();
            }
        }

        return view('pembayarans.create', compact('students', 'selectedSiswa', 'unpaidBills'));
    }

    public function getUnpaidBills(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $siswa = Siswa::with(['kelas', 'unitSekolah'])->find($siswaId);

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $bills = Tagihan::with('tahunAjaran')
            ->where('siswa_id', $siswa->id)
            ->where('status', 'belum_lunas')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'periode' => $bill->nama_bulan . ' ' . $bill->tahun,
                    'tahun_ajaran' => $bill->tahunAjaran->tahun ?? '-',
                    'nominal' => (float)$bill->nominal,
                    'nominal_terbayar' => (float)$bill->nominal_terbayar,
                    'sisa_bayar' => $bill->sisa_bayar,
                    'jatuh_tempo' => $bill->jatuh_tempo ? $bill->jatuh_tempo->format('d/m/Y') : '-',
                ];
            });

        return response()->json([
            'success' => true,
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'unit' => $siswa->unitSekolah->nama_unit ?? '-',
                'unit_id' => $siswa->unit_sekolah_id,
            ],
            'bills' => $bills,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tgl_bayar' => 'required|date',
            'metode_bayar' => 'required|in:tunai,transfer',
            'bank_tujuan' => 'nullable|string|max:50',
            'nomor_referensi' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'tagihan_ids' => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihans,id',
        ]);

        $validated['user_id'] = auth()->id();

        try {
            $pembayaran = $this->paymentService->processPayment($validated);

            return redirect()->route('pembayarans.show', $pembayaran->id)
                ->with('success', "Transaksi {$pembayaran->kode_transaksi} berhasil diproses.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['siswa.kelas', 'petugas', 'unitSekolah', 'details.tagihan.tahunAjaran']);
        return view('pembayarans.show', compact('pembayaran'));
    }

    public function kuitansi(Pembayaran $pembayaran)
    {
        $pembayaran->load(['siswa.kelas', 'petugas', 'unitSekolah', 'details.tagihan.tahunAjaran']);
        return view('pembayarans.kuitansi', compact('pembayaran'));
    }

    public function void(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'alasan' => 'required|string|min:5',
        ]);

        try {
            $this->paymentService->voidPayment($pembayaran->id, auth()->id(), $request->alasan);

            return redirect()->route('pembayarans.index')
                ->with('success', "Transaksi {$pembayaran->kode_transaksi} telah dibatalkan / void.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Export Riwayat Transaksi to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportExcel(Request $request, \App\Services\ExcelExportService $excelService)
    {
        $user = auth()->user();
        $query = Pembayaran::with(['siswa.kelas', 'petugas', 'unitSekolah']);

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tgl_bayar', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tgl_bayar', '<=', $request->end_date);
        }
        if ($request->filled('metode_bayar')) {
            $query->where('metode_bayar', $request->metode_bayar);
        }

        $pembayarans = $query->orderBy('tgl_bayar', 'desc')->get();
        $unit = $request->filled('unit_id') ? UnitSekolah::find($request->unit_id) : null;

        return $excelService->exportRealisasiKas($pembayarans, [
            'start_date' => $request->start_date ?? date('Y-m-01'),
            'end_date' => $request->end_date ?? date('Y-m-d'),
            'unit_name' => $unit?->nama_unit ?? 'Seluruh Unit',
        ]);
    }
}
