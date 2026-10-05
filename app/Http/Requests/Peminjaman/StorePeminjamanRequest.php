<?php

namespace App\Http\Requests\Peminjaman;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeminjamanRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_kembali_plan' => ['required', 'date', 'date_format:Y-md', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'integer',
            Rule::exists('alat', 'id')],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_kembali_plan.required' => 'Tanggal rencana pengembalian wajib diisi.',
            'tgl_kembali_plan.date_format' => 'Format tanggal harus TahunBulan-Tanggal (YYYY-MM-DD).',
            'tgl_kembali_plan.after_or_equal' => 'Tanggal rencana kembali tidak boleh di masa lalu.',
            'items.required' => 'Anda harus memilih minimal satu alat untuk dipinjam.',
            'items.array' => 'Format data item yang dikirim harus berupa daftar/list.',
            'items.min' => 'Anda harus memilih minimal satu alat untuk dipinjam.',
        ];
    }
    public function attributes(): array
    {
        return [
            'items.*.alat_id' => 'Alat',
            'items.*.jumlah' => 'Jumlah barang',
        ];
    }
}
