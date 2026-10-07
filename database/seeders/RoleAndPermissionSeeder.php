<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions list
        $permissions = [
            // Dashboard
            'dashboard.yayasan',
            'dashboard.sekolah',

            // Master Unit & Global
            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Tahun Ajaran & Tarif SPP
            'tahun-ajaran.view',
            'tahun-ajaran.manage',
            'tarif.view',
            'tarif.manage',

            // Master Siswa, Kelas, Jurusan (Lokal TU)
            'jurusan.view',
            'jurusan.create',
            'jurusan.edit',
            'jurusan.delete',
            'kelas.view',
            'kelas.create',
            'kelas.edit',
            'kelas.delete',
            'siswa.view',
            'siswa.create',
            'siswa.edit',
            'siswa.delete',

            // Tagihan SPP
            'tagihan.view',
            'tagihan.generate',
            'tagihan.edit',
            'tagihan.delete',
            'tagihan.view-own',

            // Pembayaran SPP (Kasir)
            'pembayaran.view',
            'pembayaran.create',
            'pembayaran.void',
            'kuitansi.print',
            'kuitansi.print-own',

            // Laporan & Monitoring
            'laporan.realisasi',
            'laporan.tunggakan',
            'laporan.rekapitulasi',
            'audit.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Role: Admin Yayasan (Super Admin Yayasan)
        $roleYayasan = Role::firstOrCreate(['name' => 'Admin Yayasan', 'guard_name' => 'web']);
        $roleYayasan->syncPermissions(Permission::all());

        // 2. Role: Kepala Sekolah (Kepsek) - Monitoring & Approval
        $roleKepsek = Role::firstOrCreate(['name' => 'Kepala Sekolah', 'guard_name' => 'web']);
        $roleKepsek->syncPermissions([
            'dashboard.sekolah',
            'jurusan.view',
            'kelas.view',
            'siswa.view',
            'tahun-ajaran.view',
            'tarif.view',
            'tagihan.view',
            'pembayaran.view',
            'kuitansi.print',
            'laporan.realisasi',
            'laporan.tunggakan',
            'laporan.rekapitulasi',
        ]);

        // 3. Role: Petugas TU / Bendahara Sekolah (Operasional)
        $roleTU = Role::firstOrCreate(['name' => 'Petugas TU', 'guard_name' => 'web']);
        $roleTU->syncPermissions([
            'dashboard.sekolah',
            'jurusan.view',
            'jurusan.create',
            'jurusan.edit',
            'kelas.view',
            'kelas.create',
            'kelas.edit',
            'siswa.view',
            'siswa.create',
            'siswa.edit',
            'siswa.delete',
            'tarif.view',
            'tagihan.view',
            'tagihan.generate',
            'pembayaran.view',
            'pembayaran.create',
            'kuitansi.print',
            'laporan.realisasi',
            'laporan.tunggakan',
            'laporan.rekapitulasi',
        ]);

        // 4. Role: Siswa / Wali Murid (Future Expansion)
        $roleSiswa = Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        $roleSiswa->syncPermissions([
            'tagihan.view-own',
            'kuitansi.print-own',
        ]);
    }
}
