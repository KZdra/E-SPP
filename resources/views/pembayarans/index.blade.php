@extends('layouts.app')

@section('subtitle', 'Riwayat Pembayaran')
@section('content_header_title', 'Riwayat Transaksi Pembayaran SPP')
@section('content_header_subtitle', 'Operasional & Kasir (Remote Server-Side DataTables & Excel Export)')

@section('content_header_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('pembayarans.excel', request()->all()) }}" id="btn-export-excel" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (.xlsx)
        </a>
        @can('pembayaran.create')
            <a href="{{ route('pembayarans.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-credit-card-2-front-fill me-1"></i> Buka Kasir Pembayaran
            </a>
        @endcan
    </div>
@stop

@section('content_body')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                        <label class="form-label small text-muted mb-1 fw-bold">Unit Sekolah:</label>
                        <select id="filter-unit" class="form-select form-select-sm">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Dari Tanggal:</label>
                    <input type="date" id="filter-start-date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Sampai Tanggal:</label>
                    <input type="date" id="filter-end-date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Metode Bayar:</label>
                    <select id="filter-metode" class="form-select form-select-sm">
                        <option value="">Semua Metode</option>
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
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
                <table id="table-pembayaran" class="table table-hover table-striped align-middle w-100">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="text-center" width="5%">No</th>
                            <th>No. Kuitansi</th>
                            <th class="text-center">Tgl Bayar</th>
                            <th>Informasi Siswa</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Total Bayar</th>
                            <th>Petugas Kasir</th>
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
        let table = $('#table-pembayaran').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pembayarans.index', [], false) }}",
                data: function (d) {
                    d.unit_id = $('#filter-unit').val();
                    d.start_date = $('#filter-start-date').val();
                    d.end_date = $('#filter-end-date').val();
                    d.metode_bayar = $('#filter-metode').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
                { data: 'kode_transaksi', name: 'kode_transaksi', className: 'font-monospace fw-bold', defaultContent: '-' },
                { data: 'tgl_format', name: 'tgl_bayar', className: 'text-center', defaultContent: '-' },
                { data: 'siswa_info', name: 'siswa_info', defaultContent: '-' },
                { data: 'metode_badge', name: 'metode_bayar', className: 'text-center', defaultContent: '-' },
                { data: 'total_format', name: 'total_bayar', className: 'text-end', defaultContent: '-' },
                { data: 'kasir_name', name: 'kasir_name', defaultContent: '-' },
                { data: 'status_badge', name: 'status', className: 'text-center', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
            ],
            language: {
                search: "Pencarian:",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data pembayaran...',
                lengthMenu: "Tampilkan _MENU_ transaksi",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Menampilkan 0 transaksi",
                infoFiltered: "(disaring dari _MAX_ data)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        function updateExportLink() {
            let params = new URLSearchParams();
            if ($('#filter-unit').val()) params.append('unit_id', $('#filter-unit').val());
            if ($('#filter-start-date').val()) params.append('start_date', $('#filter-start-date').val());
            if ($('#filter-end-date').val()) params.append('end_date', $('#filter-end-date').val());
            if ($('#filter-metode').val()) params.append('metode_bayar', $('#filter-metode').val());

            let base = "{{ route('pembayarans.excel') }}";
            let fullUrl = params.toString() ? base + '?' + params.toString() : base;
            $('#btn-export-excel').attr('href', fullUrl);
        }

        $('#filter-unit, #filter-start-date, #filter-end-date, #filter-metode').on('change', function() {
            table.draw();
            updateExportLink();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-unit').val('');
            $('#filter-start-date').val('');
            $('#filter-end-date').val('');
            $('#filter-metode').val('');
            table.draw();
            updateExportLink();
        });
    });
</script>
@endpush
