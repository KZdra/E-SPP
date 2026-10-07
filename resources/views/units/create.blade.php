@extends('layouts.app')

@section('subtitle', 'Tambah Unit Sekolah')
@section('content_header_title', 'Tambah Unit Sekolah')
@section('content_header_subtitle', 'Yayasan Management')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-building-add text-primary me-2"></i>Formulir Unit Sekolah Baru</h5>
                </div>
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kode Unit <span class="text-danger">*</span></label>
                                <input type="text" name="kode_unit" class="form-control" placeholder="Misal: SMA, SMP, SD" value="{{ old('kode_unit') }}" required autofocus>
                                <small class="text-muted">Kode unik identifikasi sekolah (digunakan di invoice)</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap Unit Sekolah <span class="text-danger">*</span></label>
                                <input type="text" name="nama_unit" class="form-control" placeholder="Misal: SMA Islam Terpadu Bina Cendekia" value="{{ old('nama_unit') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                                <select name="jenjang" class="form-select" required>
                                    <option value="">-- Pilih Jenjang --</option>
                                    <option value="SD" {{ old('jenjang') == 'SD' ? 'selected' : '' }}>SD</option>
                                    <option value="SMP" {{ old('jenjang') == 'SMP' ? 'selected' : '' }}>SMP</option>
                                    <option value="SMA" {{ old('jenjang') == 'SMA' ? 'selected' : '' }}>SMA</option>
                                    <option value="SMK" {{ old('jenjang') == 'SMK' ? 'selected' : '' }}>SMK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="text" name="telepon" class="form-control" placeholder="021-xxxxxxx" value="{{ old('telepon') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email Sekolah</label>
                                <input type="email" name="email" class="form-control" placeholder="admin@sekolah.sch.id" value="{{ old('email') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat jalan, kelurahan, kecamatan, kota">{{ old('alamat') }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="isActiveSwitch">Status Unit Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
