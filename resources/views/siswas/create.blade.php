@extends('layouts.app')

@section('subtitle', 'Tambah Siswa')
@section('content_header_title', 'Pendaftaran Siswa Baru')
@section('content_header_subtitle', 'Data Induk Siswa')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-person-plus-fill text-primary me-2"></i>Formulir Biodata Siswa</h5>
                </div>
                <form action="{{ route('siswas.store') }}" method="POST">
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
                                <label class="form-label fw-semibold">Rombel / Kelas <span class="text-danger">*</span></label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            Kelas {{ $k->nama_kelas }} ({{ $k->unitSekolah->kode_unit ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Induk Siswa (NIS) <span class="text-danger">*</span></label>
                                <input type="text" name="nis" class="form-control" placeholder="Misal: SMA-2601" value="{{ old('nis') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NISN (Nomor Induk Siswa Nasional)</label>
                                <input type="text" name="nisn" class="form-control" placeholder="10 digit nomor nasional" value="{{ old('nisn') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap sesuai akta / ijazah" value="{{ old('nama') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Orang Tua / Wali</label>
                                <input type="text" name="nama_wali" class="form-control" placeholder="Nama ayah / ibu / wali" value="{{ old('nama_wali') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor WhatsApp / Telepon Wali</label>
                                <input type="text" name="telepon_wali" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('telepon_wali') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Alamat Tempat Tinggal</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat domisili siswa">{{ old('alamat') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status Siswa <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="lulus" {{ old('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                                    <option value="pindah" {{ old('status') == 'pindah' ? 'selected' : '' }}>Pindah</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('siswas.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Data Siswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
