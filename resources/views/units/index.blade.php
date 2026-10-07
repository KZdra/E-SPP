@extends('layouts.app')

@section('subtitle', 'Unit Sekolah')
@section('content_header_title', 'Master Unit Sekolah')
@section('content_header_subtitle', 'Yayasan Management')

@section('content_header_actions')
    <a href="{{ route('units.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Unit Sekolah
    </a>
@stop

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-buildings text-primary me-2"></i>Daftar Unit Sekolah / Lembaga</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Kode Unit</th>
                            <th>Nama Unit / Sekolah</th>
                            <th>Jenjang</th>
                            <th>Kontak</th>
                            <th>Statistik</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $u)
                            <tr>
                                <td><span class="badge bg-primary fs-6">{{ $u->kode_unit }}</span></td>
                                <td>
                                    <div class="fw-bold fs-6">{{ $u->nama_unit }}</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $u->alamat ?? '-' }}</small>
                                </td>
                                <td><span class="badge bg-info text-white text-uppercase">{{ $u->jenjang }}</span></td>
                                <td>
                                    <small class="d-block"><i class="bi bi-telephone"></i> {{ $u->telepon ?? '-' }}</small>
                                    <small class="text-muted"><i class="bi bi-envelope"></i> {{ $u->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ $u->siswas_count }} Siswa</span>
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ $u->kelas_count }} Kelas</span>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $u->users_count }} Pegawai</span>
                                </td>
                                <td>
                                    <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('units.edit', $u->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('units.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?');" class="d-inline">
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
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data unit sekolah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
