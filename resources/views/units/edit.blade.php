@extends('layouts.app')

@section('subtitle', 'Edit Unit Sekolah')
@section('content_header_title', 'Edit Unit Sekolah')
@section('content_header_subtitle', $unit->nama_unit)

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Perbarui Data Unit Sekolah</h5>
                </div>
                <form action="{{ route('units.update', $unit->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kode Unit <span class="text-danger">*</span></label>
                                <input type="text" name="kode_unit" class="form-control" value="{{ old('kode_unit', $unit->kode_unit) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap Unit Sekolah <span class="text-danger">*</span></label>
                                <input type="text" name="nama_unit" class="form-control" value="{{ old('nama_unit', $unit->nama_unit) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                                <select name="jenjang" class="form-select" required>
                                    @foreach(['SD', 'SMP', 'SMA', 'SMK'] as $j)
                                        <option value="{{ $j }}" {{ old('jenjang', $unit->jenjang) == $j ? 'selected' : '' }}>{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $unit->telepon) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email Sekolah</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $unit->email) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $unit->alamat) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isActiveSwitch">Status Unit Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
