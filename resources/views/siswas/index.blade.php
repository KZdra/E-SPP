@extends('layouts.app')

@section('subtitle', 'Data Siswa')
@section('content_header_title', 'Master Data Siswa')
@section('content_header_subtitle', 'Akademik & Kesiswaan (Remote Server-Side DataTables)')

@section('content_header_actions')
    @can('siswa.create')
        <a href="{{ route('siswas.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-person-plus me-1"></i> Tambah Siswa Baru
        </a>
    @endcan
@stop

@section('content_body')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-bold">Filter Unit Sekolah:</label>
                        <select id="filter-unit" class="form-select form-select-sm">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1 fw-bold">Filter Kelas:</label>
                    <select id="filter-kelas" class="form-select form-select-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1 fw-bold">Status Siswa:</label>
                    <select id="filter-status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                    </select>
                </div>
                <div class="col-md-3 text-end d-flex align-items-end justify-content-end">
                    <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Remote Server-Side DataTable --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-siswa" class="table table-hover table-striped align-middle w-100">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="text-center" width="5%">No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Siswa</th>
                            <th>Unit Sekolah</th>
                            <th>Kelas & Jurusan</th>
                            <th class="text-center">L/P</th>
                            <th>Wali Murid</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        let table = $('#table-siswa').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('siswas.index', [], false) }}",
                data: function (d) {
                    d.unit_id = $('#filter-unit').val();
                    d.kelas_id = $('#filter-kelas').val();
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
                { data: 'nis', name: 'nis', defaultContent: '-' },
                { data: 'nama', name: 'nama', defaultContent: '-' },
                { data: 'unit_name', name: 'unit_name', defaultContent: '-' },
                { data: 'kelas_name', name: 'kelas_name', defaultContent: '-' },
                { data: 'jenis_kelamin', name: 'jenis_kelamin', className: 'text-center', defaultContent: '-' },
                { data: 'nama_wali', name: 'nama_wali', defaultContent: '-' },
                { data: 'status_badge', name: 'status', className: 'text-center', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
            ],
            language: {
                search: "Pencarian:",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data dari server...',
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data siswa",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ data)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#filter-unit, #filter-kelas, #filter-status').on('change', function() {
            table.draw();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-unit').val('');
            $('#filter-kelas').val('');
            $('#filter-status').val('');
            table.draw();
        });
    });
</script>
@endpush
