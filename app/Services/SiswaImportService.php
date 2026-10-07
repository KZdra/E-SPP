<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\UnitSekolah;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class SiswaImportService
{
    /**
     * Generate template Excel untuk import data siswa
     */
    public function generateTemplate(UnitSekolah $unit): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Headers
        $headers = [
            'A1' => 'NIS (*Wajib)',
            'B1' => 'NISN',
            'C1' => 'Nama Lengkap (*Wajib)',
            'D1' => 'Jenis Kelamin (L/P)',
            'E1' => 'Nama Kelas (*Wajib)',
            'F1' => 'Nama Wali',
            'G1' => 'No HP Wali',
            'H1' => 'Alamat',
            'I1' => 'Kategori SPP (reguler/beasiswa/keringanan/yatim)',
            'J1' => 'Diskon Tipe (persen/nominal)',
            'K1' => 'Diskon Nilai (angka)',
            'L1' => 'Catatan Keringanan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'], // Navy
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Get sample classes from unit
        $sampleKelas = Kelas::where('unit_sekolah_id', $unit->id)->first()?->nama_kelas ?? 'X RPL 1';

        // Sample Rows
        $examples = [
            ['2024001', '0081234567', 'Ahmad Pratama', 'L', $sampleKelas, 'Bambang Pratama', '081234567890', 'Jl. Merdeka No. 10', 'reguler', 'persen', 0, ''],
            ['2024002', '0081234568', 'Siti Rahmawati', 'P', $sampleKelas, 'Suryono', '081398765432', 'Jl. Melati No. 5', 'beasiswa', 'persen', 50, 'Beasiswa Prestasi Tahfidz'],
            ['2024003', '0081234569', 'Budi Santoso', 'L', $sampleKelas, 'Ibu Aminah', '081211223344', 'Jl. Kenanga No. 2', 'yatim', 'persen', 100, 'Yatim Piatu (Gratis 100%)'],
        ];

        $rowNum = 2;
        foreach ($examples as $row) {
            $sheet->fromArray($row, null, 'A' . $rowNum);
            $rowNum++;
        }

        // Auto width columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Template_Import_Siswa_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $unit->nama_unit) . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import data siswa dari file Excel
     */
    public function import(UploadedFile $file, int $unitId, ?int $fallbackKelasId = null): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Preload classes for this unit (mapped by lowercase trimmed name)
        $kelasMap = Kelas::where('unit_sekolah_id', $unitId)
            ->get()
            ->keyBy(fn($k) => strtolower(trim($k->nama_kelas)));

        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            // Row 1 is header, process starting from row 2
            for ($i = 2; $i <= count($rows); $i++) {
                $row = $rows[$i] ?? null;
                if (!$row) continue;

                $nis = trim($row['A'] ?? '');
                $nama = trim($row['C'] ?? '');

                // Skip completely empty rows
                if (empty($nis) && empty($nama)) {
                    continue;
                }

                if (empty($nis) || empty($nama)) {
                    $failed++;
                    $errors[] = "Baris {$i}: NIS dan Nama Lengkap wajib diisi.";
                    continue;
                }

                $namaKelas = trim($row['E'] ?? '');
                $kelasId = $fallbackKelasId;

                if (!empty($namaKelas)) {
                    $matchedKelas = $kelasMap->get(strtolower($namaKelas));
                    if ($matchedKelas) {
                        $kelasId = $matchedKelas->id;
                    }
                }

                if (!$kelasId) {
                    $failed++;
                    $errors[] = "Baris {$i}: Kelas '{$namaKelas}' tidak ditemukan pada unit ini.";
                    continue;
                }

                $jk = strtoupper(trim($row['D'] ?? 'L'));
                if (!in_array($jk, ['L', 'P'])) {
                    $jk = 'L';
                }

                $kategoriSpp = strtolower(trim($row['I'] ?? 'reguler'));
                if (!in_array($kategoriSpp, ['reguler', 'beasiswa', 'keringanan', 'yatim'])) {
                    $kategoriSpp = 'reguler';
                }

                $diskonTipe = strtolower(trim($row['J'] ?? 'persen'));
                if (!in_array($diskonTipe, ['persen', 'nominal'])) {
                    $diskonTipe = 'persen';
                }

                $diskonNilai = (float)($row['K'] ?? 0);
                if ($kategoriSpp === 'yatim') {
                    $diskonNilai = 100;
                    $diskonTipe = 'persen';
                }

                // Check existing student by NIS in this unit
                $existing = Siswa::where('unit_sekolah_id', $unitId)
                    ->where('nis', $nis)
                    ->first();

                $payload = [
                    'unit_sekolah_id' => $unitId,
                    'kelas_id' => $kelasId,
                    'nis' => $nis,
                    'nisn' => trim($row['B'] ?? '') ?: null,
                    'nama' => $nama,
                    'jenis_kelamin' => $jk,
                    'nama_wali' => trim($row['F'] ?? '') ?: null,
                    'telepon_wali' => trim($row['G'] ?? '') ?: null,
                    'alamat' => trim($row['H'] ?? '') ?: null,
                    'kategori_spp' => $kategoriSpp,
                    'diskon_tipe' => $diskonTipe,
                    'diskon_nilai' => $diskonNilai,
                    'catatan_keringanan' => trim($row['L'] ?? '') ?: null,
                    'status' => 'aktif',
                ];

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Siswa::create($payload);
                    $imported++;
                }
            }

            DB::commit();

            if (auth()->check()) {
                activity('import_siswa')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'unit_id' => $unitId,
                        'imported' => $imported,
                        'updated' => $updated,
                        'failed' => $failed,
                    ])
                    ->log("Import Siswa: {$imported} siswa baru ditambahkan, {$updated} diperbarui, {$failed} gagal.");
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => count($rows),
                'errors' => ['Terjadi kesalahan saat memproses file Excel: ' . $e->getMessage()],
            ];
        }
    }
}
