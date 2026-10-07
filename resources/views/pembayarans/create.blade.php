@extends('layouts.app')

@section('subtitle', 'Kasir SPP')
@section('content_header_title', 'Kasir Pembayaran SPP Siswa')
@section('content_header_subtitle', 'Transaksi Penerimaan Kas')

@section('content_body')
    <form action="{{ route('pembayarans.store') }}" method="POST" id="formKasir">
        @csrf
        <div class="row g-3">
            {{-- Left Column: Student Selection & Bill Checklist --}}
            <div class="col-lg-8">
                {{-- 1. Student Selector --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent pt-3 pb-2">
                        <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-person-bounding-box text-primary me-2"></i>1. Pilih Siswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pilih Siswa (Ketik Nama / NIS) <span class="text-danger">*</span></label>
                                <select name="siswa_id" id="siswaSelect" class="form-select" required onchange="loadStudentBills(this.value)">
                                    <option value="">-- Ketik atau Pilih Siswa --</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" {{ (old('siswa_id', $selectedSiswa?->id) == $s->id) ? 'selected' : '' }}>
                                            {{ $s->nama }} (NIS: {{ $s->nis }}) - Kelas {{ $s->kelas->nama_kelas ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Student Info Banner (Dynamic) --}}
                        <div id="studentInfoBox" class="alert alert-light border shadow-sm mt-3 {{ $selectedSiswa ? '' : 'd-none' }}">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1 text-primary" id="infoNama">{{ $selectedSiswa?->nama }}</h5>
                                    <div class="small text-muted">
                                        NIS: <span class="fw-bold font-monospace" id="infoNis">{{ $selectedSiswa?->nis }}</span> | 
                                        Kelas: <span class="badge bg-secondary" id="infoKelas">{{ $selectedSiswa?->kelas->nama_kelas ?? '-' }}</span> | 
                                        Unit: <span class="badge bg-primary" id="infoUnit">{{ $selectedSiswa?->unitSekolah->nama_unit ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border">Siswa Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Bill Checklist --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-body-secondary">
                            <i class="bi bi-card-checklist text-primary me-2"></i>2. Pilih Tagihan SPP yang Dibayar
                        </h5>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)">
                            <label class="form-check-label fw-semibold small" for="selectAllCheckbox">Pilih Semua Bulan</label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- Loading Indicator --}}
                        <div id="billsLoading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted small mt-2 mb-0">Memuat tagihan siswa...</p>
                        </div>

                        {{-- Empty State --}}
                        <div id="billsEmpty" class="text-center py-5 text-muted {{ ($selectedSiswa && $unpaidBills->count() > 0) ? 'd-none' : '' }}">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                            <span id="billsEmptyText">
                                {{ $selectedSiswa ? 'Siswa ini tidak memiliki tunggakan tagihan SPP (Semua tagihan LUNAS).' : 'Silakan pilih siswa terlebih dahulu untuk memuat tagihan SPP.' }}
                            </span>
                        </div>

                        {{-- Table of Unpaid Bills --}}
                        <div class="table-responsive {{ ($selectedSiswa && $unpaidBills->count() > 0) ? '' : 'd-none' }}" id="billsTableWrapper">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th style="width: 40px;" class="text-center">Pilih</th>
                                        <th>Periode Bulan SPP</th>
                                        <th>Tahun Ajaran</th>
                                        <th class="text-end">Nominal Tagihan</th>
                                        <th class="text-end">Sisa Bayar</th>
                                        <th>Jatuh Tempo</th>
                                    </tr>
                                </thead>
                                <tbody id="billsTableBody">
                                    @if($selectedSiswa)
                                        @foreach($unpaidBills as $b)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="tagihan_ids[]" value="{{ $b->id }}" data-nominal="{{ $b->sisa_bayar }}" class="form-check-input bill-checkbox" onchange="recalculateTotal()">
                                                </td>
                                                <td class="fw-bold">{{ $b->nama_bulan }} {{ $b->tahun }}</td>
                                                <td>{{ $b->tahunAjaran->tahun ?? '-' }}</td>
                                                <td class="text-end">Rp {{ number_format($b->nominal, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-danger">Rp {{ number_format($b->sisa_bayar, 0, ',', '.') }}</td>
                                                <td><small class="text-muted">{{ $b->jatuh_tempo ? $b->jatuh_tempo->format('d/m/Y') : '-' }}</small></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Payment Details & Summary --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-header bg-transparent pt-3 pb-2">
                        <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-wallet2 text-success me-2"></i>3. Rincian Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        {{-- Running Total Display --}}
                        <div class="p-3 rounded-3 bg-primary text-white text-center mb-3 shadow-sm">
                            <span class="small text-uppercase opacity-75">Total Pembayaran SPP</span>
                            <h2 class="fw-bold mb-0 mt-1" id="displayTotalBayar">Rp 0</h2>
                            <small class="opacity-75" id="displayBulanCount">0 Bulan dipilih</small>
                        </div>

                        {{-- Payment Form Fields --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" id="metodeTunai" value="tunai" checked onchange="toggleMetode(this.value)">
                                    <label class="form-check-label fw-bold" for="metodeTunai">
                                        <i class="bi bi-cash me-1 text-success"></i> Tunai (Cash)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" id="metodeTransfer" value="transfer" onchange="toggleMetode(this.value)">
                                    <label class="form-check-label fw-bold" for="metodeTransfer">
                                        <i class="bi bi-bank me-1 text-primary"></i> Transfer Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Transfer Fields (Hidden by default) --}}
                        <div id="transferFields" class="d-none">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Bank Tujuan Transfer</label>
                                <select name="bank_tujuan" class="form-select">
                                    <option value="">-- Pilih Rekening Sekolah --</option>
                                    <option value="Bank Syariah Indonesia (BSI)">BSI (Bank Syariah Indonesia)</option>
                                    <option value="BCA">Bank BCA</option>
                                    <option value="Bank Mandiri">Bank Mandiri</option>
                                    <option value="BRI">Bank BRI</option>
                                    <option value="Lainnya">Bank Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nomor Referensi / Resi Transfer</label>
                                <input type="text" name="nomor_referensi" class="form-control" placeholder="Nomor resi bukti transfer">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Transaksi</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional kasir..."></textarea>
                        </div>

                        <div class="d-grid gap-2 pt-2">
                            <button type="submit" id="btnSubmitBayar" class="btn btn-success btn-lg fw-bold shadow-sm" disabled>
                                <i class="bi bi-check2-circle me-1"></i> Proses Pembayaran
                            </button>
                            <a href="{{ route('pembayarans.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
<script>
function toggleMetode(metode) {
    const transferFields = document.getElementById('transferFields');
    if (metode === 'transfer') {
        transferFields.classList.remove('d-none');
    } else {
        transferFields.classList.add('d-none');
    }
}

function toggleSelectAll(checked) {
    const checkboxes = document.querySelectorAll('.bill-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
    recalculateTotal();
}

function recalculateTotal() {
    const checkboxes = document.querySelectorAll('.bill-checkbox:checked');
    let total = 0;
    checkboxes.forEach(cb => {
        total += parseFloat(cb.getAttribute('data-nominal') || 0);
    });

    document.getElementById('displayTotalBayar').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('displayBulanCount').textContent = checkboxes.length + ' Bulan dipilih';

    const btnSubmit = document.getElementById('btnSubmitBayar');
    if (checkboxes.length > 0 && total > 0) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Bayar Rp ${total.toLocaleString('id-ID')} Sekarang`;
    } else {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Proses Pembayaran`;
    }
}

function loadStudentBills(siswaId) {
    if (!siswaId) {
        document.getElementById('studentInfoBox').classList.add('d-none');
        document.getElementById('billsTableWrapper').classList.add('d-none');
        document.getElementById('billsEmpty').classList.remove('d-none');
        document.getElementById('billsEmptyText').textContent = 'Silakan pilih siswa terlebih dahulu untuk memuat tagihan SPP.';
        recalculateTotal();
        return;
    }

    const loading = document.getElementById('billsLoading');
    const empty = document.getElementById('billsEmpty');
    const tableWrapper = document.getElementById('billsTableWrapper');
    const tbody = document.getElementById('billsTableBody');

    loading.classList.remove('d-none');
    empty.classList.add('d-none');
    tableWrapper.classList.add('d-none');

    fetch(`{{ route('pembayarans.getUnpaidBills') }}?siswa_id=${siswaId}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('d-none');
            if (data.success) {
                // Populate student info
                document.getElementById('infoNama').textContent = data.siswa.nama;
                document.getElementById('infoNis').textContent = data.siswa.nis;
                document.getElementById('infoKelas').textContent = 'Kelas ' + data.siswa.kelas;
                document.getElementById('infoUnit').textContent = data.siswa.unit;
                document.getElementById('studentInfoBox').classList.remove('d-none');

                if (data.bills.length > 0) {
                    tbody.innerHTML = '';
                    data.bills.forEach(bill => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center">
                                <input type="checkbox" name="tagihan_ids[]" value="${bill.id}" data-nominal="${bill.sisa_bayar}" class="form-check-input bill-checkbox" onchange="recalculateTotal()">
                            </td>
                            <td class="fw-bold">${bill.periode}</td>
                            <td>${bill.tahun_ajaran}</td>
                            <td class="text-end">Rp ${bill.nominal.toLocaleString('id-ID')}</td>
                            <td class="text-end fw-bold text-danger">Rp ${bill.sisa_bayar.toLocaleString('id-ID')}</td>
                            <td><small class="text-muted">${bill.jatuh_tempo}</small></td>
                        `;
                        tbody.appendChild(tr);
                    });
                    tableWrapper.classList.remove('d-none');
                } else {
                    empty.classList.remove('d-none');
                    document.getElementById('billsEmptyText').textContent = 'Siswa ini tidak memiliki tunggakan tagihan SPP (Semua tagihan LUNAS).';
                }
            }
            recalculateTotal();
        })
        .catch(err => {
            loading.classList.add('d-none');
            alert('Gagal memuat tagihan: ' + err.message);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    recalculateTotal();
});
</script>
@endpush
