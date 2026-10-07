@extends('layouts.app')

@section('subtitle', 'Tambah Tarif SPP')
@section('content_header_title', 'Tambah Tarif SPP')
@section('content_header_subtitle', 'Master Keuangan')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-plus-circle text-primary me-2"></i>Formulir Tarif SPP Baru</h5>
                </div>
                <form action="{{ route('tarifs.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->tahun }} ({{ $ta->semester }}) - {{ $ta->unitSekolah->kode_unit ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khusus Jurusan SMK (Opsional)</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="">-- Berlaku untuk Semua Jurusan / Non-SMK --</option>
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                            Jurusan {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }} ({{ $j->unitSekolah->kode_unit ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Untuk SMK: Semua kelas pada jurusan ini akan mengikuti tarif ini.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khusus Kelas Tertentu (Opsional)</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">-- Berlaku untuk Seluruh Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            Kelas {{ $k->nama_kelas }} ({{ $k->unitSekolah->kode_unit ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori Tarif <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="reguler" {{ old('kategori') == 'reguler' ? 'selected' : '' }}>Reguler</option>
                                    <option value="beasiswa" {{ old('kategori') == 'beasiswa' ? 'selected' : '' }}>Beasiswa</option>
                                    <option value="khusus" {{ old('kategori') == 'khusus' ? 'selected' : '' }}>Khusus</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nominal SPP (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal" class="form-control" placeholder="Misal: 350000" value="{{ old('nominal') }}" required min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Misal: SPP Standar Bulanan TA 2026/2027" value="{{ old('keterangan') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Tarif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
