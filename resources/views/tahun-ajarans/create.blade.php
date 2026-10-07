@extends('layouts.app')

@section('subtitle', 'Tambah Tahun Ajaran')
@section('content_header_title', 'Tambah Tahun Ajaran')
@section('content_header_subtitle', 'Data Akademik')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-calendar-plus text-primary me-2"></i>Formulir Tahun Ajaran Baru</h5>
                </div>
                <form action="{{ route('tahun-ajarans.store') }}" method="POST">
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
                                <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" name="tahun" class="form-control" placeholder="Contoh: 2026/2027" value="{{ old('tahun') }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1">
                                    <label class="form-check-label fw-semibold" for="isActiveSwitch">Langsung jadikan periode aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('tahun-ajarans.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Periode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
