<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Export Laporan Realisasi Kas to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportRealisasiKas($pembayarans, array $filters): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Realisasi Kas SPP');

        // Page setup
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', 'SISTEM INFORMASI KEUANGAN SEKOLAH');
        $sheet->setCellValue('A2', 'LAPORAN REALISASI PENERIMAAN KAS SPP');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        // Filter Information Subheader
        $periodeText = 'Periode: ' . ($filters['start_date'] ?? '-') . ' s/d ' . ($filters['end_date'] ?? '-');
        $unitText = 'Unit Sekolah: ' . ($filters['unit_name'] ?? 'Seluruh Unit');
        $sheet->setCellValue('A3', $periodeText . ' | ' . $unitText);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:J3');

        // Summary Statistics Box
        $totalTunai = $pembayarans->where('metode_bayar', 'tunai')->sum('total_bayar');
        $totalTransfer = $pembayarans->where('metode_bayar', 'transfer')->sum('total_bayar');
        $grandTotal = $pembayarans->sum('total_bayar');

        $sheet->setCellValue('A5', 'RINGKASAN TOTAL:');
        $sheet->setCellValue('A6', 'Penerimaan Kas Tunai:');
        $sheet->setCellValue('B6', $totalTunai);
        $sheet->setCellValue('D6', 'Penerimaan Transfer Bank:');
        $sheet->setCellValue('E6', $totalTransfer);
        $sheet->setCellValue('G6', 'TOTAL KAS MASUK:');
        $sheet->setCellValue('H6', $grandTotal);

        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A6:H6')->getFont()->setBold(true);
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E6')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('H6')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $sheet->getStyle('G6:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2F0D9');

        // Table Header
        $tableHeaders = [
            'A8' => 'No',
            'B8' => 'No. Transaksi',
            'C8' => 'Tanggal Bayar',
            'D8' => 'NIS',
            'E8' => 'Nama Siswa',
            'F8' => 'Unit Sekolah',
            'G8' => 'Kelas',
            'H8' => 'Metode Bayar',
            'I8' => 'Nominal Bayar (Rp)',
            'J8' => 'Petugas Kasir'
        ];

        foreach ($tableHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1E3A8A']], // Dark Navy
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A8:J8')->applyFromArray($headerStyle);

        // Data Rows
        $row = 9;
        $no = 1;
        foreach ($pembayarans as $p) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $p->kode_transaksi);
            $sheet->setCellValue('C' . $row, $p->tgl_bayar ? $p->tgl_bayar->format('d/m/Y') : '-');
            $sheet->setCellValue('D' . $row, $p->siswa?->nis ?? '-');
            $sheet->setCellValue('E' . $row, $p->siswa?->nama ?? '-');
            $sheet->setCellValue('F' . $row, $p->unitSekolah?->nama_unit ?? '-');
            $sheet->setCellValue('G' . $row, $p->siswa?->kelas?->nama_kelas ?? '-');
            $sheet->setCellValue('H' . $row, strtoupper($p->metode_bayar));
            $sheet->setCellValue('I' . $row, $p->total_bayar);
            $sheet->setCellValue('J' . $row, $p->petugas?->name ?? '-');

            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total Row at bottom
        $sheet->setCellValue('A' . $row, 'TOTAL PENERIMAAN');
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue('I' . $row, "=SUM(I8:I" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9E1F2');

        // Border for data table
        $sheet->getStyle("A8:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto width columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Realisasi_Kas_' . date('Ymd_His') . '.xlsx';
        return $this->streamDownload($spreadsheet, $filename);
    }

    /**
     * Export Laporan Tunggakan Siswa to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportTunggakan($tagihans, array $filters): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Tunggakan SPP');
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', 'SISTEM INFORMASI KEUANGAN SEKOLAH');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI TUNGGAKAN PEMBAYARAN SPP');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');

        $unitText = 'Unit Sekolah: ' . ($filters['unit_name'] ?? 'Seluruh Unit');
        $kelasText = 'Kelas: ' . ($filters['kelas_name'] ?? 'Semua Kelas');
        $sheet->setCellValue('A3', $unitText . ' | ' . $kelasText . ' | Dicetak: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:K3');

        // Summary
        $totalNominal = $tagihans->sum(fn($t) => $t->nominal - $t->nominal_terbayar);
        $sheet->setCellValue('A5', 'TOTAL SISWA MENUNGGAK:');
        $sheet->setCellValue('C5', $tagihans->groupBy('siswa_id')->count() . ' Siswa');
        $sheet->setCellValue('E5', 'TOTAL SISA TUNGGAKAN:');
        $sheet->setCellValue('G5', $totalNominal);

        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('G5')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6'); // Light Red

        // Table Headers
        $tableHeaders = [
            'A7' => 'No',
            'B7' => 'NIS',
            'C7' => 'Nama Siswa',
            'D7' => 'Unit Sekolah',
            'E7' => 'Kelas',
            'F7' => 'Bulan / Tahun',
            'G7' => 'Jatuh Tempo',
            'H7' => 'Tagihan (Rp)',
            'I7' => 'Terbayar (Rp)',
            'J7' => 'Sisa Tunggakan (Rp)',
            'K7' => 'Kontak Wali'
        ];

        foreach ($tableHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '991B1B']], // Crimson Dark Red
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A7:K7')->applyFromArray($headerStyle);

        // Data Rows
        $row = 8;
        $no = 1;
        foreach ($tagihans as $t) {
            $sisa = $t->nominal - $t->nominal_terbayar;
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $t->siswa?->nis ?? '-');
            $sheet->setCellValue('C' . $row, $t->siswa?->nama ?? '-');
            $sheet->setCellValue('D' . $row, $t->unitSekolah?->nama_unit ?? '-');
            $sheet->setCellValue('E' . $row, $t->siswa?->kelas?->nama_kelas ?? '-');
            $sheet->setCellValue('F' . $row, $t->nama_bulan . ' ' . $t->tahun);
            $sheet->setCellValue('G' . $row, $t->jatuh_tempo ? $t->jatuh_tempo->format('d/m/Y') : '-');
            $sheet->setCellValue('H' . $row, $t->nominal);
            $sheet->setCellValue('I' . $row, $t->nominal_terbayar);
            $sheet->setCellValue('J' . $row, $sisa);
            $sheet->setCellValue('K' . $row, ($t->siswa?->nama_wali ?? '-') . ' (' . ($t->siswa?->telepon_wali ?? '-') . ')');

            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Summary row
        $sheet->setCellValue('A' . $row, 'TOTAL SELURUH TUNGGAKAN');
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue('J' . $row, "=SUM(J8:J" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');

        $sheet->getStyle("A7:K{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Tunggakan_SPP_' . date('Ymd_His') . '.xlsx';
        return $this->streamDownload($spreadsheet, $filename);
    }

    /**
     * Export Matriks Kelas 12 Bulan to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportMatriksKelas(array $matriksData, $tahunAjaran, $kelas): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriks SPP Kelas');
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', 'REKAPITULASI MATRIKS PEMBAYARAN SPP SISWA (12 BULAN)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:Q1');

        $infoText = 'Unit: ' . ($kelas->unitSekolah->nama_unit ?? '-') . ' | Kelas: ' . $kelas->nama_kelas . ' | Tahun Ajaran: ' . ($tahunAjaran->tahun ?? '-');
        $sheet->setCellValue('A2', $infoText);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A2:Q2');

        // Table Header
        $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'NIS');
        $sheet->setCellValue('C4', 'Nama Siswa');

        $colIdx = 'D';
        foreach ($months as $m) {
            $sheet->setCellValue($colIdx . '4', $m);
            $colIdx++;
        }
        $sheet->setCellValue($colIdx . '4', 'Total Bayar (Rp)');
        $colIdx++;
        $sheet->setCellValue($colIdx . '4', 'Sisa Tunggakan (Rp)');
        $lastCol = $colIdx;

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1F2937']], // Dark Slate
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray($headerStyle);

        // Data Rows
        $row = 5;
        $no = 1;
        foreach ($matriksData['rows'] as $r) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $r['siswa']->nis);
            $sheet->setCellValue('C' . $row, $r['siswa']->nama);

            $c = 'D';
            foreach ($r['months'] as $mStatus) {
                $statusText = match($mStatus['status']) {
                    'lunas' => 'LUNAS',
                    'sebagian' => 'SEBAGIAN',
                    'belum_lunas' => 'BELUM',
                    default => '-'
                };
                $sheet->setCellValue($c . $row, $statusText);
                $sheet->getStyle($c . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($mStatus['status'] === 'lunas') {
                    $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D1E7DD'); // Green
                } elseif ($mStatus['status'] === 'belum_lunas') {
                    $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8D7DA'); // Red
                }
                $c++;
            }

            $sheet->setCellValue($c . $row, $r['total_terbayar']);
            $sheet->getStyle($c . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $c++;
            $sheet->setCellValue($c . $row, $r['total_tunggakan']);
            $sheet->getStyle($c . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');

            $row++;
        }

        $sheet->getStyle("A4:{$lastCol}" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Matriks_SPP_Kelas_' . preg_replace('/[^A-Za-z0-9]/', '_', $kelas->nama_kelas) . '_' . date('Ymd') . '.xlsx';
        return $this->streamDownload($spreadsheet, $filename);
    }

    /**
     * Export General Pembayaran / Kasir Transactions to Excel (.xlsx)
     */
    public function exportPembayaran($pembayarans, array $filters = []): StreamedResponse
    {
        return $this->exportRealisasiKas($pembayarans, [
            'start_date' => $filters['start_date'] ?? date('Y-m-01'),
            'end_date' => $filters['end_date'] ?? date('Y-m-d'),
            'unit_name' => $filters['unit_name'] ?? 'Semua Unit'
        ]);
    }

    /**
     * Export General Tagihan to Excel (.xlsx)
     */
    public function exportTagihan($tagihans, array $filters = []): StreamedResponse
    {
        return $this->exportTunggakan($tagihans, [
            'unit_name' => $filters['unit_name'] ?? 'Semua Unit',
            'kelas_name' => $filters['kelas_name'] ?? 'Semua Kelas'
        ]);
    }

    /**
     * Helper to stream PhpSpreadsheet download
     */
    protected function streamDownload(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
