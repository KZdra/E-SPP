<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\UnitSekolah;
use App\Models\Pembayaran;
use App\Models\TahunAjaran;
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

    public function test_yayasan_admin_can_access_executive_dashboard_and_management_menus(): void
    {
        $yayasan = User::where('email', 'yayasan@sekolah.sch.id')->first();
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
        $kepsek = User::where('email', 'kepsek.sma@sekolah.sch.id')->first();
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
        $tu = User::where('email', 'tu.sma@sekolah.sch.id')->first();
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
        $tu = User::where('email', 'tu.sma@sekolah.sch.id')->first();
        $unit = UnitSekolah::where('kode_unit', 'SMA')->first();
        $ta = TahunAjaran::where('unit_sekolah_id', $unit->id)->first();

        $billingService = app(BillingGeneratorService::class);

        // First run for November 2026
        $res1 = $billingService->generateMonthlyBilling([
            'unit_sekolah_id' => $unit->id,
            'tahun_ajaran_id' => $ta->id,
            'bulan' => 11,
            'tahun' => 2026,
            'user_id' => $tu->id,
        ]);

        $this->assertGreaterThan(0, $res1['created']);

        // Second run for same November 2026 should create 0 and skip all
        $res2 = $billingService->generateMonthlyBilling([
            'unit_sekolah_id' => $unit->id,
            'tahun_ajaran_id' => $ta->id,
            'bulan' => 11,
            'tahun' => 2026,
            'user_id' => $tu->id,
        ]);

        $this->assertEquals(0, $res2['created']);
        $this->assertEquals($res1['created'], $res2['skipped']);
    }

    public function test_void_payment_reverses_bill_and_records_audit(): void
    {
        $tu = User::where('email', 'tu.sma@sekolah.sch.id')->first();
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
        $yayasan = User::where('email', 'yayasan@sekolah.sch.id')->first();

        $this->actingAs($yayasan)->get('/reports/realisasi-kas')->assertStatus(200);
        $this->actingAs($yayasan)->get('/reports/tunggakan')->assertStatus(200);
        $this->actingAs($yayasan)->get('/reports/matriks-kelas')->assertStatus(200);
    }
}
