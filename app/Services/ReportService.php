<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\UnitSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get Realisasi Kas (Cash Realization) Report
     */
    public function getRealisasiKas(array $filters = []): array
    {
        $query = Pembayaran::with(['siswa.kelas', 'petugas', 'unitSekolah', 'details.tagihan'])
            ->orderBy('tgl_bayar', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['unit_sekolah_id'])) {
            $query->where('unit_sekolah_id', $filters['unit_sekolah_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('tgl_bayar', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tgl_bayar', '<=', $filters['end_date']);
        }

        if (!empty($filters['metode_bayar'])) {
            $query->where('metode_bayar', $filters['metode_bayar']);
        }

        $pembayarans = $query->get();

        $totalTunai = $pembayarans->where('metode_bayar', 'tunai')->sum('total_bayar');
        $totalTransfer = $pembayarans->where('metode_bayar', 'transfer')->sum('total_bayar');
        $grandTotal = $pembayarans->sum('total_bayar');

        return [
            'data' => $pembayarans,
            'total_tunai' => $totalTunai,
            'total_transfer' => $totalTransfer,
            'grand_total' => $grandTotal,
            'count' => $pembayarans->count(),
        ];
    }

    /**
     * Get Laporan Tunggakan (Arrears / Unpaid SPP)
     */
    public function getLaporanTunggakan(array $filters = []): array
    {
        $query = Tagihan::with(['siswa.kelas', 'unitSekolah', 'tahunAjaran'])
            ->where('status', 'belum_lunas');

        if (!empty($filters['unit_sekolah_id'])) {
            $query->where('unit_sekolah_id', $filters['unit_sekolah_id']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['tahun_ajaran_id'])) {
            $query->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
        }

        $tagihans = $query->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        // Group by student
        $rekapPerSiswa = $tagihans->groupBy('siswa_id')->map(function ($items) {
            $siswa = $items->first()->siswa;
            $totalTunggakan = $items->sum(fn($t) => $t->nominal - $t->nominal_terbayar);
            $bulanTertunggak = $items->map(fn($t) => $t->nama_bulan . ' ' . $t->tahun)->implode(', ');

            return [
                'siswa' => $siswa,
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'unit' => $siswa->unitSekolah->nama_unit ?? '-',
                'jumlah_bulan' => $items->count(),
                'daftar_bulan' => $bulanTertunggak,
                'total_tunggakan' => $totalTunggakan,
                'tagihan_items' => $items,
            ];
        })->values();

        return [
            'data' => $rekapPerSiswa,
            'tagihans' => $tagihans,
            'total_nominal' => $rekapPerSiswa->sum('total_tunggakan'),
            'total_siswa_tertunggak' => $rekapPerSiswa->count(),
            'total_tagihan_tertunggak' => $tagihans->count(),
        ];
    }

    /**
     * Get Rekapitulasi Matriks SPP per Kelas (Month by Month matrix)
     */
    public function getRekapitulasiMatriksKelas(int $kelasId, int $tahunAjaranId): array
    {
        $kelas = Kelas::with('unitSekolah')->findOrFail($kelasId);
        $tahunAjaran = TahunAjaran::findOrFail($tahunAjaranId);

        $siswas = Siswa::where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        // Academic year in Indonesia runs July to June (7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6)
        $monthsOrder = [
            ['bulan' => 7, 'label' => 'Jul'],
            ['bulan' => 8, 'label' => 'Agu'],
            ['bulan' => 9, 'label' => 'Sep'],
            ['bulan' => 10, 'label' => 'Okt'],
            ['bulan' => 11, 'label' => 'Nov'],
            ['bulan' => 12, 'label' => 'Des'],
            ['bulan' => 1, 'label' => 'Jan'],
            ['bulan' => 2, 'label' => 'Feb'],
            ['bulan' => 3, 'label' => 'Mar'],
            ['bulan' => 4, 'label' => 'Apr'],
            ['bulan' => 5, 'label' => 'Mei'],
            ['bulan' => 6, 'label' => 'Jun'],
        ];

        // Fetch all bills for this class and academic year
        $tagihans = Tagihan::whereIn('siswa_id', $siswas->pluck('id'))
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->with('pembayaranDetails.pembayaran')
            ->get()
            ->groupBy('siswa_id');

        $matriks = [];
        foreach ($siswas as $siswa) {
            $studentBills = $tagihans->get($siswa->id, collect());
            $monthsStatus = [];
            $totalTerbayar = 0;
            $totalTunggakan = 0;

            foreach ($monthsOrder as $m) {
                $bill = $studentBills->firstWhere('bulan', $m['bulan']);
                if ($bill) {
                    $isLunas = $bill->status === 'lunas';
                    if ($isLunas) {
                        $totalTerbayar += $bill->nominal;
                    } else {
                        $totalTunggakan += ($bill->nominal - $bill->nominal_terbayar);
                    }

                    $monthsStatus[$m['bulan']] = [
                        'has_bill' => true,
                        'status' => $bill->status,
                        'nominal' => $bill->nominal,
                        'terbayar' => $bill->nominal_terbayar,
                        'tgl_bayar' => $bill->pembayaranDetails->first()?->pembayaran?->tgl_bayar?->format('d/m/y'),
                    ];
                } else {
                    $monthsStatus[$m['bulan']] = [
                        'has_bill' => false,
                        'status' => 'belum_ada',
                        'nominal' => 0,
                        'terbayar' => 0,
                        'tgl_bayar' => null,
                    ];
                }
            }

            $matriks[] = [
                'siswa' => $siswa,
                'months' => $monthsStatus,
                'total_terbayar' => $totalTerbayar,
                'total_tunggakan' => $totalTunggakan,
            ];
        }

        return [
            'kelas' => $kelas,
            'tahun_ajaran' => $tahunAjaran,
            'months_header' => $monthsOrder,
            'matriks' => $matriks,
        ];
    }

    /**
     * Get Executive Dashboard KPIs & Analytics
     */
    public function getDashboardStats(?int $unitId = null): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Query bases
        $pembayaranQuery = Pembayaran::query();
        $tagihanQuery = Tagihan::query();
        $siswaQuery = Siswa::where('status', 'aktif');

        if ($unitId) {
            $pembayaranQuery->where('unit_sekolah_id', $unitId);
            $tagihanQuery->where('unit_sekolah_id', $unitId);
            $siswaQuery->where('unit_sekolah_id', $unitId);
        }

        // 1. Penerimaan Bulan Ini
        $penerimaanBulanIni = (clone $pembayaranQuery)
            ->whereMonth('tgl_bayar', $currentMonth)
            ->whereYear('tgl_bayar', $currentYear)
            ->sum('total_bayar');

        // 2. Penerimaan Hari Ini
        $penerimaanHariIni = (clone $pembayaranQuery)
            ->whereDate('tgl_bayar', $now->toDateString())
            ->sum('total_bayar');

        // 3. Total Tunggakan Keseluruhan
        $totalTunggakan = (clone $tagihanQuery)
            ->where('status', 'belum_lunas')
            ->selectRaw('SUM(nominal - nominal_terbayar) as tunggakan')
            ->value('tunggakan') ?? 0;

        // 4. Total Siswa Aktif
        $totalSiswa = $siswaQuery->count();

        // 5. Tingkat Kepatuhan (Compliance rate % for current month)
        $tagihanBulanIni = (clone $tagihanQuery)
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->get();

        $totalTagihanCount = $tagihanBulanIni->count();
        $lunasCount = $tagihanBulanIni->where('status', 'lunas')->count();
        $persenKepatuhan = $totalTagihanCount > 0 ? round(($lunasCount / $totalTagihanCount) * 100, 1) : 0;

        // 6. Trend Penerimaan 6 Bulan Terakhir
        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $chartLabels[] = $monthDate->translatedFormat('M Y');

            $rev = Pembayaran::query();
            if ($unitId) {
                $rev->where('unit_sekolah_id', $unitId);
            }
            $chartData[] = (float) $rev->whereMonth('tgl_bayar', $monthDate->month)
                ->whereYear('tgl_bayar', $monthDate->year)
                ->sum('total_bayar');
        }

        // 7. Breakdown per Unit (Khusus Yayasan / Multi-Unit overview)
        $unitBreakdown = [];
        if (!$unitId) {
            $units = UnitSekolah::where('is_active', true)->get();
            foreach ($units as $u) {
                $revUnit = Pembayaran::where('unit_sekolah_id', $u->id)
                    ->whereMonth('tgl_bayar', $currentMonth)
                    ->whereYear('tgl_bayar', $currentYear)
                    ->sum('total_bayar');

                $tunggakanUnit = Tagihan::where('unit_sekolah_id', $u->id)
                    ->where('status', 'belum_lunas')
                    ->selectRaw('SUM(nominal - nominal_terbayar) as tunggakan')
                    ->value('tunggakan') ?? 0;

                $unitBreakdown[] = [
                    'unit' => $u,
                    'penerimaan_bulan_ini' => $revUnit,
                    'total_tunggakan' => $tunggakanUnit,
                    'total_siswa' => Siswa::where('unit_sekolah_id', $u->id)->where('status', 'aktif')->count(),
                ];
            }
        }

        // 8. Transaksi Terbaru (Latest 5 payments)
        $recentPayments = (clone $pembayaranQuery)
            ->with(['siswa.kelas', 'unitSekolah', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        return [
            'penerimaan_bulan_ini' => $penerimaanBulanIni,
            'penerimaan_hari_ini' => $penerimaanHariIni,
            'total_tunggakan' => $totalTunggakan,
            'total_siswa' => $totalSiswa,
            'persen_kepatuhan' => $persenKepatuhan,
            'tagihan_lunas_count' => $lunasCount,
            'tagihan_total_count' => $totalTagihanCount,
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
            'unit_breakdown' => $unitBreakdown,
            'recent_payments' => $recentPayments,
        ];
    }
}
