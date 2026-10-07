@extends('layouts.app')

@section('subtitle', 'Detail Transaksi')
@section('content_header_title', 'Rincian Bukti Transaksi Pembayaran')
@section('content_header_subtitle', $pembayaran->kode_transaksi)

@section('content_header_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('pembayarans.kuitansi', $pembayaran->id) }}" target="_blank" class="btn btn-primary shadow-sm">
            <i class="bi bi-printer-fill me-1"></i> Cetak Kuitansi Resmi
        </a>
        <a href="{{ route('pembayarans.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
    </div>
@stop

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="badge bg-success-subtle text-success fs-6 border px-3 py-1 mb-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Transaksi Berhasil
                        </span>
                        <h3 class="fw-bold font-monospace text-primary mb-0">{{ $pembayaran->kode_transaksi }}</h3>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Waktu Transaksi:</small>
                        <strong class="fs-6">{{ $pembayaran->created_at->format('d F Y - H:i:s') }}</strong>
                    </div>
                </div>

                <div class="card-body px-4 py-3">
                    {{-- Summary Grid --}}
                    <div class="row g-3 p-3 bg-light rounded-3 mb-4">
                        <div class="col-md-3">
                            <small class="text-muted text-uppercase d-block fw-semibold">Nama Siswa</small>
                            <span class="fw-bold fs-6">{{ $pembayaran->siswa->nama ?? '-' }}</span>
                            <div class="text-muted small font-monospace">NIS: {{ $pembayaran->siswa->nis ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted text-uppercase d-block fw-semibold">Unit & Kelas</small>
                            <span class="fw-bold fs-6">{{ $pembayaran->unitSekolah->nama_unit ?? '-' }}</span>
                            <div class="text-muted small">Kelas: {{ $pembayaran->siswa->kelas->nama_kelas ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted text-uppercase d-block fw-semibold">Metode & Petugas</small>
                            <span class="badge {{ $pembayaran->metode_bayar === 'transfer' ? 'bg-info text-white' : 'bg-success' }} text-uppercase">
                                {{ $pembayaran->metode_bayar }}
                            </span>
                            @if($pembayaran->bank_tujuan)
                                <small class="d-block text-muted">{{ $pembayaran->bank_tujuan }}</small>
                            @endif
                            <div class="text-muted small mt-1">Petugas: {{ $pembayaran->petugas->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <small class="text-muted text-uppercase d-block fw-semibold">Total Dibayar</small>
                            <h3 class="fw-bold text-success mb-0">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</h3>
                            <small class="text-muted">{{ $pembayaran->details->count() }} Bulan tagihan SPP</small>
                        </div>
                    </div>

                    @if($pembayaran->catatan)
                        <div class="alert alert-secondary py-2 px-3 small mb-4">
                            <strong>Catatan:</strong> {{ $pembayaran->catatan }}
                        </div>
                    @endif

                    {{-- Details Table --}}
                    <h6 class="fw-bold text-body-secondary mb-3"><i class="bi bi-list-check me-1"></i>Rincian Tagihan yang Telah Dilunasi:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Item / Periode Tagihan SPP</th>
                                    <th>Tahun Ajaran</th>
                                    <th class="text-end">Nominal Tarif</th>
                                    <th class="text-end">Jumlah Dibayarkan</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pembayaran->details as $idx => $detail)
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td class="fw-bold">
                                            SPP Bulan {{ $detail->tagihan->nama_bulan ?? '-' }} {{ $detail->tagihan->tahun ?? '-' }}
                                        </td>
                                        <td>{{ $detail->tagihan->tahunAjaran->tahun ?? '-' }} ({{ $detail->tagihan->tahunAjaran->semester ?? '-' }})</td>
                                        <td class="text-end">Rp {{ number_format($detail->tagihan->nominal ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($detail->nominal_dibayar, 0, ',', '.') }}</td>
                                        <td class="text-center"><span class="badge bg-success">Lunas</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="4" class="text-end fw-bold">TOTAL PEMBAYARAN:</th>
                                    <th class="text-end fw-bold text-success fs-5">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Card Footer: Void Action --}}
                @can('pembayaran.void')
                    <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-shield-exclamation text-warning me-1"></i>Tindakan Void / Pembatalan akan mengembalikan status tagihan siswa menjadi BELUM LUNAS dan dicatat ke Audit Trail.</small>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#voidModal">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi (Void)
                        </button>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- Void Confirmation Modal --}}
    @can('pembayaran.void')
        <div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('pembayarans.void', $pembayaran->id) }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold" id="voidModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Pembatalan (Void)</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin membatalkan transaksi <strong>{{ $pembayaran->kode_transaksi }}</strong> sebesar <strong>Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</strong>?</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="alasan" class="form-control" rows="3" placeholder="Misal: Salah input metode bayar / salah pilih bulan siswa..." required minlength="5"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-trash3 me-1"></i> Ya, Batalkan Transaksi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@stop
