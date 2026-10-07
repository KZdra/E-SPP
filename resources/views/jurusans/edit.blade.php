@extends('adminlte::page')

@section('title', 'Edit Jurusan SMK')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">Edit Jurusan: {{ $jurusan->kode_jurusan }}</h1>
            <p class="text-muted mb-0">Perbarui informasi program keahlian</p>
        </div>
        <a href="{{ route('jurusans.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 font-weight-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Jurusan SMK</h5>
                </div>
                <form action="{{ route('jurusans.update', $jurusan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="unit_sekolah_id" class="form-label font-weight-bold">Unit Sekolah <span class="text-danger">*</span></label>
                            <select name="unit_sekolah_id" id="unit_sekolah_id" class="form-select @error('unit_sekolah_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Unit Sekolah --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_sekolah_id', $jurusan->unit_sekolah_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama_unit }} ({{ strtoupper($unit->jenjang) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="kode_jurusan" class="form-label font-weight-bold">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="kode_jurusan" id="kode_jurusan" class="form-control @error('kode_jurusan') is-invalid @enderror" value="{{ old('kode_jurusan', $jurusan->kode_jurusan) }}" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="nama_jurusan" class="form-label font-weight-bold">Nama Lengkap Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jurusan" id="nama_jurusan" class="form-control @error('nama_jurusan') is-invalid @enderror" value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bidang_keahlian" class="form-label font-weight-bold">Bidang / Program Keahlian</label>
                            <input type="text" name="bidang_keahlian" id="bidang_keahlian" class="form-control" value="{{ old('bidang_keahlian', $jurusan->bidang_keahlian) }}">
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label font-weight-bold">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $jurusan->keterangan) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Perbarui Jurusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
