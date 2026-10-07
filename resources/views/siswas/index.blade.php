@extends('layouts.app')

@section('subtitle', 'Data Siswa')
@section('content_header_title', 'Master Data Siswa')
@section('content_header_subtitle', 'Akademik & Kesiswaan')

@section('content_header_actions')
    @can('siswa.create')
        <a href="{{ route('siswas.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-person-plus me-1"></i> Tambah Siswa Baru
        </a>
    @endcan
@stop

@section('content_body')
    {{-- Filter & Search Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('siswas.index') }}" class="row g-2 align-items-center">
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
                <div class="col-md-3">
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="pindah" {{ request('status') == 'pindah' ? 'selected' : '' }}>Pindah</option>
                    </select>
                </div>
                <div class="col-md-4 ms-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIS, atau NISN..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
                        @if(request()->hasAny(['unit_id', 'kelas_id', 'status', 'search']))
                            <a href="{{ route('siswas.index') }}" class="btn btn-outline-danger" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>NIS / NISN</th>
                            <th>Nama Siswa</th>
                            <th>Unit & Kelas</th>
                            <th>L/P</th>
                            <th>Wali Murid</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $s)
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary">{{ $s->nis }}</span>
                                    <small class="d-block text-muted">NISN: {{ $s->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $s->nama }}</div>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> {{ $s->telepon_wali ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border me-1">{{ $s->unitSekolah->kode_unit ?? '-' }}</span>
                                    <span class="badge bg-secondary-subtle text-secondary border">Kelas {{ $s->kelas->nama_kelas ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $s->jenis_kelamin === 'L' ? 'bg-primary' : 'bg-danger' }}">
                                        {{ $s->jenis_kelamin }}
                                    </span>
                                </td>
                                <td>{{ $s->nama_wali ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeStatus = match($s->status) {
                                            'aktif' => 'bg-success',
                                            'lulus' => 'bg-info text-white',
                                            'pindah' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeStatus }} text-capitalize">{{ $s->status }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('siswas.show', $s->id) }}" class="btn btn-outline-info" title="Detail Profil & Tagihan">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('pembayaran.create')
                                            <a href="{{ route('pembayarans.create', ['siswa_id' => $s->id]) }}" class="btn btn-outline-success" title="Bayar SPP Langsung">
                                                <i class="bi bi-credit-card-2-front"></i>
                                            </a>
                                        @endcan
                                        @can('siswa.edit')
                                            <a href="{{ route('siswas.edit', $s->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('siswa.delete')
                                            <form action="{{ route('siswas.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini (Soft Delete)?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Data siswa tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswas->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>
@stop
