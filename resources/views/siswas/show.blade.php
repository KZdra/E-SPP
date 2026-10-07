@extends('layouts.app')

@section('subtitle', 'Detail Siswa')
@section('content_header_title', 'Buku Kas & Profil Siswa')
@section('content_header_subtitle', $siswa->nama)

@section('content_header_actions')
    <div class="d-flex gap-2">
        @can('pembayaran.create')
            <a href="{{ route('pembayarans.create', ['siswa_id' => $siswa->id]) }}" class="btn btn-success shadow-sm">
                <i class="bi bi-credit-card-2-front-fill me-1"></i> Buka Kasir Pembayaran Siswa
            </a>
        @endcan
        <a href="{{ route('siswas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
@stop

@section('content_body')
    {{-- Top Overview Card --}}
    <div class="row g-3 mb-4">
        {{-- Profile Card --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $siswa->nama }}</h5>
                    <p class="text-muted small mb-2 font-monospace">NIS: {{ $siswa->nis }} | NISN: {{ $siswa->nisn ?? '-' }}</p>
                    <div class="d-flex justify-content-center gap-1 mb-3">
                        <span class="badge bg-primary">{{ $siswa->unitSekolah->nama_unit ?? '-' }}</span>
                        <span class="badge bg-secondary">Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                        <span class="badge {{ $siswa->status === 'aktif' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($siswa->status) }}</span>
                    </div>
                    <hr class="my-3">
                    <ul class="list-unstyled text-start small mb-0">
                        <li class="mb-2"><strong><i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin:</strong> {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</li>
                        <li class="mb-2"><strong><i class="bi bi-person-heart me-2"></i>Orang Tua / Wali:</strong> {{ $siswa->nama_wali ?? '-' }}</li>
                        <li class="mb-2"><strong><i class="bi bi-whatsapp me-2"></i>Kontak Wali:</strong> {{ $siswa->telepon_wali ?? '-' }}</li>
                        <li><strong><i class="bi bi-geo-alt me-2"></i>Alamat:</strong> {{ $siswa->alamat ?? '-' }}</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Financial Summary KPIs --}}
        <div class="col-lg-8">
            <div class="row g-3 h-100">
                @php
                    $totalTagihan = $siswa->tagihans->sum('nominal');
                    $totalTerbayar = $siswa->tagihans->sum('nominal_terbayar');
                    $totalTunggakan = $totalTagihan - $totalTerbayar;
                    $lunasCount = $siswa->tagihans->where('status', 'lunas')->count();
                    $unpaidCount = $siswa->tagihans->where('status', 'belum_lunas')->count();
                @endphp
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <span class="text-muted small fw-semibold text-uppercase">Total Tagihan SPP</span>
                            <h4 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h4>
                            <small class="text-muted">{{ $siswa->tagihans->count() }} Invoice periode</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <span class="text-muted small fw-semibold text-uppercase">Total Terbayar</span>
                            <h4 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</h4>
                            <small class="text-success"><i class="bi bi-check-circle"></i> {{ $lunasCount }} Bulan lunas</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body">
                            <span class="text-muted small fw-semibold text-uppercase">Sisa Tunggakan</span>
                            <h4 class="fw-bold text-danger mt-1 mb-0">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h4>
                            <small class="text-danger"><i class="bi bi-exclamation-circle"></i> {{ $unpaidCount }} Bulan belum lunas</small>
                        </div>
                    </div>
                </div>

                {{-- Tabs: Bills & Payments --}}
                <div class="col-12 mt-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <ul class="nav nav-pills" id="siswaTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-semibold" id="bills-tab" data-bs-toggle="tab" data-bs-target="#bills-pane" type="button" role="tab">
                                        <i class="bi bi-card-checklist me-1"></i> Daftar Tagihan SPP ({{ $siswa->tagihans->count() }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane" type="button" role="tab">
                                        <i class="bi bi-receipt me-1"></i> Riwayat Pembayaran ({{ $siswa->pembayarans->count() }})
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content" id="siswaTabContent">
                                {{-- Tab 1: Bills --}}
                                <div class="tab-pane fade show active" id="bills-pane" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr class="small text-muted text-uppercase">
                                                    <th>Periode SPP</th>
                                                    <th>Tahun Ajaran</th>
                                                    <th class="text-end">Nominal Tagihan</th>
                                                    <th class="text-end">Terbayar</th>
                                                    <th class="text-end">Sisa Bayar</th>
                                                    <th>Jatuh Tempo</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($siswa->tagihans as $tagihan)
                                                    <tr>
                                                        <td class="fw-bold">{{ $tagihan->nama_bulan }} {{ $tagihan->tahun }}</td>
                                                        <td>{{ $tagihan->tahunAjaran->tahun ?? '-' }} ({{ $tagihan->tahunAjaran->semester ?? '-' }})</td>
                                                        <td class="text-end">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                                                        <td class="text-end text-success">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</td>
                                                        <td class="text-end fw-bold {{ $tagihan->sisa_bayar > 0 ? 'text-danger' : 'text-muted' }}">
                                                            Rp {{ number_format($tagihan->sisa_bayar, 0, ',', '.') }}
                                                        </td>
                                                        <td>{{ $tagihan->jatuh_tempo ? $tagihan->jatuh_tempo->format('d/m/Y') : '-' }}</td>
                                                        <td>
                                                            @if($tagihan->status === 'lunas')
                                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                                            @else
                                                                <span class="badge bg-danger"><i class="bi bi-clock me-1"></i> Belum Lunas</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada tagihan SPP untuk siswa ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Tab 2: Payments --}}
                                <div class="tab-pane fade" id="payments-pane" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr class="small text-muted text-uppercase">
                                                    <th>No. Kuitansi</th>
                                                    <th>Tgl Bayar</th>
                                                    <th>Metode</th>
                                                    <th class="text-end">Total Bayar</th>
                                                    <th>Petugas TU</th>
                                                    <th class="text-center">Cetak</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($siswa->pembayarans as $pembayaran)
                                                    <tr>
                                                        <td class="fw-bold font-monospace text-primary">
                                                            <a href="{{ route('pembayarans.show', $pembayaran->id) }}">{{ $pembayaran->kode_transaksi }}</a>
                                                        </td>
                                                        <td>{{ $pembayaran->tgl_bayar ? $pembayaran->tgl_bayar->format('d/m/Y') : '-' }}</td>
                                                        <td><span class="badge bg-secondary text-uppercase">{{ $pembayaran->metode_bayar }}</span></td>
                                                        <td class="text-end fw-bold text-success">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</td>
                                                        <td>{{ $pembayaran->petugas->name ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <a href="{{ route('pembayarans.kuitansi', $pembayaran->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-printer"></i> Cetak Kuitansi
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pembayaran yang tercatat.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
