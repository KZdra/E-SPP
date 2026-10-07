@extends('layouts.app')

@section('subtitle', 'Laporan Tunggakan')
@section('content_header_title', 'Laporan Tunggakan SPP Siswa')
@section('content_header_subtitle', 'Monitoring & Penagihan')

@section('content_header_actions')
    <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm">
        <i class="bi bi-printer me-1"></i> Cetak Laporan
    </button>
@stop

@section('content_body')
    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.tunggakan') }}" class="row g-2 align-items-center">
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
                    <label class="form-label small fw-semibold text-muted mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" {{ ($filters['tahun_ajaran_id'] ?? '') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->tahun }} ({{ $ta->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Pilih Kelas</label>
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Nilai Tunggakan</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">
                        Rp {{ number_format($report['total_nominal'], 0, ',', '.') }}
                    </h3>
                    <small class="text-muted">Akumulasi piutang SPP</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Siswa Tertunggak</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">
                        {{ number_format($report['total_siswa_tertunggak'], 0, ',', '.') }} Siswa
                    </h3>
                    <small class="text-muted">Memiliki tagihan belum lunas</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Tagihan Belum Lunas</span>
                    <h3 class="fw-bold text-info mb-0 mt-1">
                        {{ number_format($report['total_tagihan_tertunggak'], 0, ',', '.') }} Bulan
                    </h3>
                    <small class="text-muted">Invoice belum terbayar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Daftar Siswa dengan Tunggakan SPP</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Unit Sekolah</th>
                            <th class="text-center">Jml Bulan</th>
                            <th>Rincian Bulan Tertunggak</th>
                            <th class="text-end">Total Tunggakan</th>
                            <th class="text-center">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['data'] as $item)
                            <tr>
                                <td class="font-monospace fw-bold text-primary">{{ $item['siswa']->nis }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item['siswa']->nama }}</div>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> Wali: {{ $item['siswa']->telepon_wali ?? '-' }}</small>
                                </td>
                                <td><span class="badge bg-secondary">Kelas {{ $item['kelas'] }}</span></td>
                                <td><span class="badge bg-primary-subtle text-primary border">{{ $item['unit'] }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-danger fs-6">{{ $item['jumlah_bulan'] }} Bulan</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $item['daftar_bulan'] }}</small>
                                </td>
                                <td class="text-end fw-bold text-danger fs-6">
                                    Rp {{ number_format($item['total_tunggakan'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @can('pembayaran.create')
                                        <a href="{{ route('pembayarans.create', ['siswa_id' => $item['siswa']->id]) }}" class="btn btn-sm btn-outline-success" title="Buka Kasir">
                                            <i class="bi bi-cash-stack"></i> Bayar
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                    Alhamdulillah, tidak ada tunggakan SPP pada filter yang dipilih!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="6" class="text-end fw-bold">TOTAL NILAI TUNGGAKAN:</th>
                            <th class="text-end fw-bold text-danger fs-5">Rp {{ number_format($report['total_nominal'], 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop
