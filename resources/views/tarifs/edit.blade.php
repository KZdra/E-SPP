@extends('layouts.app')

@section('subtitle', 'Edit Tarif SPP')
@section('content_header_title', 'Edit Tarif SPP')
@section('content_header_subtitle', $tarif->unitSekolah->nama_unit ?? '')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Perbarui Tarif SPP</h5>
                </div>
                <form action="{{ route('tarifs.update', $tarif->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit Sekolah <span class="text-danger">*</span></label>
                                <select name="unit_sekolah_id" class="form-select" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id', $tarif->unit_sekolah_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $tarif->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->tahun }} ({{ $ta->semester }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khusus Kelas (Opsional)</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">-- Berlaku untuk Seluruh Kelas (Umum) --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $tarif->kelas_id) == $k->id ? 'selected' : '' }}>
                                            Kelas {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori Tarif <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    @foreach(['reguler', 'beasiswa', 'khusus'] as $kat)
                                        <option value="{{ $kat }}" {{ old('kategori', $tarif->kategori) == $kat ? 'selected' : '' }}>
                                            {{ ucfirst($kat) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nominal SPP (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal" class="form-control" value="{{ old('nominal', (int)$tarif->nominal) }}" required min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $tarif->keterangan) }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
