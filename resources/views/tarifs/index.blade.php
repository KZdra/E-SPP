@extends('layouts.app')

@section('subtitle', 'Tarif SPP')
@section('content_header_title', 'Master Tarif Dasar SPP')
@section('content_header_subtitle', 'Yayasan & Unit Keuangan')

@section('content_header_actions')
    <a href="{{ route('tarifs.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Tarif SPP
    </a>
@stop

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-cash-coin text-primary me-2"></i>Daftar Tarif SPP</h5>
            @if(auth()->user()->isYayasan())
                <form method="GET" action="{{ route('tarifs.index') }}" class="d-flex align-items-center gap-2">
                    <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Unit Sekolah</th>
                            <th>Tahun Ajaran</th>
                            <th>Berlaku Untuk</th>
                            <th>Kategori</th>
                            <th class="text-end">Nominal / Bulan</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tarifs as $t)
                            <tr>
                                <td><span class="badge bg-primary fs-6">{{ $t->unitSekolah->kode_unit ?? '-' }}</span> {{ $t->unitSekolah->nama_unit ?? '-' }}</td>
                                <td>{{ $t->tahunAjaran->tahun ?? '-' }} ({{ $t->tahunAjaran->semester ?? '-' }})</td>
                                <td>
                                    @if($t->kelas)
                                        <span class="badge bg-info-subtle text-info border"><i class="bi bi-door-open me-1"></i>Kelas {{ $t->kelas->nama_kelas }}</span>
                                    @elseif($t->jurusan)
                                        <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-diagram-3 me-1"></i>Jurusan {{ $t->jurusan->kode_jurusan }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border"><i class="bi bi-check-all me-1"></i>Umum (Seluruh Siswa)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $t->kategori === 'reguler' ? 'bg-secondary' : 'bg-warning text-dark' }} text-uppercase">
                                        {{ $t->kategori }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    Rp {{ number_format($t->nominal, 0, ',', '.') }}
                                </td>
                                <td><small class="text-muted">{{ $t->keterangan ?? '-' }}</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('tarifs.edit', $t->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('tarifs.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus tarif ini?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data tarif SPP.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
