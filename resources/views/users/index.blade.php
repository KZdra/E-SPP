@extends('layouts.app')

@section('subtitle', 'Kelola Pengguna')
@section('content_header_title', 'Pengguna & Hak Akses (RBAC)')
@section('content_header_subtitle', 'Yayasan Management')

@section('content_header_actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
    </a>
@stop

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-people-fill text-primary me-2"></i>Daftar Pengguna Sistem</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Nama & Email</th>
                            <th>Role / Hak Akses</th>
                            <th>Penugasan Unit</th>
                            <th>Kontak Telepon</th>
                            <th>Status Akun</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $u->adminlte_image() }}" alt="{{ $u->name }}" class="rounded-circle me-2" width="36" height="36">
                                        <div>
                                            <div class="fw-bold text-body-secondary">{{ $u->name }}</div>
                                            <small class="text-muted">{{ $u->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($u->roles as $role)
                                        @php
                                            $badgeClass = match($role->name) {
                                                'Admin Yayasan' => 'bg-danger',
                                                'Kepala Sekolah' => 'bg-warning text-dark',
                                                'Petugas TU' => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fs-6">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($u->unitSekolah)
                                        <span class="badge bg-primary-subtle text-primary border">
                                            {{ $u->unitSekolah->nama_unit }} ({{ $u->unitSekolah->kode_unit }})
                                        </span>
                                    @else
                                        <span class="badge bg-dark-subtle text-dark border">
                                            <i class="bi bi-globe me-1"></i> Global Yayasan (Semua Unit)
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $u->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $u->status_aktif ? 'bg-success' : 'bg-danger' }}">
                                        {{ $u->status_aktif ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('users.edit', $u->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun pengguna ini?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus / Nonaktifkan">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
