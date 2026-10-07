@extends('layouts.app')

@section('subtitle', 'Kenaikan Kelas & Kelulusan')
@section('content_header_title', 'Promosi & Kenaikan Kelas Masal')
@section('content_header_subtitle', 'Manajemen Rombongan Belajar & Kelulusan Siswa')

@section('content_body')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Card 1: Pilih Kelas Asal --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-funnel-fill me-1"></i> 1. Tentukan Kelas Asal
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('kenaikan-kelas.index') }}">
                        @if(auth()->user()->isYayasan())
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Unit Sekolah</label>
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Kelas yang Akan Dipromosikan</label>
                            <select name="kelas_asal_id" class="form-select" onchange="this.form.submit()" required>
                                <option value="">-- Pilih Kelas Asal --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                        Kelas {{ $k->nama_kelas }} ({{ $k->jurusan->kode_jurusan ?? 'Umum' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-info border-0 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Pilih kelas asal untuk memuat seluruh siswa aktif yang terdaftar di kelas tersebut.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card 2: Daftar Siswa & Eksekusi Aksi --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-people-fill me-1"></i> 2. Daftar Siswa & Aksi Promosi
                    </h6>
                    @if($students->isNotEmpty())
                        <span class="badge bg-primary fs-6">{{ $students->count() }} Siswa Aktif</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(!$selectedKelasId)
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-arrow-left-circle display-4 text-secondary mb-2 d-block"></i>
                            Silakan pilih kelas asal pada panel sebelah kiri terlebih dahulu.
                        </div>
                    @elseif($students->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-exclamation-circle display-4 text-warning mb-2 d-block"></i>
                            Tidak ada siswa dengan status aktif di kelas ini.
                        </div>
                    @else
                        <form action="{{ route('kenaikan-kelas.process') }}" method="POST" id="form-promosi">
                            @csrf
                            <input type="hidden" name="unit_sekolah_id" value="{{ $selectedUnitId }}">
                            <input type="hidden" name="kelas_asal_id" value="{{ $selectedKelasId }}">

                            {{-- Aksi Promosi --}}
                            <div class="p-3 bg-light rounded border mb-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Jenis Aksi Promosi <span class="text-danger">*</span></label>
                                        <select name="aksi" id="select-aksi" class="form-select fw-semibold" required>
                                            <option value="naik_kelas">Naik ke Kelas Berikutnya</option>
                                            <option value="lulus">Luluskan Siswa (Tingkat Akhir)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7" id="wrapper-kelas-tujuan">
                                        <label class="form-label fw-bold">Kelas Tujuan <span class="text-danger">*</span></label>
                                        <select name="kelas_tujuan_id" id="select-kelas-tujuan" class="form-select">
                                            <option value="">-- Pilih Kelas Tujuan --</option>
                                            @foreach($kelasList as $kt)
                                                @if($kt->id != $selectedKelasId)
                                                    <option value="{{ $kt->id }}">Kelas {{ $kt->nama_kelas }} ({{ $kt->jurusan->kode_jurusan ?? 'Umum' }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Siswa --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check-all" checked>
                                    <label class="form-check-label fw-semibold" for="check-all">
                                        Centang / Batal Semua
                                    </label>
                                </div>
                                <span class="small text-muted" id="selected-count">{{ $students->count() }} siswa dipilih</span>
                            </div>

                            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr class="small text-muted">
                                            <th class="text-center" width="5%">Pilih</th>
                                            <th width="20%">NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-center" width="10%">L/P</th>
                                            <th class="text-center" width="15%">Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $siswa)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="form-check-input siswa-checkbox" checked>
                                                </td>
                                                <td class="font-monospace fw-bold">{{ $siswa->nis }}</td>
                                                <td>{{ $siswa->nama }}</td>
                                                <td class="text-center">{{ $siswa->jenis_kelamin }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-muted border">{{ ucfirst($siswa->kategori_spp) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold" onclick="return confirm('Apakah Anda yakin ingin memproses kenaikan/kelulusan untuk siswa yang dipilih?');">
                                    <i class="bi bi-check-circle-fill me-1"></i> Proses Kenaikan / Kelulusan
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#select-aksi').on('change', function() {
            if ($(this).val() === 'lulus') {
                $('#wrapper-kelas-tujuan').hide();
                $('#select-kelas-tujuan').prop('required', false);
            } else {
                $('#wrapper-kelas-tujuan').show();
                $('#select-kelas-tujuan').prop('required', true);
            }
        });

        $('#check-all').on('change', function() {
            $('.siswa-checkbox').prop('checked', $(this).prop('checked'));
            updateCount();
        });

        $('.siswa-checkbox').on('change', function() {
            updateCount();
        });

        function updateCount() {
            let total = $('.siswa-checkbox:checked').length;
            $('#selected-count').text(total + ' siswa dipilih');
        }
    });
</script>
@endpush
