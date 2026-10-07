@extends('layouts.app')

@section('subtitle', 'Matriks SPP Kelas')
@section('content_header_title', 'Rekapitulasi Matriks SPP per Kelas')
@section('content_header_subtitle', 'Monitoring Matriks 12 Bulan')

@section('content_header_actions')
    <div class="d-flex gap-2">
        @if($selectedKelasId && $selectedTaId)
            <a href="{{ route('reports.matriks-kelas.excel', ['kelas_id' => $selectedKelasId, 'tahun_ajaran_id' => $selectedTaId, 'unit_id' => $unitId]) }}" class="btn btn-success shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (.xlsx)
            </a>
        @endif
        <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Matriks
        </button>
    </div>
@stop

@section('content_body')
    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.matriks-kelas') }}" class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Unit Sekolah</label>
                        <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Pilih Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }} ({{ $k->unitSekolah->kode_unit ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select name="tahun_ajaran_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" {{ $selectedTaId == $ta->id ? 'selected' : '' }}>
                                {{ $ta->tahun }} ({{ $ta->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">
                        <i class="bi bi-arrow-repeat"></i> Muat Matriks
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($report)
        {{-- Matrix Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-body-secondary">
                        <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Matriks Pembayaran: Kelas {{ $report['kelas']->nama_kelas }} ({{ $report['kelas']->unitSekolah->nama_unit ?? '' }})
                    </h5>
                    <small class="text-muted">Tahun Ajaran {{ $report['tahun_ajaran']->tahun }} (Semester {{ $report['tahun_ajaran']->semester }})</small>
                </div>
                <div class="small">
                    <span class="badge bg-success me-1 px-2 py-1"><i class="bi bi-check-lg"></i> L = Lunas</span>
                    <span class="badge bg-danger me-1 px-2 py-1"><i class="bi bi-x-lg"></i> B = Belum Lunas</span>
                    <span class="badge bg-light text-dark border px-2 py-1">- = Belum Ada Tagihan</span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center font-monospace" style="font-size: 0.82rem;">
                        <thead class="table-light font-sans-serif">
                            <tr class="small text-muted text-uppercase">
                                <th style="width: 35px;">No</th>
                                <th style="width: 90px;">NIS</th>
                                <th style="width: 170px;" class="text-start">Nama Siswa</th>
                                @foreach($report['months_header'] as $mh)
                                    <th style="width: 50px;">{{ $mh['label'] }}</th>
                                @endforeach
                                <th style="width: 110px;" class="text-end">Terbayar</th>
                                <th style="width: 110px;" class="text-end">Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTerbayar = 0;
                                $grandTunggakan = 0;
                            @endphp
                            @forelse($report['matriks'] as $idx => $row)
                                @php
                                    $grandTerbayar += $row['total_terbayar'];
                                    $grandTunggakan += $row['total_tunggakan'];
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="text-primary fw-bold">{{ $row['siswa']->nis }}</td>
                                    <td class="text-start font-sans-serif fw-semibold">{{ $row['siswa']->nama }}</td>

                                    {{-- Month columns --}}
                                    @foreach($report['months_header'] as $mh)
                                        @php
                                            $mState = $row['months'][$mh['bulan']] ?? null;
                                        @endphp
                                        <td>
                                            @if($mState && $mState['has_bill'])
                                                @if($mState['status'] === 'lunas')
                                                    <span class="badge bg-success" title="Lunas ({{ $mState['tgl_bayar'] ?? 'Tercatat' }})">
                                                        L
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger" title="Belum Lunas: Rp {{ number_format($mState['nominal'], 0, ',', '.') }}">
                                                        B
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted opacity-50">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="text-end fw-bold text-success font-sans-serif">
                                        Rp {{ number_format($row['total_terbayar'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold {{ $row['total_tunggakan'] > 0 ? 'text-danger' : 'text-muted' }} font-sans-serif">
                                        Rp {{ number_format($row['total_tunggakan'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center py-4 text-muted font-sans-serif">
                                        Belum ada data siswa aktif di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light font-sans-serif">
                            <tr>
                                <th colspan="15" class="text-end fw-bold">TOTAL REKAPITULASI KELAS:</th>
                                <th class="text-end fw-bold text-success">Rp {{ number_format($grandTerbayar, 0, ',', '.') }}</th>
                                <th class="text-end fw-bold text-danger">Rp {{ number_format($grandTunggakan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm text-center py-4">
            <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
            Silakan pilih Kelas dan Tahun Ajaran untuk menampilkan matriks rekapitulasi SPP.
        </div>
    @endif
@stop
