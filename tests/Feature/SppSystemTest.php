<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\UnitSekolah;
use App\Models\Pembayaran;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Services\PaymentService;
use App\Services\BillingGeneratorService;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class SppSystemTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_using_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'yayasan',
            'password' => 'password',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
    }

    public function test_yayasan_admin_can_access_executive_dashboard_and_management_menus(): void
    {
        $yayasan = User::where('username', 'yayasan')->first();
        $this->assertNotNull($yayasan);

        $response = $this->actingAs($yayasan)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee('Admin Yayasan');

        // Can access units
        $resUnits = $this->actingAs($yayasan)->get('/units');
        $resUnits->assertStatus(200);

        // Can access users
        $resUsers = $this->actingAs($yayasan)->get('/users');
        $resUsers->assertStatus(200);

        // Can access audit logs
        $resAudit = $this->actingAs($yayasan)->get('/audit-logs');
        $resAudit->assertStatus(200);
    }

    public function test_kepala_sekolah_can_access_monitoring_but_cannot_access_unit_management(): void
    {
        $kepsek = User::where('username', 'kepsek.sma')->first();
        $this->assertNotNull($kepsek);

        // Can access dashboard
        $response = $this->actingAs($kepsek)->get('/');
        $response->assertStatus(200);

        // Can access reports
        $resReports = $this->actingAs($kepsek)->get('/reports/realisasi-kas');
        $resReports->assertStatus(200);

        // Cannot access Unit Management (Forbidden 403)
        $resUnits = $this->actingAs($kepsek)->get('/units');
        $resUnits->assertStatus(403);

        // Cannot create payment (Forbidden 403)
        $resKasir = $this->actingAs($kepsek)->get('/pembayarans/create');
        $resKasir->assertStatus(403);
    }

    public function test_tu_can_access_kasir_and_process_payment_atomically(): void
    {
        $tu = User::where('username', 'tu.sma')->first();
        $this->assertNotNull($tu);

        // Can access kasir
        $resKasir = $this->actingAs($tu)->get('/pembayarans/create');
        $resKasir->assertStatus(200);

        // Find student with unpaid bill
        $unpaidBill = Tagihan::where('status', 'belum_lunas')->first();
        $this->assertNotNull($unpaidBill);
        $siswa = $unpaidBill->siswa;

        $paymentService = app(PaymentService::class);
        $pembayaran = $paymentService->processPayment([
            'unit_sekolah_id' => $siswa->unit_sekolah_id,
            'siswa_id' => $siswa->id,
            'user_id' => $tu->id,
            'tgl_bayar' => date('Y-m-d'),
            'metode_bayar' => 'tunai',
            'catatan' => 'Test bayar unit test',
            'tagihan_ids' => [$unpaidBill->id],
        ]);

        $this->assertNotNull($pembayaran);
        $this->assertStringStartsWith('INV/', $pembayaran->kode_transaksi);
        $this->assertEquals('lunas', $unpaidBill->fresh()->status);
        $this->assertEquals($unpaidBill->nominal, $unpaidBill->fresh()->nominal_terbayar);
    }

    public function test_billing_generator_is_idempotent(): void
    {
        $tu = User::where('username', 'tu.sma')->first();
        $unit = UnitSekolah::where('kode_unit', 'SMA')->first();
        $ta = TahunAjaran::where('unit_sekolah_id', $unit->id)->first();

        $billingService = app(BillingGeneratorService::class);

        // Ensure clean state for test month (December 2026)
        Tagihan::where('unit_sekolah_id', $unit->id)->where('bulan', 12)->where('tahun', 2026)->forceDelete();

        // First run for December 2026
        $res1 = $billingService->generateMonthlyBilling([
            'unit_sekolah_id' => $unit->id,
            'tahun_ajaran_id' => $ta->id,
            'bulan' => 12,
            'tahun' => 2026,
            'user_id' => $tu->id,
        ]);

        $this->assertGreaterThan(0, $res1['created']);

        // Second run for same December 2026 should create 0 and skip all
        $res2 = $billingService->generateMonthlyBilling([
            'unit_sekolah_id' => $unit->id,
            'tahun_ajaran_id' => $ta->id,
            'bulan' => 12,
            'tahun' => 2026,
            'user_id' => $tu->id,
        ]);

        $this->assertEquals(0, $res2['created']);
        $this->assertEquals($res1['created'], $res2['skipped']);
    }

    public function test_void_payment_reverses_bill_and_records_audit(): void
    {
        $tu = User::where('username', 'tu.sma')->first();
        $payment = Pembayaran::with('details.tagihan')->latest('id')->first();
        $this->assertNotNull($payment);

        $tagihan = $payment->details->first()->tagihan;
        $this->assertEquals('lunas', $tagihan->status);

        $paymentService = app(PaymentService::class);
        $voidSuccess = $paymentService->voidPayment($payment->id, $tu->id, 'Testing pembatalan transaksi');

        $this->assertTrue($voidSuccess);
        $this->assertEquals('belum_lunas', $tagihan->fresh()->status);

        // Check activity log
        $logExists = Activity::where('log_name', 'pembatalan_transaksi')->exists();
        $this->assertTrue($logExists);
    }

    public function test_reports_pages_render_successfully(): void
    {
        $yayasan = User::where('username', 'yayasan')->first();

        $this->actingAs($yayasan)->get('/reports/realisasi-kas')->assertStatus(200);
        $this->actingAs($yayasan)->get('/reports/tunggakan')->assertStatus(200);
        $this->actingAs($yayasan)->get('/reports/matriks-kelas')->assertStatus(200);
    }

    public function test_smk_students_have_differential_tariffs_by_jurusan(): void
    {
        $rplStudent = Siswa::where('nis', 'SMK-2601')->first();
        $tkjStudent = Siswa::where('nis', 'SMK-2602')->first();
        $tkrStudent = Siswa::where('nis', 'SMK-2603')->first();

        $this->assertNotNull($rplStudent);
        $this->assertNotNull($tkjStudent);
        $this->assertNotNull($tkrStudent);

        $billRpl = Tagihan::where('siswa_id', $rplStudent->id)->where('bulan', 10)->first();
        $billTkj = Tagihan::where('siswa_id', $tkjStudent->id)->where('bulan', 10)->first();
        $billTkr = Tagihan::where('siswa_id', $tkrStudent->id)->where('bulan', 10)->first();

        $this->assertEquals(350000, (int)$billRpl->nominal);
        $this->assertEquals(375000, (int)$billTkj->nominal);
        $this->assertEquals(400000, (int)$billTkr->nominal);
    }

    public function test_remote_datatables_ajax_returns_json_for_siswas_and_pembayarans(): void
    {
        $tu = User::where('username', 'tu.sma')->first();

        // Test siswas datatable with search payload
        $dtParams = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'SMA', 'regex' => 'false']
        ];
        $responseSiswa = $this->actingAs($tu)
            ->getJson('/siswas?' . http_build_query($dtParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseSiswa->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Test tagihans datatable with search payload
        $responseTagihan = $this->actingAs($tu)
            ->getJson('/tagihans?' . http_build_query($dtParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $responseTagihan->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Test pembayarans datatable with search payload
        $responsePembayaran = $this->actingAs($tu)
            ->getJson('/pembayarans?' . http_build_query($dtParams), ['X-Requested-With' => 'XMLHttpRequest']);
        $responsePembayaran->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_excel_export_using_phpspreadsheet(): void
    {
        $yayasan = User::where('username', 'yayasan')->first();

        $response = $this->actingAs($yayasan)->get('/reports/realisasi-kas/export-excel');
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_download_siswa_excel_template(): void
    {
        $tu = User::where('username', 'tu.sma')->first();

        $response = $this->actingAs($tu)->get('/siswas/download-template');
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Template_Import_Siswa', $response->headers->get('Content-Disposition'));
    }

    public function test_scholarship_discount_in_billing_generator(): void
    {
        $smaUnit = UnitSekolah::where('kode_unit', 'SMA')->first();
        $ta = TahunAjaran::where('is_active', true)->first();
        $kelas = Kelas::where('unit_sekolah_id', $smaUnit->id)->first();

        // Create student with 50% scholarship
        $siswaBeasiswa = Siswa::create([
            'unit_sekolah_id' => $smaUnit->id,
            'kelas_id' => $kelas->id,
            'nis' => 'TEST-SCH-01',
            'nama' => 'Siswa Beasiswa 50%',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'kategori_spp' => 'beasiswa',
            'diskon_tipe' => 'persen',
            'diskon_nilai' => 50,
        ]);

        // Create student with Yatim (100% free)
        $siswaYatim = Siswa::create([
            'unit_sekolah_id' => $smaUnit->id,
            'kelas_id' => $kelas->id,
            'nis' => 'TEST-SCH-02',
            'nama' => 'Siswa Yatim Piatu',
            'jenis_kelamin' => 'P',
            'status' => 'aktif',
            'kategori_spp' => 'yatim',
            'diskon_tipe' => 'persen',
            'diskon_nilai' => 100,
        ]);

        $service = app(BillingGeneratorService::class);
        $service->generateMonthlyBilling([
            'unit_sekolah_id' => $smaUnit->id,
            'tahun_ajaran_id' => $ta->id,
            'bulan' => 11,
            'tahun' => 2026,
            'nominal_default' => 250000,
        ]);

        $billBeasiswa = Tagihan::where('siswa_id', $siswaBeasiswa->id)
            ->where('bulan', 11)->where('tahun', 2026)->first();
        $billYatim = Tagihan::where('siswa_id', $siswaYatim->id)
            ->where('bulan', 11)->where('tahun', 2026)->first();

        $this->assertNotNull($billBeasiswa);
        $this->assertEquals(125000, (int)$billBeasiswa->nominal); // 50% of 250,000

        $this->assertNotNull($billYatim);
        $this->assertEquals(0, (int)$billYatim->nominal); // 0 (Free)
        $this->assertEquals('lunas', $billYatim->status);
    }

    public function test_kenaikan_kelas_and_kelulusan_flow(): void
    {
        $tu = User::where('username', 'tu.sma')->first();
        $smaUnit = UnitSekolah::where('kode_unit', 'SMA')->first();
        $kelasAsal = Kelas::where('unit_sekolah_id', $smaUnit->id)->first();
        $kelasTujuan = Kelas::create([
            'unit_sekolah_id' => $smaUnit->id,
            'nama_kelas' => 'XI MIPA 99',
            'tingkat' => '11',
        ]);

        $siswa = Siswa::create([
            'unit_sekolah_id' => $smaUnit->id,
            'kelas_id' => $kelasAsal->id,
            'nis' => 'TEST-NAIK-01',
            'nama' => 'Siswa Siap Naik Kelas',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        // Process promotion
        $response = $this->actingAs($tu)->post('/kelas/kenaikan-kelas', [
            'unit_sekolah_id' => $smaUnit->id,
            'kelas_asal_id' => $kelasAsal->id,
            'aksi' => 'naik_kelas',
            'kelas_tujuan_id' => $kelasTujuan->id,
            'siswa_ids' => [$siswa->id],
        ]);

        $response->assertRedirect();
        $siswa->refresh();
        $this->assertEquals($kelasTujuan->id, $siswa->kelas_id);

        // Process graduation
        $responseGrad = $this->actingAs($tu)->post('/kelas/kenaikan-kelas', [
            'unit_sekolah_id' => $smaUnit->id,
            'kelas_asal_id' => $kelasTujuan->id,
            'aksi' => 'lulus',
            'siswa_ids' => [$siswa->id],
        ]);

        $responseGrad->assertRedirect();
        $siswa->refresh();
        $this->assertEquals('lulus', $siswa->status);
    }

    public function test_artisan_spp_generate_monthly_command(): void
    {
        $this->artisan('spp:generate-monthly', [
            '--month' => 12,
            '--year' => 2026,
        ])->assertExitCode(0);
    }
}
