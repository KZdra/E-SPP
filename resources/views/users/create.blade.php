@extends('layouts.app')

@section('subtitle', 'Tambah Pengguna')
@section('content_header_title', 'Tambah Pengguna Baru')
@section('content_header_subtitle', 'Yayasan RBAC Management')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-person-plus text-primary me-2"></i>Formulir Pengguna Baru</h5>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" placeholder="Misal: tu.sma, kepsek.sma, yayasan" value="{{ old('username') }}" required autofocus>
                                <small class="text-muted">Gunakan huruf kecil, angka, titik, atau garis bawah tanpa spasi.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Misal: Siti Rahmawati, S.E" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Alamat Email (Opsional)</label>
                                <input type="email" name="email" class="form-control" placeholder="tu.sma@sekolah.sch.id" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                                <select name="role" id="roleSelect" class="form-select" required onchange="handleRoleChange(this.value)">
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="unitWrapper">
                                <label class="form-label fw-semibold">Penugasan Unit Sekolah</label>
                                <select name="unit_sekolah_id" id="unitSelect" class="form-select">
                                    <option value="">-- Pilih Unit Sekolah (Khusus Kepsek & TU) --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Admin Yayasan memiliki hak lintas unit (dikosongkan).</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="status_aktif" id="statusAktifSwitch" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="statusAktifSwitch">Status Akun Aktif</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
function handleRoleChange(role) {
    const unitSelect = document.getElementById('unitSelect');
    if (role === 'Admin Yayasan') {
        unitSelect.value = '';
        unitSelect.disabled = true;
    } else {
        unitSelect.disabled = false;
    }
}
document.addEventListener('DOMContentLoaded', () => {
    handleRoleChange(document.getElementById('roleSelect').value);
});
</script>
@endpush
