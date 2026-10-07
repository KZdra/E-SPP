<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitSekolah;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TarifSpp;
use App\Models\Tagihan;
use App\Services\PaymentService;
use App\Services\BillingGeneratorService;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create School Units
        $unitSd = UnitSekolah::create([
            'kode_unit' => 'SD',
            'nama_unit' => 'SD Islam Terpadu Bina Cendekia',
            'jenjang' => 'SD',
            'alamat' => 'Jl. Pendidikan No. 10, Jakarta Selatan',
            'telepon' => '021-7890001',
            'email' => 'sd@sekolah.sch.id',
            'is_active' => true,
        ]);

        $unitSmp = UnitSekolah::create([
            'kode_unit' => 'SMP',
            'nama_unit' => 'SMP Islam Terpadu Bina Cendekia',
            'jenjang' => 'SMP',
            'alamat' => 'Jl. Pendidikan No. 12, Jakarta Selatan',
            'telepon' => '021-7890002',
            'email' => 'smp@sekolah.sch.id',
            'is_active' => true,
        ]);

        $unitSma = UnitSekolah::create([
            'kode_unit' => 'SMA',
            'nama_unit' => 'SMA Islam Terpadu Bina Cendekia',
            'jenjang' => 'SMA',
            'alamat' => 'Jl. Pendidikan No. 14, Jakarta Selatan',
            'telepon' => '021-7890003',
            'email' => 'sma@sekolah.sch.id',
            'is_active' => true,
        ]);

        // 2. Create Users with Assigned Roles
        // Yayasan Admin (Super Admin - Global across all units)
        $userYayasan = User::create([
            'unit_sekolah_id' => null,
            'username' => 'yayasan',
            'name' => 'Drs. H. M. Fauzi (Yayasan)',
            'email' => 'yayasan@sekolah.sch.id',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $userYayasan->assignRole('Admin Yayasan');

        // SMA Staff
        $kepsekSma = User::create([
            'unit_sekolah_id' => $unitSma->id,
            'username' => 'kepsek.sma',
            'name' => 'Dr. H. Ahmad Santoso, M.Pd (Kepsek SMA)',
            'email' => 'kepsek.sma@sekolah.sch.id',
            'phone' => '081298765431',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $kepsekSma->assignRole('Kepala Sekolah');

        $tuSma = User::create([
            'unit_sekolah_id' => $unitSma->id,
            'username' => 'tu.sma',
            'name' => 'Siti Rahmawati, S.E (Bendahara SMA)',
            'email' => 'tu.sma@sekolah.sch.id',
            'phone' => '081298765432',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $tuSma->assignRole('Petugas TU');

        // SMP Staff
        $kepsekSmp = User::create([
            'unit_sekolah_id' => $unitSmp->id,
            'username' => 'kepsek.smp',
            'name' => 'Bambang Sudarsono, M.Pd (Kepsek SMP)',
            'email' => 'kepsek.smp@sekolah.sch.id',
            'phone' => '081298765433',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $kepsekSmp->assignRole('Kepala Sekolah');

        $tuSmp = User::create([
            'unit_sekolah_id' => $unitSmp->id,
            'username' => 'tu.smp',
            'name' => 'Dewi Anggraini, A.Md (Bendahara SMP)',
            'email' => 'tu.smp@sekolah.sch.id',
            'phone' => '081298765434',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $tuSmp->assignRole('Petugas TU');

        // SD Staff
        $kepsekSd = User::create([
            'unit_sekolah_id' => $unitSd->id,
            'username' => 'kepsek.sd',
            'name' => 'Hj. Nurul Hidayati, S.Pd (Kepsek SD)',
            'email' => 'kepsek.sd@sekolah.sch.id',
            'phone' => '081298765435',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $kepsekSd->assignRole('Kepala Sekolah');

        $tuSd = User::create([
            'unit_sekolah_id' => $unitSd->id,
            'username' => 'tu.sd',
            'name' => 'Rina Marlina, S.Ak (Bendahara SD)',
            'email' => 'tu.sd@sekolah.sch.id',
            'phone' => '081298765436',
            'password' => Hash::make('password'),
            'status_aktif' => true,
        ]);
        $tuSd->assignRole('Petugas TU');

        // 3. Create Academic Year (2026/2027 Ganjil)
        $taSma = TahunAjaran::create([
            'unit_sekolah_id' => $unitSma->id,
            'tahun' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $taSmp = TahunAjaran::create([
            'unit_sekolah_id' => $unitSmp->id,
            'tahun' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $taSd = TahunAjaran::create([
            'unit_sekolah_id' => $unitSd->id,
            'tahun' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        // 4. Create Classes
        $kelasSma1 = Kelas::create(['unit_sekolah_id' => $unitSma->id, 'nama_kelas' => 'X-MIPA-1', 'tingkat' => '10', 'wali_kelas_id' => $tuSma->id]);
        $kelasSma2 = Kelas::create(['unit_sekolah_id' => $unitSma->id, 'nama_kelas' => 'X-IPS-1', 'tingkat' => '10']);
        $kelasSma3 = Kelas::create(['unit_sekolah_id' => $unitSma->id, 'nama_kelas' => 'XI-MIPA-1', 'tingkat' => '11']);

        $kelasSmp1 = Kelas::create(['unit_sekolah_id' => $unitSmp->id, 'nama_kelas' => 'VII-A', 'tingkat' => '7', 'wali_kelas_id' => $tuSmp->id]);
        $kelasSmp2 = Kelas::create(['unit_sekolah_id' => $unitSmp->id, 'nama_kelas' => 'VIII-A', 'tingkat' => '8']);

        $kelasSd1 = Kelas::create(['unit_sekolah_id' => $unitSd->id, 'nama_kelas' => 'I-A', 'tingkat' => '1', 'wali_kelas_id' => $tuSd->id]);
        $kelasSd2 = Kelas::create(['unit_sekolah_id' => $unitSd->id, 'nama_kelas' => 'II-A', 'tingkat' => '2']);

        // 5. Create SPP Tariffs
        TarifSpp::create([
            'unit_sekolah_id' => $unitSma->id,
            'tahun_ajaran_id' => $taSma->id,
            'kelas_id' => null,
            'nominal' => 350000,
            'kategori' => 'reguler',
            'keterangan' => 'Tarif Dasar SPP Bulanan SMA Reguler',
        ]);

        TarifSpp::create([
            'unit_sekolah_id' => $unitSmp->id,
            'tahun_ajaran_id' => $taSmp->id,
            'kelas_id' => null,
            'nominal' => 250000,
            'kategori' => 'reguler',
            'keterangan' => 'Tarif Dasar SPP Bulanan SMP Reguler',
        ]);

        TarifSpp::create([
            'unit_sekolah_id' => $unitSd->id,
            'tahun_ajaran_id' => $taSd->id,
            'kelas_id' => null,
            'nominal' => 180000,
            'kategori' => 'reguler',
            'keterangan' => 'Tarif Dasar SPP Bulanan SD Reguler',
        ]);

        // 6. Create Students
        $studentsDataSma = [
            ['nis' => 'SMA-2601', 'nisn' => '0081234001', 'nama' => 'Aditya Pratama Putra', 'jk' => 'L', 'wali' => 'Budi Santoso', 'telp' => '08121111001', 'kelas_id' => $kelasSma1->id],
            ['nis' => 'SMA-2602', 'nisn' => '0081234002', 'nama' => 'Aulia Zahra Nurhaliza', 'jk' => 'P', 'wali' => 'Irwan Nur', 'telp' => '08121111002', 'kelas_id' => $kelasSma1->id],
            ['nis' => 'SMA-2603', 'nisn' => '0081234003', 'nama' => 'Dimas Arya Pamungkas', 'jk' => 'L', 'wali' => 'Arya Gunawan', 'telp' => '08121111003', 'kelas_id' => $kelasSma1->id],
            ['nis' => 'SMA-2604', 'nisn' => '0081234004', 'nama' => 'Farhan Al Farizi', 'jk' => 'L', 'wali' => 'Farid Wajdi', 'telp' => '08121111004', 'kelas_id' => $kelasSma1->id],
            ['nis' => 'SMA-2605', 'nisn' => '0081234005', 'nama' => 'Gita Maharani Putri', 'jk' => 'P', 'wali' => 'Hendra Setiawan', 'telp' => '08121111005', 'kelas_id' => $kelasSma2->id],
            ['nis' => 'SMA-2606', 'nisn' => '0081234006', 'nama' => 'Muhammad Rizky Hidayat', 'jk' => 'L', 'wali' => 'Hidayatullah', 'telp' => '08121111006', 'kelas_id' => $kelasSma2->id],
        ];

        foreach ($studentsDataSma as $sd) {
            Siswa::create([
                'unit_sekolah_id' => $unitSma->id,
                'kelas_id' => $sd['kelas_id'],
                'nis' => $sd['nis'],
                'nisn' => $sd['nisn'],
                'nama' => $sd['nama'],
                'jenis_kelamin' => $sd['jk'],
                'nama_wali' => $sd['wali'],
                'telepon_wali' => $sd['telp'],
                'alamat' => 'Komplek Griya Indah Blok C',
                'status' => 'aktif',
            ]);
        }

        $studentsDataSmp = [
            ['nis' => 'SMP-2601', 'nisn' => '0091234001', 'nama' => 'Anisa Rahmadani', 'jk' => 'P', 'wali' => 'Rahmat Hidayat', 'telp' => '08122222001', 'kelas_id' => $kelasSmp1->id],
            ['nis' => 'SMP-2602', 'nisn' => '0091234002', 'nama' => 'Bayu Nugroho', 'jk' => 'L', 'wali' => 'Sunaryo Nugroho', 'telp' => '08122222002', 'kelas_id' => $kelasSmp1->id],
            ['nis' => 'SMP-2603', 'nisn' => '0091234003', 'nama' => 'Citra Kirana', 'jk' => 'P', 'wali' => 'Doni Kirana', 'telp' => '08122222003', 'kelas_id' => $kelasSmp1->id],
        ];

        foreach ($studentsDataSmp as $sd) {
            Siswa::create([
                'unit_sekolah_id' => $unitSmp->id,
                'kelas_id' => $sd['kelas_id'],
                'nis' => $sd['nis'],
                'nisn' => $sd['nisn'],
                'nama' => $sd['nama'],
                'jenis_kelamin' => $sd['jk'],
                'nama_wali' => $sd['wali'],
                'telepon_wali' => $sd['telp'],
                'alamat' => 'Jl. Mawar No. 45',
                'status' => 'aktif',
            ]);
        }

        $studentsDataSd = [
            ['nis' => 'SD-2601', 'nisn' => '0101234001', 'nama' => 'Ahmad Bilal Al Fatih', 'jk' => 'L', 'wali' => 'Fatih Usman', 'telp' => '08123333001', 'kelas_id' => $kelasSd1->id],
            ['nis' => 'SD-2602', 'nisn' => '0101234002', 'nama' => 'Nabila Azzahra', 'jk' => 'P', 'wali' => 'Agus Priyanto', 'telp' => '08123333002', 'kelas_id' => $kelasSd1->id],
        ];

        foreach ($studentsDataSd as $sd) {
            Siswa::create([
                'unit_sekolah_id' => $unitSd->id,
                'kelas_id' => $sd['kelas_id'],
                'nis' => $sd['nis'],
                'nisn' => $sd['nisn'],
                'nama' => $sd['nama'],
                'jenis_kelamin' => $sd['jk'],
                'nama_wali' => $sd['wali'],
                'telepon_wali' => $sd['telp'],
                'alamat' => 'Jl. Melati No. 12',
                'status' => 'aktif',
            ]);
        }

        // 7. Generate Monthly Bills using BillingGeneratorService
        $billingService = app(BillingGeneratorService::class);
        $paymentService = app(PaymentService::class);

        // Generate bills for July, August, September, October 2026
        $monthsToGenerate = [
            ['bulan' => 7, 'tahun' => 2026],
            ['bulan' => 8, 'tahun' => 2026],
            ['bulan' => 9, 'tahun' => 2026],
            ['bulan' => 10, 'tahun' => 2026],
        ];

        foreach ($monthsToGenerate as $m) {
            // SMA
            $billingService->generateMonthlyBilling([
                'unit_sekolah_id' => $unitSma->id,
                'tahun_ajaran_id' => $taSma->id,
                'bulan' => $m['bulan'],
                'tahun' => $m['tahun'],
                'jatuh_tempo' => "{$m['tahun']}-{$m['bulan']}-10",
                'user_id' => $tuSma->id,
            ]);

            // SMP
            $billingService->generateMonthlyBilling([
                'unit_sekolah_id' => $unitSmp->id,
                'tahun_ajaran_id' => $taSmp->id,
                'bulan' => $m['bulan'],
                'tahun' => $m['tahun'],
                'jatuh_tempo' => "{$m['tahun']}-{$m['bulan']}-10",
                'user_id' => $tuSmp->id,
            ]);

            // SD
            $billingService->generateMonthlyBilling([
                'unit_sekolah_id' => $unitSd->id,
                'tahun_ajaran_id' => $taSd->id,
                'bulan' => $m['bulan'],
                'tahun' => $m['tahun'],
                'jatuh_tempo' => "{$m['tahun']}-{$m['bulan']}-10",
                'user_id' => $tuSd->id,
            ]);
        }

        // 8. Simulate Some Payments using PaymentService
        // Student 1 SMA: Pay July, August, September (3 months) via Transfer
        $s1 = Siswa::where('nis', 'SMA-2601')->first();
        if ($s1) {
            $billsToPay = Tagihan::where('siswa_id', $s1->id)->whereIn('bulan', [7, 8, 9])->pluck('id')->toArray();
            $paymentService->processPayment([
                'unit_sekolah_id' => $unitSma->id,
                'siswa_id' => $s1->id,
                'user_id' => $tuSma->id,
                'tgl_bayar' => '2026-09-15',
                'metode_bayar' => 'transfer',
                'bank_tujuan' => 'Bank Syariah Indonesia (BSI)',
                'nomor_referensi' => 'TRX-BSI-98472891',
                'catatan' => 'Pembayaran SPP 3 bulan lunas',
                'tagihan_ids' => $billsToPay,
            ]);
        }

        // Student 2 SMA: Pay July (1 month) via Tunai
        $s2 = Siswa::where('nis', 'SMA-2602')->first();
        if ($s2) {
            $billsToPay2 = Tagihan::where('siswa_id', $s2->id)->where('bulan', 7)->pluck('id')->toArray();
            $paymentService->processPayment([
                'unit_sekolah_id' => $unitSma->id,
                'siswa_id' => $s2->id,
                'user_id' => $tuSma->id,
                'tgl_bayar' => '2026-07-10',
                'metode_bayar' => 'tunai',
                'catatan' => 'Pembayaran SPP Juli tunai di kasir TU',
                'tagihan_ids' => $billsToPay2,
            ]);
        }

        // Student 1 SMP: Pay July & August via Tunai
        $smp1 = Siswa::where('nis', 'SMP-2601')->first();
        if ($smp1) {
            $billsToPaySmp = Tagihan::where('siswa_id', $smp1->id)->whereIn('bulan', [7, 8])->pluck('id')->toArray();
            $paymentService->processPayment([
                'unit_sekolah_id' => $unitSmp->id,
                'siswa_id' => $smp1->id,
                'user_id' => $tuSmp->id,
                'tgl_bayar' => '2026-08-08',
                'metode_bayar' => 'tunai',
                'catatan' => 'Lunas 2 bulan',
                'tagihan_ids' => $billsToPaySmp,
            ]);
        }
    }
}
