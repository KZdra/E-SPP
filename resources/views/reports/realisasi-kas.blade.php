@extends('layouts.app')

@section('subtitle', 'Realisasi Kas')
@section('content_header_title', 'Laporan Realisasi Kas Penerimaan SPP')
@section('content_header_subtitle', 'Laporan & Audit Keuangan')

@section('content_header_actions')
    <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm">
        <i class="bi bi-printer me-1"></i> Cetak Laporan
    </button>
@stop

@section('content_body')
    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.realisasi-kas') }}" class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Unit Sekolah</label>
                        <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ ($filters['unit_sekolah_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Metode Bayar</label>
                    <select name="metode_bayar" class="form-select form-select-sm">
                        <option value="">Semua Metode</option>
                        <option value="tunai" {{ ($filters['metode_bayar'] ?? '') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ ($filters['metode_bayar'] ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Grand Total Kas Masuk</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">
                        Rp {{ number_format($report['grand_total'], 0, ',', '.') }}
                    </h3>
                    <small class="text-muted">{{ $report['count'] }} Transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Penerimaan Tunai (Kas Fisik)</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1">
                        Rp {{ number_format($report['total_tunai'], 0, ',', '.') }}
                    </h4>
                    <small class="text-muted">Kasir Bendahara</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Penerimaan Transfer Bank</span>
                    <h4 class="fw-bold text-info mb-0 mt-1">
                        Rp {{ number_format($report['total_transfer'], 0, ',', '.') }}
                    </h4>
                    <small class="text-muted">Rekening Bank Sekolah</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-secondary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Periode Laporan</span>
                    <h6 class="fw-bold text-dark mb-0 mt-2">
                        {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
                    </h6>
                    <small class="text-muted">Filter aktif</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-journal-text text-primary me-2"></i>Jurnal Rincian Realisasi Penerimaan SPP</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Tanggal</th>
                            <th>No. Kuitansi</th>
                            <th>Siswa (NIS)</th>
                            <th>Unit & Kelas</th>
                            <th>Item Tagihan</th>
                            <th>Metode</th>
                            <th class="text-end">Jumlah Bayar</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['data'] as $p)
                            <tr>
                                <td>{{ $p->tgl_bayar ? $p->tgl_bayar->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('pembayarans.show', $p->id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                        {{ $p->kode_transaksi }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $p->siswa->nama ?? '-' }}</strong>
                                    <small class="d-block text-muted font-monospace">{{ $p->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $p->unitSekolah->kode_unit ?? '-' }}</span>
                                    <small>{{ $p->siswa->kelas->nama_kelas ?? '-' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $p->details->map(fn($d) => $d->tagihan ? $d->tagihan->nama_bulan.' '.$d->tagihan->tahun : '')->filter()->implode(', ') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $p->metode_bayar === 'transfer' ? 'bg-info text-white' : 'bg-success' }} text-uppercase">
                                        {{ $p->metode_bayar }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                                </td>
                                <td><small class="text-muted">{{ $p->petugas->name ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi pada periode tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="6" class="text-end fw-bold">TOTAL PENERIMAAN KAS:</th>
                            <th class="text-end fw-bold text-success fs-5">Rp {{ number_format($report['grand_total'], 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop
