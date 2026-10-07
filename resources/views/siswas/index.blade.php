@extends('layouts.app')

@section('subtitle', 'Data Siswa')
@section('content_header_title', 'Master Data Siswa')
@section('content_header_subtitle', 'Akademik & Kesiswaan (Remote Server-Side DataTables)')

@section('content_header_actions')
    <div class="d-flex gap-2">
        @can('siswa.create')
            <button type="button" class="btn btn-outline-success shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-import-siswa">
                <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
            </button>
            <a href="{{ route('siswas.create') }}" class="btn btn-primary shadow-sm fw-semibold">
                <i class="bi bi-person-plus me-1"></i> Tambah Siswa Baru
            </a>
        @endcan
    </div>
@stop

@section('content_body')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
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
                            <th class="text-center">Kategori SPP</th>
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

    {{-- Modal Import Excel Siswa --}}
    <div class="modal fade" id="modal-import-siswa" tabindex="-1" aria-labelledby="modalImportSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('siswas.import-excel') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalImportSiswaLabel">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Siswa dari Excel (.xlsx)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 small mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i> 
                            Gunakan template Excel resmi agar format kolom (NIS, Nama, Kelas) terbaca sempurna oleh sistem.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Unit Sekolah <span class="text-danger">*</span></label>
                            <select name="unit_id" id="import-unit-id" class="form-select" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                            <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls" required>
                            <div class="form-text">Maksimal ukuran file: 5 MB</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('siswas.download-template') }}" id="btn-download-template" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Download Template (.xlsx)
                            </a>
                            <span class="small text-muted">Contoh data disertakan</span>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success fw-semibold">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Proses Import
                        </button>
                    </div>
                </form>
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
                { data: 'kategori_badge', name: 'kategori_spp', className: 'text-center', defaultContent: '-' },
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

        // Update download template link based on selected unit
        $('#import-unit-id').on('change', function() {
            let unitId = $(this).val();
            let base = "{{ route('siswas.download-template', [], false) }}";
            $('#btn-download-template').attr('href', base + '?unit_id=' + unitId);
        });
    });
</script>
@endpush
