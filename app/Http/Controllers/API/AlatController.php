<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Alat\StoreAlatRequest;
use App\Http\Requests\Alat\UpdateAlatRequest;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlatController extends Controller
{
    public function index(): JsonResponse
    {
        // Tetap menggunakan eager loading untuk mencegah N+1 Query
        $alat = Alat::with('kategori')->latest()->get();
        return response()->json([
            'message' => 'Daftar alat berhasil diambil.',
            'data' => AlatResource::collection($alat)
        ]);
    }

    public function store(StoreAlatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $alat = DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('alat','public');
            }
            
            return Alat::create($data);
        });
        return response()->json([
            'message' => 'Alat berhasil ditambahkan.',
            'data' => new AlatResource($alat->load('kategori'))
        ], 201);
    }
    public function show(Alat $alat): JsonResponse
    {
        // Menggunakan Route Model Binding ($alat) ikombinasikan dengan load()
        return response()->json([
            'data' => new AlatResource($alat->load('kategori'))
        ]);
    }
    public function update(UpdateAlatRequest $request, Alat $alat): JsonResponse
    {
        $data = $request->validated();
        $oldGambar = $alat->gambar;//Simpan path gambar lama lebih dahulu
        
        DB::transaction(function () use ($request, &$data, $alat,$oldGambar) {
            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('alat','public');
                
                // Hapus gambar lama dari server HANYA setelah gambar baru sukses masuk database
                if ($oldGambar) {
                    Storage::disk('public')->delete($oldGambar);
                }
            }
            $alat->update($data);
        });
        return response()->json([
            'message' => 'Alat berhasil diperbarui.',
            'data' => new AlatResource($alat->load('kategori'))
        ]);
    }
    public function destroy(Alat $alat): JsonResponse
    {
        DB::transaction(function () use ($alat) {
            if ($alat->gambar) {
                Storage::disk('public')->delete($alat->gambar);
            }
            $alat->delete();
        });
        return response()->json([
            'message' => 'Alat berhasil dihapus.'
        ]);
    }
    public function katalog(): JsonResponse
    {
        $alat = Alat::with('kategori')->tersedia()->latest()->get();
        return response()->json([
            'message' => 'Katalog alat tersedia.',
            'data' => AlatResource::collection($alat)
        ]);
    }
}