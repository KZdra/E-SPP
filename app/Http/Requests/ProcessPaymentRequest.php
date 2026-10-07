<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('pembayaran.create');
    }

    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'tgl_bayar' => 'required|date',
            'metode_bayar' => 'required|in:tunai,transfer',
            'bank_tujuan' => 'nullable|string|max:50',
            'nomor_referensi' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'tagihan_ids' => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihans,id',
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Pilih siswa terlebih dahulu.',
            'tagihan_ids.required' => 'Pilih minimal satu tagihan SPP untuk dibayar.',
            'tagihan_ids.min' => 'Pilih minimal satu tagihan SPP untuk dibayar.',
            'tgl_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'metode_bayar.required' => 'Metode pembayaran (tunai/transfer) wajib dipilih.',
        ];
    }
}
