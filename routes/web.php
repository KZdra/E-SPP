<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UnitSekolahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TarifSppController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ReportController;

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    // 1. Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);

    // 2. Yayasan Management Modules (Super Admin)
    Route::middleware(['permission:unit.view'])->group(function () {
        Route::resource('units', UnitSekolahController::class);
    });

    Route::middleware(['permission:user.view'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(['permission:tarif.manage'])->group(function () {
        Route::resource('tarifs', TarifSppController::class);
    });

    Route::middleware(['permission:audit.view'])->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // 3. Master Data Sekolah (Tahun Ajaran, Kelas, Siswa)
    Route::middleware(['permission:tahun-ajaran.view'])->group(function () {
        Route::resource('tahun-ajarans', TahunAjaranController::class);
        Route::post('tahun-ajarans/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajarans.activate');
    });

    Route::middleware(['permission:kelas.view'])->group(function () {
        Route::resource('kelas', KelasController::class);
    });

    Route::middleware(['permission:siswa.view'])->group(function () {
        Route::resource('siswas', SiswaController::class);
    });

    // 4. Operasional SPP (Tagihan & Pembayaran Kasir)
    Route::middleware(['permission:tagihan.view'])->group(function () {
        Route::get('tagihans', [TagihanController::class, 'index'])->name('tagihans.index');
        Route::get('tagihans/{tagihan}/edit', [TagihanController::class, 'edit'])->name('tagihans.edit');
        Route::put('tagihans/{tagihan}', [TagihanController::class, 'update'])->name('tagihans.update');
    });

    Route::middleware(['permission:tagihan.generate'])->group(function () {
        Route::get('tagihans/generate', [TagihanController::class, 'generate'])->name('tagihans.generate');
        Route::post('tagihans/generate', [TagihanController::class, 'processGenerate'])->name('tagihans.processGenerate');
    });

    Route::middleware(['permission:pembayaran.create'])->group(function () {
        Route::get('pembayarans/create', [PembayaranController::class, 'create'])->name('pembayarans.create');
        Route::get('pembayarans/api/unpaid-bills', [PembayaranController::class, 'getUnpaidBills'])->name('pembayarans.getUnpaidBills');
        Route::post('pembayarans', [PembayaranController::class, 'store'])->name('pembayarans.store');
    });

    Route::middleware(['permission:pembayaran.view'])->group(function () {
        Route::get('pembayarans', [PembayaranController::class, 'index'])->name('pembayarans.index');
        Route::get('pembayarans/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayarans.show');
        Route::get('pembayarans/{pembayaran}/kuitansi', [PembayaranController::class, 'kuitansi'])->name('pembayarans.kuitansi');
    });

    Route::middleware(['permission:pembayaran.void'])->group(function () {
        Route::post('pembayarans/{pembayaran}/void', [PembayaranController::class, 'void'])->name('pembayarans.void');
    });

    // 5. Laporan & Monitoring (Kepsek & Yayasan)
    Route::middleware(['permission:laporan.realisasi'])->group(function () {
        Route::get('reports/realisasi-kas', [ReportController::class, 'realisasiKas'])->name('reports.realisasi-kas');
    });

    Route::middleware(['permission:laporan.tunggakan'])->group(function () {
        Route::get('reports/tunggakan', [ReportController::class, 'tunggakan'])->name('reports.tunggakan');
    });

    Route::middleware(['permission:laporan.rekapitulasi'])->group(function () {
        Route::get('reports/matriks-kelas', [ReportController::class, 'matriksKelas'])->name('reports.matriks-kelas');
    });
});
