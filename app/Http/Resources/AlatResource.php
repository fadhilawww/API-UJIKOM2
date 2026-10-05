<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_alat' => $this->nama_alat,
            'stok' => $this->stok,
            'status_kondisi' => $this->status_kondisi,
            'deskripsi' => $this->deskripsi,
            'gambar' => $this->gambar ? url('storage/' . $this->gambar) : null,

            // Eager load relasi kategori jika tersedia
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
