@extends('adminlte::page')

@section('title', 'Data Jurusan SMK')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">Master Data Jurusan (SMK)</h1>
            <p class="text-muted mb-0">Kelola program keahlian / jurusan SMK untuk pembedaan tarif SPP & pengelompokan kelas</p>
        </div>
        <a href="{{ route('jurusans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jurusan Baru
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-0 font-weight-bold"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Daftar Jurusan / Program Keahlian</h5>
                </div>
                @if(auth()->user()->isYayasan())
                <div class="col-md-6">
                    <form method="GET" action="{{ route('jurusans.index') }}" class="d-flex justify-content-end gap-2">
                        <select name="unit_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">-- Semua Unit Sekolah --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            @if(auth()->user()->isYayasan())
                                <th>Unit Sekolah</th>
                            @endif
                            <th>Kode Jurusan</th>
                            <th>Nama Program / Jurusan</th>
                            <th>Bidang Keahlian</th>
                            <th class="text-center">Jumlah Kelas</th>
                            <th>Keterangan</th>
                            <th class="text-center" width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurusans as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                @if(auth()->user()->isYayasan())
                                    <td><span class="badge bg-secondary">{{ $item->unitSekolah?->nama_unit }}</span></td>
                                @endif
                                <td><span class="badge bg-primary fs-6">{{ $item->kode_jurusan }}</span></td>
                                <td class="fw-bold">{{ $item->nama_jurusan }}</td>
                                <td>{{ $item->bidang_keahlian ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $item->kelas_count }} Kelas</span>
                                </td>
                                <td><small class="text-muted">{{ $item->keterangan ?? '-' }}</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('jurusans.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <form action="{{ route('jurusans.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurusan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isYayasan() ? 8 : 7 }}" class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                                    Belum ada data jurusan. Klik tombol "Tambah Jurusan Baru" untuk menambahkan program keahlian SMK.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
