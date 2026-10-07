<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('siswa.edit');
    }

    public function rules(): array
    {
        return [
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|max:30',
            'nisn' => 'nullable|string|max:30',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_wali' => 'nullable|string|max:150',
            'telepon_wali' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,lulus,pindah',
            'kategori_spp' => 'nullable|in:reguler,beasiswa,keringanan,yatim',
            'diskon_tipe' => 'nullable|in:persen,nominal',
            'diskon_nilai' => 'nullable|numeric|min:0',
            'catatan_keringanan' => 'nullable|string|max:255',
        ];
    }
}
