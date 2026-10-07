<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TarifSpp;
use App\Models\TahunAjaran;
use App\Models\UnitSekolah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BillingGeneratorService
{
    /**
     * Generate bulk monthly SPP invoices for active students
     *
     * @param array $params [
     *   'unit_sekolah_id' => int,
     *   'tahun_ajaran_id' => int,
     *   'bulan' => int,
     *   'tahun' => int,
     *   'kelas_id' => int|null,
     *   'jatuh_tempo' => string|null,
     *   'nominal_default' => float|null,
     *   'user_id' => int,
     * ]
     * @return array ['created' => int, 'skipped' => int, 'total_students' => int]
     */
    public function generateMonthlyBilling(array $params): array
    {
        return DB::transaction(function () use ($params) {
            $unitId = $params['unit_sekolah_id'];
            $tahunAjaranId = $params['tahun_ajaran_id'];
            $bulan = (int)$params['bulan'];
            $tahun = (int)$params['tahun'];
            $kelasId = $params['kelas_id'] ?? null;
            $userId = $params['user_id'] ?? null;

            $jatuhTempo = !empty($params['jatuh_tempo'])
                ? Carbon::parse($params['jatuh_tempo'])
                : Carbon::create($tahun, $bulan, 10); // Default 10th of the month

            $query = Siswa::where('unit_sekolah_id', $unitId)
                ->where('status', 'aktif');

            if ($kelasId) {
                $query->where('kelas_id', $kelasId);
            }

            $students = $query->with(['kelas.jurusan'])->get();

            // Pre-load tariffs for this academic year & unit
            $tariffs = TarifSpp::where('unit_sekolah_id', $unitId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get();

            $defaultTariff = $tariffs->whereNull('kelas_id')->whereNull('jurusan_id')->first()?->nominal
                ?? ($params['nominal_default'] ?? 250000);

            $createdCount = 0;
            $skippedCount = 0;

            foreach ($students as $siswa) {
                // Check if invoice already exists for this student in this month & year
                $exists = Tagihan::where('siswa_id', $siswa->id)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Determine student tariff hierarchy:
                // 1. Specific to Siswa's Kelas ($siswa->kelas_id)
                // 2. Specific to Siswa's Jurusan (via $siswa->kelas?->jurusan_id)
                // 3. Default unit tariff (no class, no jurusan)
                $classTariff = $tariffs->where('kelas_id', $siswa->kelas_id)->first();
                $jurusanTariff = ($siswa->kelas && $siswa->kelas->jurusan_id)
                    ? $tariffs->where('jurusan_id', $siswa->kelas->jurusan_id)->whereNull('kelas_id')->first()
                    : null;

                if ($classTariff) {
                    $nominal = $classTariff->nominal;
                } elseif ($jurusanTariff) {
                    $nominal = $jurusanTariff->nominal;
                } else {
                    $nominal = $defaultTariff;
                }

                // Perhitungkan beasiswa / diskon khusus siswa jika ada
                $finalNominal = $siswa->hitungNominalSetelahDiskon((float)$nominal);
                $status = ($finalNominal <= 0) ? 'lunas' : 'belum_lunas';

                Tagihan::create([
                    'unit_sekolah_id' => $unitId,
                    'siswa_id' => $siswa->id,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'nominal' => $finalNominal,
                    'nominal_terbayar' => 0,
                    'status' => $status,
                    'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                ]);

                $createdCount++;
            }

            // Audit log
            if ($userId) {
                $user = User::find($userId);
                $unit = UnitSekolah::find($unitId);
                activity('generate_tagihan')
                    ->causedBy($user)
                    ->withProperties([
                        'unit' => $unit?->nama_unit,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'created' => $createdCount,
                        'skipped' => $skippedCount,
                    ])
                    ->log("Generate Tagihan SPP Periode {$bulan}/{$tahun} berhasil: {$createdCount} dibuat, {$skippedCount} dilewati.");
            }

            return [
                'created' => $createdCount,
                'skipped' => $skippedCount,
                'total_students' => $students->count(),
            ];
        });
    }
}
