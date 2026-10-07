<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\UnitSekolah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PaymentService
{
    /**
     * Process SPP payment transaction atomically (multi-bill / multi-month support)
     *
     * @param array $data [
     *   'unit_sekolah_id' => int,
     *   'siswa_id' => int,
     *   'user_id' => int,
     *   'tgl_bayar' => string (Y-m-d),
     *   'metode_bayar' => 'tunai'|'transfer',
     *   'bank_tujuan' => string|null,
     *   'nomor_referensi' => string|null,
     *   'catatan' => string|null,
     *   'tagihan_ids' => array of int,
     * ]
     * @return Pembayaran
     * @throws Exception
     */
    public function processPayment(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['siswa_id']);
            $unit = UnitSekolah::findOrFail($siswa->unit_sekolah_id);

            $tglBayar = !empty($data['tgl_bayar']) ? Carbon::parse($data['tgl_bayar']) : Carbon::today();
            $tagihanIds = $data['tagihan_ids'] ?? [];

            if (empty($tagihanIds)) {
                throw new Exception("Pilih minimal satu tagihan SPP yang akan dibayarkan.");
            }

            // Lock selected bills for update to prevent concurrent duplicate payments
            $tagihans = Tagihan::whereIn('id', $tagihanIds)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'belum_lunas')
                ->lockForUpdate()
                ->get();

            if ($tagihans->count() !== count($tagihanIds)) {
                throw new Exception("Satu atau lebih tagihan yang dipilih tidak valid atau sudah berstatus LUNAS.");
            }

            $totalBayar = 0;
            foreach ($tagihans as $tagihan) {
                $totalBayar += ($tagihan->nominal - $tagihan->nominal_terbayar);
            }

            // Generate unique transaction invoice code
            $kodeTransaksi = $this->generateTransactionCode($unit->kode_unit, $tglBayar);

            // Create Master Pembayaran
            $pembayaran = Pembayaran::create([
                'unit_sekolah_id' => $unit->id,
                'kode_transaksi' => $kodeTransaksi,
                'siswa_id' => $siswa->id,
                'user_id' => $data['user_id'],
                'tgl_bayar' => $tglBayar->format('Y-m-d'),
                'total_bayar' => $totalBayar,
                'metode_bayar' => $data['metode_bayar'] ?? 'tunai',
                'bank_tujuan' => $data['bank_tujuan'] ?? null,
                'nomor_referensi' => $data['nomor_referensi'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Create Details & Update Tagihan status
            foreach ($tagihans as $tagihan) {
                $nominalDibayar = $tagihan->nominal - $tagihan->nominal_terbayar;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'tagihan_id' => $tagihan->id,
                    'nominal_dibayar' => $nominalDibayar,
                ]);

                $tagihan->update([
                    'nominal_terbayar' => $tagihan->nominal,
                    'status' => 'lunas',
                ]);
            }

            // Audit log
            activity('transaksi_pembayaran')
                ->performedOn($pembayaran)
                ->causedBy(User::find($data['user_id']))
                ->withProperties([
                    'kode_transaksi' => $kodeTransaksi,
                    'siswa' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'total_bayar' => $totalBayar,
                    'jumlah_bulan' => count($tagihans),
                ])
                ->log("Pembayaran SPP {$kodeTransaksi} untuk {$siswa->nama} berhasil dicatat.");

            return $pembayaran->load(['siswa.kelas', 'petugas', 'details.tagihan', 'unitSekolah']);
        });
    }

    /**
     * Generate unique transaction invoice code: INV/{KODE_UNIT}/{YYYYMM}/{SEQ4}
     */
    public function generateTransactionCode(string $kodeUnit, Carbon $date): string
    {
        $yearMonth = $date->format('Ym');
        $prefix = "INV/" . strtoupper($kodeUnit) . "/{$yearMonth}/";

        $lastPayment = Pembayaran::withTrashed()
            ->where('kode_transaksi', 'like', $prefix . '%')
            ->orderBy('kode_transaksi', 'desc')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($lastPayment) {
            $parts = explode('/', $lastPayment->kode_transaksi);
            $lastSeq = (int) end($parts);
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Void / Cancel a payment (Transaction rollback with audit trail)
     */
    public function voidPayment(int $pembayaranId, int $userId, string $alasan): bool
    {
        return DB::transaction(function () use ($pembayaranId, $userId, $alasan) {
            $pembayaran = Pembayaran::with('details.tagihan')->findOrFail($pembayaranId);

            foreach ($pembayaran->details as $detail) {
                if ($detail->tagihan) {
                    $detail->tagihan->update([
                        'nominal_terbayar' => max(0, $detail->tagihan->nominal_terbayar - $detail->nominal_dibayar),
                        'status' => 'belum_lunas',
                    ]);
                }
            }

            $user = User::find($userId);
            activity('pembatalan_transaksi')
                ->performedOn($pembayaran)
                ->causedBy($user)
                ->withProperties([
                    'kode_transaksi' => $pembayaran->kode_transaksi,
                    'alasan' => $alasan,
                ])
                ->log("Pembayaran {$pembayaran->kode_transaksi} dibatalkan / void.");

            $pembayaran->delete(); // Soft delete for audit trail
            return true;
        });
    }
}
