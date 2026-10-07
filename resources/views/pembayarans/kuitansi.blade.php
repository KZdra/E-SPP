<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi SPP - {{ $pembayaran->kode_transaksi }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        .receipt-container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            padding: 35px 45px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #dee2e6;
        }
        .school-header {
            border-bottom: 3px double #212529;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .receipt-title {
            text-align: center;
            letter-spacing: 1.5px;
            font-weight: 800;
            margin-top: 10px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        .table-receipt th {
            background-color: #f1f3f5 !important;
            font-size: 0.85rem;
        }
        .table-receipt td, .table-receipt th {
            padding: 8px 12px;
        }
        .total-box {
            background-color: #f8f9fa;
            border: 2px dashed #0d6efd;
            padding: 12px 20px;
            border-radius: 6px;
        }
        .signature-box {
            margin-top: 40px;
        }
        .signature-line {
            width: 180px;
            border-bottom: 1px solid #212529;
            margin: 60px auto 5px auto;
        }
        @media print {
            body {
                background: #ffffff;
                margin: 0;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Print Action Bar --}}
    <div class="container text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold shadow">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2 ms-2">
            Tutup Tab
        </button>
    </div>

    {{-- Official Receipt Document --}}
    <div class="receipt-container">
        {{-- Header / Kop Surat --}}
        <div class="school-header text-center position-relative">
            <h4 class="fw-bold mb-0 text-uppercase tracking-wide">
                {{ $pembayaran->unitSekolah->nama_unit ?? 'LEMBAGA PENDIDIKAN ISLAM' }}
            </h4>
            <p class="mb-0 small text-muted">
                {{ $pembayaran->unitSekolah->alamat ?? 'Jl. Pendidikan Terpadu No. 12, Jakarta' }}
            </p>
            <p class="mb-0 small text-muted">
                Telp: {{ $pembayaran->unitSekolah->telepon ?? '021-7890000' }} | Email: {{ $pembayaran->unitSekolah->email ?? 'info@sekolah.sch.id' }}
            </p>
        </div>

        {{-- Document Title --}}
        <div class="receipt-title">
            <h5 class="fw-bold mb-1 text-primary">BUKTI PEMBAYARAN SPP RESMI</h5>
            <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-1">
                NO: {{ $pembayaran->kode_transaksi }}
            </span>
        </div>

        {{-- Metadata Grid --}}
        <div class="row g-2 mb-3 small">
            <div class="col-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted text-nowrap" style="width: 110px;">NIS Siswa</td>
                        <td class="fw-bold font-monospace">: {{ $pembayaran->siswa->nis }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Siswa</td>
                        <td class="fw-bold text-uppercase">: {{ $pembayaran->siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kelas / Rombel</td>
                        <td>: Kelas {{ $pembayaran->siswa->kelas->nama_kelas ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted text-nowrap" style="width: 120px;">Tanggal Bayar</td>
                        <td class="fw-bold">: {{ $pembayaran->tgl_bayar ? $pembayaran->tgl_bayar->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode Bayar</td>
                        <td class="text-uppercase">: {{ $pembayaran->metode_bayar }} {{ $pembayaran->bank_tujuan ? "({$pembayaran->bank_tujuan})" : '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas TU</td>
                        <td>: {{ $pembayaran->petugas->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Itemized Table --}}
        <table class="table table-bordered table-receipt align-middle mb-3">
            <thead>
                <tr class="text-uppercase text-muted">
                    <th style="width: 45px;" class="text-center">No</th>
                    <th>Keterangan Pembayaran SPP</th>
                    <th>Tahun Ajaran</th>
                    <th class="text-end" style="width: 170px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembayaran->details as $idx => $detail)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>SPP Bulan {{ $detail->tagihan->nama_bulan ?? '-' }} {{ $detail->tagihan->tahun ?? '-' }}</strong>
                            <small class="d-block text-success"><i class="bi bi-check-circle"></i> LUNAS</small>
                        </td>
                        <td>{{ $detail->tagihan->tahunAjaran->tahun ?? '-' }} ({{ $detail->tagihan->tahunAjaran->semester ?? '-' }})</td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($detail->nominal_dibayar, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end fw-bold">TOTAL PEMBAYARAN</th>
                    <th class="text-end fw-bold fs-6 text-primary">
                        Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        </table>

        {{-- Notes & Total Banner --}}
        <div class="row align-items-center mb-4">
            <div class="col-12">
                <div class="total-box">
                    <div class="small text-muted text-uppercase fw-semibold">Status Transaksi:</div>
                    <div class="fw-bold text-success">
                        <i class="bi bi-check-circle-fill me-1"></i> LUNAS - Sah & Diterima Bendahara Sekolah
                    </div>
                    @if($pembayaran->catatan)
                        <div class="small text-muted mt-1"><em>Catatan: {{ $pembayaran->catatan }}</em></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Signature Section --}}
        <div class="row text-center signature-box small">
            <div class="col-4">
                <p class="mb-0">Siswa / Wali Murid,</p>
                <div class="signature-line"></div>
                <span>( {{ $pembayaran->siswa->nama_wali ?? $pembayaran->siswa->nama }} )</span>
            </div>
            <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                {{-- Verification Stamp / QR Badge --}}
                <div class="border rounded p-2 text-muted" style="font-size: 0.7rem; width: 110px;">
                    <i class="bi bi-qr-code fs-2 d-block text-dark"></i>
                    <span>VERIFIKASI SISTEM</span>
                </div>
            </div>
            <div class="col-4">
                <p class="mb-0">Bendahara / Petugas TU,</p>
                <div class="signature-line"></div>
                <span class="fw-bold">( {{ $pembayaran->petugas->name ?? 'Bendahara' }} )</span>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
            Bukti pembayaran ini dicetak secara otomatis melalui Sistem Informasi E-SPP pada {{ date('d/m/Y H:i:s') }}. Simpan kuitansi ini sebagai bukti pembayaran yang sah.
        </div>
    </div>

</body>
</html>
