<?php

namespace App\Http\Requests\Alat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlatRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        // Mengambil ID alat yang sedang di-update dari parameter rute URL
        $alatId = $this->route('alat');
        return [
            'kategori_id' => ['required','integer', Rule::exists('kategori', 'id')],
            'nama_alat' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'status_kondisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg','max:2048'],

        ];
    }
}
