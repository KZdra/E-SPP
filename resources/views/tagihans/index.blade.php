@extends('layouts.app')

@section('subtitle', 'Data Tagihan')
@section('content_header_title', 'Data Tagihan SPP Siswa')
@section('content_header_subtitle', 'Operasional & Billing')

@section('content_header_actions')
    @can('tagihan.generate')
        <a href="{{ route('tagihans.generate') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-calendar2-plus me-1"></i> Generate Tagihan Massal
        </a>
    @endcan
@stop

@section('content_body')
    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('tagihans.index') }}" class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-2">
                        <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->kode_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $num => $nama)
                            <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @foreach([2025, 2026, 2027] as $thn)
                            <option value="{{ $thn }}" {{ request('tahun') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>Kelas {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 ms-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Cari siswa..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Billing Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Siswa</th>
                            <th>Unit & Kelas</th>
                            <th>Periode SPP</th>
                            <th>Tahun Ajaran</th>
                            <th class="text-end">Nominal Tagihan</th>
                            <th class="text-end">Terbayar</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tagihans as $t)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $t->siswa->nama ?? '-' }}</div>
                                    <small class="text-muted font-monospace">NIS: {{ $t->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border me-1">{{ $t->unitSekolah->kode_unit ?? '-' }}</span>
                                    <small>{{ $t->siswa->kelas->nama_kelas ?? '-' }}</small>
                                </td>
                                <td class="fw-semibold text-primary">
                                    {{ $t->nama_bulan }} {{ $t->tahun }}
                                </td>
                                <td><small class="text-muted">{{ $t->tahunAjaran->tahun ?? '-' }}</small></td>
                                <td class="text-end fw-bold">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                                <td class="text-end text-success">Rp {{ number_format($t->nominal_terbayar, 0, ',', '.') }}</td>
                                <td><small class="text-muted">{{ $t->jatuh_tempo ? $t->jatuh_tempo->format('d/m/Y') : '-' }}</small></td>
                                <td>
                                    @if($t->status === 'lunas')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-clock me-1"></i> Belum Lunas</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if($t->status === 'belum_lunas')
                                            @can('pembayaran.create')
                                                <a href="{{ route('pembayarans.create', ['siswa_id' => $t->siswa_id]) }}" class="btn btn-outline-success" title="Bayar Sekarang">
                                                    <i class="bi bi-credit-card-2-front"></i>
                                                </a>
                                            @endcan
                                            <a href="{{ route('tagihans.edit', $t->id) }}" class="btn btn-outline-warning" title="Ubah Nominal / Diskon">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small"><i class="bi bi-check-all text-success"></i> Selesai</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data tagihan ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tagihans->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $tagihans->links() }}
            </div>
        @endif
    </div>
@stop
