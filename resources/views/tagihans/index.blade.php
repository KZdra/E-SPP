@extends('layouts.app')

@section('subtitle', 'Data Tagihan')
@section('content_header_title', 'Data Tagihan SPP Siswa')
@section('content_header_subtitle', 'Operasional & Billing (Remote Server-Side DataTables & Excel Export)')

@section('content_header_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('tagihans.excel', request()->all()) }}" id="btn-export-excel" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (.xlsx)
        </a>
        @can('tagihan.generate')
            <a href="{{ route('tagihans.generate') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-calendar2-plus me-1"></i> Generate Tagihan Massal
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

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                @if(auth()->user()->isYayasan())
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1 fw-bold">Unit Sekolah:</label>
                        <select id="filter-unit" class="form-select form-select-sm">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->kode_unit }} - {{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Bulan:</label>
                    <select id="filter-bulan" class="form-select form-select-sm">
                        <option value="">Semua Bulan</option>
                        @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Tahun:</label>
                    <select id="filter-tahun" class="form-select form-select-sm">
                        <option value="">Semua Tahun</option>
                        @foreach([2025, 2026, 2027] as $thn)
                            <option value="{{ $thn }}">{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Status Tagihan:</label>
                    <select id="filter-status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-bold">Kelas:</label>
                    <select id="filter-kelas" class="form-select form-select-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-end d-flex align-items-end justify-content-end">
                    <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Remote Server-Side DataTable --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-tagihan" class="table table-hover table-striped align-middle w-100">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="text-center" width="5%">No</th>
                            <th>Periode SPP</th>
                            <th>Informasi Siswa</th>
                            <th>Unit Sekolah</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="8%">Aksi</th>
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
        let table = $('#table-tagihan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('tagihans.index', [], false) }}",
                data: function (d) {
                    d.unit_id = $('#filter-unit').val();
                    d.bulan = $('#filter-bulan').val();
                    d.tahun = $('#filter-tahun').val();
                    d.status = $('#filter-status').val();
                    d.kelas_id = $('#filter-kelas').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
                { data: 'periode', name: 'periode', defaultContent: '-' },
                { data: 'siswa_info', name: 'siswa_info', defaultContent: '-' },
                { data: 'unit_name', name: 'unit_name', defaultContent: '-' },
                { data: 'nominal_format', name: 'nominal', className: 'text-end', defaultContent: '-' },
                { data: 'terbayar_format', name: 'nominal_terbayar', className: 'text-end text-success', defaultContent: '-' },
                { data: 'sisa_format', name: 'nominal', className: 'text-end', defaultContent: '-' },
                { data: 'status_badge', name: 'status', className: 'text-center', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', defaultContent: '-' },
            ],
            language: {
                search: "Pencarian:",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data tagihan...',
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan",
                infoEmpty: "Menampilkan 0 tagihan",
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
            if ($('#filter-bulan').val()) params.append('bulan', $('#filter-bulan').val());
            if ($('#filter-tahun').val()) params.append('tahun', $('#filter-tahun').val());
            if ($('#filter-status').val()) params.append('status', $('#filter-status').val());
            if ($('#filter-kelas').val()) params.append('kelas_id', $('#filter-kelas').val());

            let base = "{{ route('tagihans.excel') }}";
            let fullUrl = params.toString() ? base + '?' + params.toString() : base;
            $('#btn-export-excel').attr('href', fullUrl);
        }

        $('#filter-unit, #filter-bulan, #filter-tahun, #filter-status, #filter-kelas').on('change', function() {
            table.draw();
            updateExportLink();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-unit').val('');
            $('#filter-bulan').val('');
            $('#filter-tahun').val('');
            $('#filter-status').val('');
            $('#filter-kelas').val('');
            table.draw();
            updateExportLink();
        });
    });
</script>
@endpush
