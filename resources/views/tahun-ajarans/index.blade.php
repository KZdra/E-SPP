@extends('layouts.app')

@section('subtitle', 'Tahun Ajaran')
@section('content_header_title', 'Master Tahun Ajaran')
@section('content_header_subtitle', 'Data Akademik')

@section('content_header_actions')
    <a href="{{ route('tahun-ajarans.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Tahun Ajaran
    </a>
@stop

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-calendar3 text-primary me-2"></i>Daftar Periode Akademik</h5>
            @if(auth()->user()->isYayasan())
                <form method="GET" action="{{ route('tahun-ajarans.index') }}" class="d-flex align-items-center gap-2">
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
                            <th>Semester</th>
                            <th>Status Aktif</th>
                            <th class="text-center">Aksi Aktivasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tahunAjarans as $ta)
                            <tr>
                                <td>
                                    <span class="badge bg-primary me-1">{{ $ta->unitSekolah->kode_unit ?? '-' }}</span>
                                    {{ $ta->unitSekolah->nama_unit ?? '-' }}
                                </td>
                                <td class="fw-bold fs-6">{{ $ta->tahun }}</td>
                                <td><span class="badge bg-info text-white">{{ $ta->semester }}</span></td>
                                <td>
                                    @if($ta->is_active)
                                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i> Aktif Berjalan</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$ta->is_active)
                                        <form action="{{ route('tahun-ajarans.activate', $ta->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-toggle-on me-1"></i> Jadikan Aktif
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success small fw-semibold"><i class="bi bi-check-lg"></i> Periode Utama</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data tahun ajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
