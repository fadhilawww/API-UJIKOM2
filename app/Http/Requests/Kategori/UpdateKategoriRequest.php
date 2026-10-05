<?php

namespace App\Http\Requests\Kategori;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan bernilai true karena otorisasi sudah
        //dihandle oleh Middleware di route
    }

    public function rules(): array
    {
        $kategori = $this->route('kategori');
        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                // Mengabaikan pengecekan unik untuk ID kategori yang sedang di-update
                Rule::unique('kategori', 'nama_kategori')->ignore($kategori),
            ]
        ];
    }
}
