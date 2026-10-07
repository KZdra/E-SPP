@extends('layouts.app')

@section('subtitle', 'Edit Pengguna')
@section('content_header_title', 'Edit Data Pengguna')
@section('content_header_subtitle', $user->name)

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Perbarui Akun & Akses Pengguna</h5>
                </div>
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Alamat Email (Login) <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                                <select name="role" id="roleSelect" class="form-select" required onchange="handleRoleChange(this.value)">
                                    @php $currentRole = $user->roles->first()?->name; @endphp
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}" {{ old('role', $currentRole) == $r->name ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="unitWrapper">
                                <label class="form-label fw-semibold">Penugasan Unit Sekolah</label>
                                <select name="unit_sekolah_id" id="unitSelect" class="form-select">
                                    <option value="">-- Pilih Unit Sekolah --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id', $user->unit_sekolah_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="status_aktif" id="statusAktifSwitch" value="1" {{ old('status_aktif', $user->status_aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="statusAktifSwitch">Status Akun Aktif</label>
                                </div>
                            </div>
                            <div class="col-12"><hr class="my-2"><small class="text-muted">Biarkan kolom password kosong jika tidak ingin mengubah password.</small></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Baru (Opsional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tetap">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
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
