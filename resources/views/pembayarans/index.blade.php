@extends('layouts.app')

@section('subtitle', 'Riwayat Pembayaran')
@section('content_header_title', 'Riwayat Transaksi Pembayaran SPP')
@section('content_header_subtitle', 'Operasional & Kasir')

@section('content_header_actions')
    @can('pembayaran.create')
        <a href="{{ route('pembayarans.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-credit-card-2-front-fill me-1"></i> Buka Kasir Pembayaran
        </a>
    @endcan
@stop

@section('content_body')
    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('pembayarans.index') }}" class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-3">
                        <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" placeholder="Dari Tgl" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" placeholder="Sampai Tgl" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <select name="metode_bayar" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Metode</option>
                        <option value="tunai" {{ request('metode_bayar') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ request('metode_bayar') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="col-md-3 ms-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="No. Kuitansi / Nama siswa..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        @if(request()->hasAny(['unit_id', 'start_date', 'end_date', 'metode_bayar', 'search']))
                            <a href="{{ route('pembayarans.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>No. Kuitansi</th>
                            <th>Tgl Bayar</th>
                            <th>Siswa</th>
                            <th>Unit & Kelas</th>
                            <th>Rincian Tagihan</th>
                            <th>Metode</th>
                            <th class="text-end">Total Bayar</th>
                            <th>Petugas TU</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('pembayarans.show', $p->id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                        {{ $p->kode_transaksi }}
                                    </a>
                                </td>
                                <td class="text-nowrap">{{ $p->tgl_bayar ? $p->tgl_bayar->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $p->siswa->nama ?? '-' }}</div>
                                    <small class="text-muted font-monospace">NIS: {{ $p->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border me-1">{{ $p->unitSekolah->kode_unit ?? '-' }}</span>
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
                                <td class="text-end fw-bold text-success fs-6">
                                    Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                                </td>
                                <td><small class="text-muted">{{ $p->petugas->name ?? '-' }}</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pembayarans.kuitansi', $p->id) }}" target="_blank" class="btn btn-outline-secondary" title="Cetak Kuitansi">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('pembayarans.show', $p->id) }}" class="btn btn-outline-primary" title="Detail Transaksi">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat transaksi pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pembayarans->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $pembayarans->links() }}
            </div>
        @endif
    </div>
@stop
