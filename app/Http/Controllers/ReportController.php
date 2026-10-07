<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\UnitSekolah;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Laporan Realisasi Kas Harian / Periode
     */
    public function realisasiKas(Request $request)
    {
        $user = auth()->user();
        $filters = [];

        // Scope unit
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $filters['unit_sekolah_id'] = $user->unit_sekolah_id;
        } elseif ($request->filled('unit_id')) {
            $filters['unit_sekolah_id'] = $request->unit_id;
        }

        $filters['start_date'] = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $filters['end_date'] = $request->get('end_date', Carbon::now()->toDateString());

        if ($request->filled('metode_bayar')) {
            $filters['metode_bayar'] = $request->metode_bayar;
        }

        $report = $this->reportService->getRealisasiKas($filters);
        $units = UnitSekolah::where('is_active', true)->get();

        return view('reports.realisasi-kas', compact('report', 'units', 'filters'));
    }

    /**
     * Laporan Tunggakan SPP
     */
    public function tunggakan(Request $request)
    {
        $user = auth()->user();
        $filters = [];

        // Scope unit
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $filters['unit_sekolah_id'] = $user->unit_sekolah_id;
        } elseif ($request->filled('unit_id')) {
            $filters['unit_sekolah_id'] = $request->unit_id;
        }

        if ($request->filled('kelas_id')) {
            $filters['kelas_id'] = $request->kelas_id;
        }

        if ($request->filled('tahun_ajaran_id')) {
            $filters['tahun_ajaran_id'] = $request->tahun_ajaran_id;
        }

        $report = $this->reportService->getLaporanTunggakan($filters);
        $units = UnitSekolah::where('is_active', true)->get();

        $kelasList = Kelas::query();
        if (!empty($filters['unit_sekolah_id'])) {
            $kelasList->where('unit_sekolah_id', $filters['unit_sekolah_id']);
        }
        $kelasList = $kelasList->orderBy('nama_kelas')->get();

        $tahunAjarans = TahunAjaran::orderBy('tahun', 'desc')->get();

        return view('reports.tunggakan', compact('report', 'units', 'kelasList', 'tahunAjarans', 'filters'));
    }

    /**
     * Rekapitulasi Matriks SPP Kelas
     */
    public function matriksKelas(Request $request)
    {
        $user = auth()->user();
        $unitId = (!$user->isYayasan() && $user->unit_sekolah_id)
            ? $user->unit_sekolah_id
            : $request->get('unit_id');

        $units = UnitSekolah::where('is_active', true)->get();

        $kelasQuery = Kelas::query();
        if ($unitId) {
            $kelasQuery->where('unit_sekolah_id', $unitId);
        }
        $kelasList = $kelasQuery->orderBy('nama_kelas')->get();

        $tahunAjaranQuery = TahunAjaran::query();
        if ($unitId) {
            $tahunAjaranQuery->where('unit_sekolah_id', $unitId);
        }
        $tahunAjarans = $tahunAjaranQuery->orderBy('tahun', 'desc')->get();

        $report = null;
        $selectedKelasId = $request->get('kelas_id') ?? $kelasList->first()?->id;
        $selectedTaId = $request->get('tahun_ajaran_id') ?? $tahunAjarans->firstWhere('is_active', true)?->id ?? $tahunAjarans->first()?->id;

        if ($selectedKelasId && $selectedTaId) {
            $report = $this->reportService->getRekapitulasiMatriksKelas($selectedKelasId, $selectedTaId);
        }

        return view('reports.matriks-kelas', compact(
            'report',
            'units',
            'kelasList',
            'tahunAjarans',
            'unitId',
            'selectedKelasId',
            'selectedTaId'
        ));
    }

    /**
     * Export Realisasi Kas to Excel (.xlsx)
     */
    public function exportRealisasiKasExcel(Request $request, \App\Services\ExcelExportService $excelService)
    {
        $user = auth()->user();
        $filters = [];

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $filters['unit_sekolah_id'] = $user->unit_sekolah_id;
        } elseif ($request->filled('unit_id')) {
            $filters['unit_sekolah_id'] = $request->unit_id;
        }

        $filters['start_date'] = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $filters['end_date'] = $request->get('end_date', Carbon::now()->toDateString());

        if ($request->filled('metode_bayar')) {
            $filters['metode_bayar'] = $request->metode_bayar;
        }

        $unit = !empty($filters['unit_sekolah_id']) ? UnitSekolah::find($filters['unit_sekolah_id']) : null;
        $filters['unit_name'] = $unit ? $unit->nama_unit : 'Seluruh Unit';

        $report = $this->reportService->getRealisasiKas($filters);

        return $excelService->exportRealisasiKas($report['data'], $filters);
    }

    /**
     * Export Tunggakan to Excel (.xlsx)
     */
    public function exportTunggakanExcel(Request $request, \App\Services\ExcelExportService $excelService)
    {
        $user = auth()->user();
        $filters = [];

        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $filters['unit_sekolah_id'] = $user->unit_sekolah_id;
        } elseif ($request->filled('unit_id')) {
            $filters['unit_sekolah_id'] = $request->unit_id;
        }

        if ($request->filled('kelas_id')) {
            $filters['kelas_id'] = $request->kelas_id;
            $kelas = Kelas::find($request->kelas_id);
            $filters['kelas_name'] = $kelas?->nama_kelas;
        }

        if ($request->filled('tahun_ajaran_id')) {
            $filters['tahun_ajaran_id'] = $request->tahun_ajaran_id;
        }

        $unit = !empty($filters['unit_sekolah_id']) ? UnitSekolah::find($filters['unit_sekolah_id']) : null;
        $filters['unit_name'] = $unit ? $unit->nama_unit : 'Seluruh Unit';

        $report = $this->reportService->getLaporanTunggakan($filters);

        return $excelService->exportTunggakan($report['tagihans'], $filters);
    }

    /**
     * Export Matriks Kelas 12 Bulan to Excel (.xlsx)
     */
    public function exportMatriksKelasExcel(Request $request, \App\Services\ExcelExportService $excelService)
    {
        $user = auth()->user();
        $unitId = (!$user->isYayasan() && $user->unit_sekolah_id)
            ? $user->unit_sekolah_id
            : $request->get('unit_id');

        $kelasId = $request->get('kelas_id');
        $taId = $request->get('tahun_ajaran_id');

        if (!$kelasId || !$taId) {
            return back()->with('error', 'Silakan pilih Kelas dan Tahun Ajaran terlebih dahulu untuk ekspor matriks.');
        }

        $kelas = Kelas::with('unitSekolah')->findOrFail($kelasId);
        $tahunAjaran = TahunAjaran::findOrFail($taId);
        $report = $this->reportService->getRekapitulasiMatriksKelas($kelasId, $taId);

        return $excelService->exportMatriksKelas($report, $tahunAjaran, $kelas);
    }
}
