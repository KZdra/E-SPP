@extends('layouts.app')

@section('subtitle', 'Edit Siswa')
@section('content_header_title', 'Perbarui Data Siswa')
@section('content_header_subtitle', $siswa->nama)

@section('content_body')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Siswa: {{ $siswa->nama }}</h5>
                </div>
                <form action="{{ route('siswas.update', $siswa->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit Sekolah <span class="text-danger">*</span></label>
                                <select name="unit_sekolah_id" class="form-select @error('unit_sekolah_id') is-invalid @enderror" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_sekolah_id', $siswa->unit_sekolah_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }} ({{ $u->kode_unit }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_sekolah_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rombel / Kelas <span class="text-danger">*</span></label>
                                <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                                            Kelas {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Induk Siswa (NIS) <span class="text-danger">*</span></label>
                                <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $siswa->nis) }}" required>
                                @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NISN (Nomor Induk Siswa Nasional)</label>
                                <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $siswa->nisn) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $siswa->nama) }}" required>
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Orang Tua / Wali</label>
                                <input type="text" name="nama_wali" class="form-control" value="{{ old('nama_wali', $siswa->nama_wali) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor WhatsApp / Telepon Wali</label>
                                <input type="text" name="telepon_wali" class="form-control" value="{{ old('telepon_wali', $siswa->telepon_wali) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Alamat Tempat Tinggal</label>
                                <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $siswa->alamat) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status Siswa <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif" {{ old('status', $siswa->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="lulus" {{ old('status', $siswa->status) == 'lulus' ? 'selected' : '' }}>Lulus</option>
                                    <option value="pindah" {{ old('status', $siswa->status) == 'pindah' ? 'selected' : '' }}>Pindah</option>
                                </select>
                            </div>

                            {{-- Pengaturan SPP & Keringanan / Beasiswa --}}
                            <div class="col-12 mt-4">
                                <div class="p-3 rounded bg-light border">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-award-fill me-1"></i> Kebijakan Beasiswa & Keringanan Biaya SPP
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Kategori SPP</label>
                                            <select name="kategori_spp" id="kategori_spp" class="form-select">
                                                <option value="reguler" {{ old('kategori_spp', $siswa->kategori_spp) == 'reguler' ? 'selected' : '' }}>Reguler (Bayar Penuh 100%)</option>
                                                <option value="beasiswa" {{ old('kategori_spp', $siswa->kategori_spp) == 'beasiswa' ? 'selected' : '' }}>Beasiswa Prestasi / Tahfidz</option>
                                                <option value="keringanan" {{ old('kategori_spp', $siswa->kategori_spp) == 'keringanan' ? 'selected' : '' }}>Keringanan Keluarga Tidak Mampu</option>
                                                <option value="yatim" {{ old('kategori_spp', $siswa->kategori_spp) == 'yatim' ? 'selected' : '' }}>Yatim Piatu (Gratis 100%)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Tipe Potongan / Diskon</label>
                                            <select name="diskon_tipe" id="diskon_tipe" class="form-select">
                                                <option value="persen" {{ old('diskon_tipe', $siswa->diskon_tipe) == 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                                                <option value="nominal" {{ old('diskon_tipe', $siswa->diskon_tipe) == 'nominal' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Nilai Potongan</label>
                                            <input type="number" step="any" name="diskon_nilai" id="diskon_nilai" class="form-control" value="{{ old('diskon_nilai', $siswa->diskon_nilai) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Catatan Keterangan / SK Keringanan</label>
                                            <input type="text" name="catatan_keringanan" class="form-control" value="{{ old('catatan_keringanan', $siswa->catatan_keringanan) }}" placeholder="Nomor SK beasiswa atau rekomendasi kepala sekolah">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                        <a href="{{ route('siswas.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $('#kategori_spp').on('change', function() {
        if ($(this).val() === 'yatim') {
            $('#diskon_tipe').val('persen').prop('disabled', true);
            $('#diskon_nilai').val(100).prop('readonly', true);
        } else if ($(this).val() === 'reguler') {
            $('#diskon_tipe').prop('disabled', false);
            $('#diskon_nilai').val(0).prop('readonly', false);
        } else {
            $('#diskon_tipe').prop('disabled', false);
            $('#diskon_nilai').prop('readonly', false);
        }
    });
</script>
@endpush
