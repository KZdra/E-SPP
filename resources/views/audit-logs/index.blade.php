@extends('layouts.app')

@section('subtitle', 'Audit Trail Log')
@section('content_header_title', 'Audit Trail Log Aktivitas')
@section('content_header_subtitle', 'Sistem Keamanan & Transparansi (Remote Server-Side DataTables)')

@section('content_body')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter Kategori Log:</label>
                    <select id="filter-log-name" class="form-select form-select-sm">
                        <option value="">Semua Kategori Log</option>
                        @foreach($logTypes as $lt)
                            <option value="{{ $lt }}">{{ ucfirst($lt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Aksi (Event):</label>
                    <select id="filter-event" class="form-select form-select-sm">
                        <option value="">Semua Aksi</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
                <div class="col-md-4 text-end d-flex align-items-end justify-content-end">
                    <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-audit" class="table table-hover table-striped align-middle w-100" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="text-center" width="5%">No</th>
                            <th>Waktu & Tanggal</th>
                            <th>Pengguna (Actor)</th>
                            <th class="text-center">Modul / Log</th>
                            <th class="text-center">Aksi (Event)</th>
                            <th>Deskripsi Aktivitas</th>
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
        let table = $('#table-audit').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('audit-logs.index', [], false) }}",
                data: function (d) {
                    d.log_name = $('#filter-log-name').val();
                    d.event = $('#filter-event').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
                { data: 'tgl_format', name: 'created_at', className: 'text-nowrap', defaultContent: '-' },
                { data: 'user_name', name: 'user_name', defaultContent: '-' },
                { data: 'log_badge', name: 'log_name', className: 'text-center', defaultContent: '-' },
                { data: 'event_badge', name: 'event', className: 'text-center', defaultContent: '-' },
                { data: 'description_text', name: 'description', defaultContent: '-' },
            ],
            language: {
                search: "Pencarian:",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat audit trail...',
                lengthMenu: "Tampilkan _MENU_ log",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ log",
                infoEmpty: "Menampilkan 0 log",
                infoFiltered: "(disaring dari _MAX_ data)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#filter-log-name, #filter-event').on('change', function() {
            table.draw();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-log-name').val('');
            $('#filter-event').val('');
            table.draw();
        });
    });
</script>
@endpush
