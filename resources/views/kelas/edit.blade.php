@extends('layouts.app')

@section('subtitle', 'Edit Kelas')
@section('content_header_title', 'Edit Data Kelas')
@section('content_header_subtitle', $kelas->nama_kelas)

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Perbarui Data Kelas</h5>
                </div>
                <form action="{{ route('kelas.update', $kelas->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Unit Sekolah <span class="text-danger">*</span></label>
                                <select name="unit_sekolah_id" class="form-select" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id', $kelas->unit_sekolah_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kelas" class="form-control" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Tingkat / Grade</label>
                                <input type="text" name="tingkat" class="form-control" value="{{ old('tingkat', $kelas->tingkat) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Jurusan / Program Keahlian (Khusus SMK)</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="">-- Non-Kejuruan / Umum (Tanpa Jurusan) --</option>
                                    @foreach($jurusans as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_id', $kelas->jurusan_id) == $j->id ? 'selected' : '' }}>
                                            {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }} ({{ $j->unitSekolah?->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Wali Kelas (Guru / Pembina)</label>
                                <select name="wali_kelas_id" class="form-select">
                                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
