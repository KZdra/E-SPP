@extends('layouts.app')

@section('subtitle', 'Penyesuaian Tagihan')
@section('content_header_title', 'Penyesuaian Tagihan SPP')
@section('content_header_subtitle', $tagihan->siswa->nama ?? '')

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Penyesuaian Nominal Tagihan Siswa</h5>
                </div>
                <form action="{{ route('tagihans.update', $tagihan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            <div class="row">
                                <div class="col-6 small">
                                    <span class="text-muted d-block">Siswa:</span>
                                    <strong>{{ $tagihan->siswa->nama }}</strong> ({{ $tagihan->siswa->nis }})
                                </div>
                                <div class="col-6 small">
                                    <span class="text-muted d-block">Periode:</span>
                                    <strong>{{ $tagihan->nama_bulan }} {{ $tagihan->tahun }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nominal Tagihan SPP (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal" class="form-control" value="{{ old('nominal', (int)$tagihan->nominal) }}" required min="0" step="1000">
                            </div>
                            <small class="text-muted">Gunakan nilai ini jika siswa mendapatkan beasiswa, potongan, atau penyesuaian khusus.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo', $tagihan->jatuh_tempo ? $tagihan->jatuh_tempo->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('tagihans.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Penyesuaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
