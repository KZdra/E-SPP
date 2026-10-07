@extends('layouts.app')

@section('subtitle', 'Tambah Kelas')
@section('content_header_title', 'Tambah Data Kelas')
@section('content_header_subtitle', 'Data Akademik')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-plus-circle text-primary me-2"></i>Formulir Kelas Baru</h5>
                </div>
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Unit Sekolah <span class="text-danger">*</span></label>
                                <select name="unit_sekolah_id" class="form-select" required>
                                    <option value="">-- Pilih Unit Sekolah --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kelas" class="form-control" placeholder="Misal: X-RPL-1, X-TKJ, VII-A" value="{{ old('nama_kelas') }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Tingkat / Grade</label>
                                <input type="text" name="tingkat" class="form-control" placeholder="Misal: 10, 7, 1" value="{{ old('tingkat') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Jurusan / Program Keahlian (Khusus SMK)</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="">-- Non-Kejuruan / Umum (Tanpa Jurusan) --</option>
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }} ({{ $j->unitSekolah?->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Untuk SMK, kelas wajib dikaitkan dengan jurusan agar tarif SPP otomatis mengikuti tarif jurusan.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Wali Kelas (Guru / Pembina)</label>
                                <select name="wali_kelas_id" class="form-select">
                                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" {{ old('wali_kelas_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }} ({{ $t->unitSekolah->kode_unit ?? 'Global' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
