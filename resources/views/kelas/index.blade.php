@extends('layouts.app')

@section('subtitle', 'Data Kelas')
@section('content_header_title', 'Master Data Kelas')
@section('content_header_subtitle', 'Data Akademik')

@section('content_header_actions')
    <a href="{{ route('kelas.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kelas
    </a>
@stop

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-door-open-fill text-primary me-2"></i>Daftar Kelas</h5>
            @if(auth()->user()->isYayasan())
                <form method="GET" action="{{ route('kelas.index') }}" class="d-flex align-items-center gap-2">
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
                            <th>Nama Kelas</th>
                            <th>Jurusan (SMK)</th>
                            <th>Tingkat</th>
                            <th>Wali Kelas</th>
                            <th>Jumlah Siswa Aktif</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelas as $k)
                            <tr>
                                <td>
                                    <span class="badge bg-primary me-1">{{ $k->unitSekolah->kode_unit ?? '-' }}</span>
                                    {{ $k->unitSekolah->nama_unit ?? '-' }}
                                </td>
                                <td class="fw-bold fs-6">{{ $k->nama_kelas }}</td>
                                <td>
                                    @if($k->jurusan)
                                        <span class="badge bg-success-subtle text-success border">
                                            <i class="bi bi-diagram-3 me-1"></i>{{ $k->jurusan->kode_jurusan }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary border">Tingkat {{ $k->tingkat ?? '-' }}</span></td>
                                <td>
                                    @if($k->waliKelas)
                                        <i class="bi bi-person-badge text-primary me-1"></i> {{ $k->waliKelas->name }}
                                    @else
                                        <span class="text-muted fst-italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border fs-6">
                                        {{ $k->active_siswas_count }} Siswa
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus data kelas ini?');" class="d-inline">
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
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
