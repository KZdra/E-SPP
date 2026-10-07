@extends('layouts.app')

@section('subtitle', 'Generate Tagihan SPP')
@section('content_header_title', 'Generate Tagihan SPP Bulanan Massal')
@section('content_header_subtitle', 'Otomatisasi Tagihan Siswa')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-calendar2-plus-fill text-success me-2"></i>Parameter Pembuatan Tagihan Massal</h5>
                </div>
                <form action="{{ route('tagihans.processGenerate') }}" method="POST" onsubmit="return confirm('Sistem akan membuat tagihan SPP untuk semua siswa aktif yang belum memiliki tagihan pada bulan & tahun ini. Lanjutkan?');">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                            <div>
                                <strong>Fitur Idempotent Aman:</strong>
                                <p class="mb-0 small">Siswa yang sudah memiliki tagihan pada bulan dan tahun yang sama tidak akan dibuatkan ganda (duplikasi dihindari secara otomatis).</p>
                            </div>
                        </div>

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
                                <label class="form-label fw-semibold">Target Bulan Tagihan <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-select" required>
                                    @foreach([
                                        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                                        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                                        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                                    ] as $num => $nama)
                                        <option value="{{ $num }}" {{ (old('bulan', date('n')) == $num) ? 'selected' : '' }}>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Kalender <span class="text-danger">*</span></label>
                                <input type="number" name="tahun" class="form-control" value="{{ old('tahun', date('Y')) }}" required min="2020" max="2035">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Batasan Kelas (Opsional)</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">-- Semua Kelas Aktif (Satu Unit) --</option>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            Kelas {{ $k->nama_kelas }} ({{ $k->unitSekolah->kode_unit ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Biarkan kosong untuk generate ke seluruh siswa aktif pada unit.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                                <input type="date" name="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo', date('Y-m-10')) }}">
                                <small class="text-muted">Standar tanggal 10 setiap bulan</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Tarif Standar Fallback (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal_default" class="form-control" placeholder="250000" value="{{ old('nominal_default', 250000) }}">
                                </div>
                                <small class="text-muted">Digunakan jika belum ada tarif SPP khusus yang disetting pada menu Tarif SPP.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('tagihans.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">
                            <i class="bi bi-play-circle-fill me-1"></i> Mulai Proses Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
