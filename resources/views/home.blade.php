@extends('layouts.app')

@section('subtitle', 'Dashboard')
@section('content_header_title', 'Dashboard')
@section('content_header_subtitle', $user->roles->first()?->name ?? 'User')

@section('content_header_actions')
    @if($user->isYayasan())
        <form method="GET" action="{{ route('home') }}" class="d-flex align-items-center gap-2">
            <span class="text-muted fw-semibold small text-nowrap"><i class="bi bi-funnel-fill"></i> Filter Unit:</span>
            <select name="unit_id" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                <option value="">Semua Unit Sekolah (Konsolidasi Yayasan)</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                        {{ $unit->nama_unit }} ({{ $unit->kode_unit }})
                    </option>
                @endforeach
            </select>
        </form>
    @else
        <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm">
            <i class="bi bi-building me-1"></i> {{ $user->unitSekolah?->nama_unit ?? 'Unit Sekolah' }}
        </span>
    @endif
@stop

@section('content_body')

    {{-- Role Notice Banner --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 bg-gradient text-white shadow-sm" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i>Selamat Datang, {{ $user->name }}!</h4>
                        <p class="mb-0 opacity-75">
                            Role: <span class="badge bg-warning text-dark fw-bold">{{ $user->roles->first()?->name }}</span> | 
                            Unit Kerja: <strong>{{ $user->unitSekolah?->nama_unit ?? 'Yayasan (Pusat Konsolidasi)' }}</strong>
                        </p>
                    </div>
                    @can('pembayaran.create')
                        <div class="d-flex gap-2">
                            <a href="{{ route('pembayarans.create') }}" class="btn btn-light fw-bold text-primary shadow-sm">
                                <i class="bi bi-credit-card-2-front-fill me-1"></i> Buka Kasir SPP
                            </a>
                            <a href="{{ route('tagihans.generate') }}" class="btn btn-outline-light fw-semibold">
                                <i class="bi bi-calendar2-plus me-1"></i> Generate Tagihan
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Penerimaan Bulan Ini --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card border-0 h-100 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Penerimaan Bulan Ini</div>
                            <h4 class="fw-bold text-success mb-0 mt-1">
                                Rp {{ number_format($stats['penerimaan_bulan_ini'], 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">Hari ini: Rp {{ number_format($stats['penerimaan_hari_ini'], 0, ',', '.') }}</small>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Tunggakan --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card border-0 h-100 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Tunggakan SPP</div>
                            <h4 class="fw-bold text-danger mb-0 mt-1">
                                Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">Siswa tertunggak aktif</small>
                        </div>
                        <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-octagon-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Tingkat Kepatuhan SPP --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card border-0 h-100 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Kepatuhan SPP Bulan Ini</div>
                            <h4 class="fw-bold text-info mb-0 mt-1">
                                {{ $stats['persen_kepatuhan'] }}%
                            </h4>
                            <small class="text-muted">{{ $stats['tagihan_lunas_count'] }} dari {{ $stats['tagihan_total_count'] }} tagihan lunas</small>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="bi bi-pie-chart-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Siswa Aktif --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card border-0 h-100 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Siswa Aktif</div>
                            <h4 class="fw-bold text-primary mb-0 mt-1">
                                {{ number_format($stats['total_siswa'], 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">Terdaftar di sistem</small>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics & Breakdown Row --}}
    <div class="row g-3 mb-4">
        {{-- Left: Revenue Trend Chart (6 Months) --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-body-secondary">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i>Tren Realisasi Penerimaan SPP (6 Bulan Terakhir)
                    </h5>
                    <span class="badge bg-secondary-subtle text-secondary">Statistik Bulanan</span>
                </div>
                <div class="card-body">
                    <div style="height: 280px; position: relative;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Multi-Unit Overview (Yayasan) OR Quick Operations (TU/Kepsek) --}}
        <div class="col-lg-4">
            @if($user->isYayasan() && !empty($stats['unit_breakdown']))
                {{-- Yayasan Multi-Unit Breakdown --}}
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h5 class="fw-bold mb-0 text-body-secondary">
                            <i class="bi bi-buildings text-warning me-2"></i>Perbandingan Unit Sekolah
                        </h5>
                    </div>
                    <div class="card-body p-0 pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted">
                                        <th>Unit</th>
                                        <th class="text-end">Penerimaan</th>
                                        <th class="text-end">Tunggakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['unit_breakdown'] as $ub)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary me-1">{{ $ub['unit']->kode_unit }}</span>
                                                <small class="fw-bold">{{ $ub['unit']->nama_unit }}</small>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $ub['total_siswa'] }} siswa</div>
                                            </td>
                                            <td class="text-end fw-semibold text-success small">
                                                Rp {{ number_format($ub['penerimaan_bulan_ini'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-end fw-semibold text-danger small">
                                                Rp {{ number_format($ub['total_tunggakan'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                {{-- School Unit Quick Actions / Summary for Kepsek / TU --}}
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h5 class="fw-bold mb-0 text-body-secondary">
                            <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Akses Pintas Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @can('pembayaran.create')
                                <a href="{{ route('pembayarans.create') }}" class="btn btn-outline-primary text-start p-3 shadow-sm rounded-3">
                                    <i class="bi bi-cash-coin fs-4 float-start me-3 text-primary"></i>
                                    <div class="fw-bold">Kasir Pembayaran SPP</div>
                                    <small class="text-muted">Proses transaksi tunai / transfer siswa</small>
                                </a>
                            @endcan

                            @can('tagihan.generate')
                                <a href="{{ route('tagihans.generate') }}" class="btn btn-outline-success text-start p-3 shadow-sm rounded-3">
                                    <i class="bi bi-file-earmark-plus fs-4 float-start me-3 text-success"></i>
                                    <div class="fw-bold">Generate Tagihan Bulanan</div>
                                    <small class="text-muted">Terbitkan invoice SPP massal kelas</small>
                                </a>
                            @endcan

                            @can('laporan.realisasi')
                                <a href="{{ route('reports.realisasi-kas') }}" class="btn btn-outline-info text-start p-3 shadow-sm rounded-3">
                                    <i class="bi bi-journal-check fs-4 float-start me-3 text-info"></i>
                                    <div class="fw-bold">Laporan Realisasi Kas</div>
                                    <small class="text-muted">Lihat rekapitulasi penerimaan kas harian</small>
                                </a>
                            @endcan

                            @can('laporan.tunggakan')
                                <a href="{{ route('reports.tunggakan') }}" class="btn btn-outline-danger text-start p-3 shadow-sm rounded-3">
                                    <i class="bi bi-exclamation-triangle fs-4 float-start me-3 text-danger"></i>
                                    <div class="fw-bold">Laporan Tunggakan Siswa</div>
                                    <small class="text-muted">Monitoring siswa yang belum bayar SPP</small>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Transactions Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-body-secondary">
                        <i class="bi bi-receipt-cutoff text-primary me-2"></i>Transaksi Pembayaran Terbaru
                    </h5>
                    <a href="{{ route('pembayarans.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua Riwayat <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th>No. Kuitansi</th>
                                    <th>Unit</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tgl Bayar</th>
                                    <th>Metode</th>
                                    <th class="text-end">Total Bayar</th>
                                    <th>Petugas</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_payments'] as $payment)
                                    <tr>
                                        <td class="fw-bold font-monospace text-primary">
                                            {{ $payment->kode_transaksi }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                {{ $payment->unitSekolah->kode_unit ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $payment->siswa->nama ?? '-' }}</div>
                                            <small class="text-muted">NIS: {{ $payment->siswa->nis ?? '-' }}</small>
                                        </td>
                                        <td>{{ $payment->siswa->kelas->nama_kelas ?? '-' }}</td>
                                        <td>{{ $payment->tgl_bayar ? $payment->tgl_bayar->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            <span class="badge {{ $payment->metode_bayar === 'transfer' ? 'bg-info text-white' : 'bg-success' }} text-uppercase">
                                                {{ $payment->metode_bayar }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($payment->total_bayar, 0, ',', '.') }}
                                        </td>
                                        <td><small class="text-muted">{{ $payment->petugas->name ?? '-' }}</small></td>
                                        <td class="text-center">
                                            <a href="{{ route('pembayarans.kuitansi', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak Kuitansi">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <a href="{{ route('pembayarans.show', $payment->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                            Belum ada transaksi pembayaran yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['chart_labels']) !!},
                datasets: [{
                    label: 'Realisasi Kas SPP (Rp)',
                    data: {!! json_encode($stats['chart_data']) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y;
                                return ' Realisasi: Rp ' + val.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
