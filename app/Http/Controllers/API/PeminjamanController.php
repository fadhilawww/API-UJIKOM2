<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjaman\StorePeminjamanRequest;
use App\Http\Resources\PeminjamanResource;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Exception;

class PeminjamanController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);
        if ($user->role === 'peminjam') {
            $query->where('user_id', $user->id);
        }
        $peminjaman = $query->latest()->get();
        
        return response()->json([
            'message' => 'Daftar peminjaman berhasil diambil.',
            'data' => PeminjamanResource::collection($peminjaman)
        ]);

    }


    public function store(StorePeminjamanRequest $request): JsonResponse
    {
        try {
            $peminjaman = DB::transaction(function () use ($request) {
                $user = auth()->user();
                $peminjaman = Peminjaman::create([
                    'user_id' => $user->id,
                    'tgl_pinjam' => now()->toDateString(),
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                    'status' => 'diajukan',
                ]);

                foreach ($request->items as $item) {
                    // lockForUpdate mengunci baris data di database sampai transaksi ini COMMIT
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);
                    if ($alat->stok < $item['jumlah']) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}");
                    }
                    DetailPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['alat_id'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
                return $peminjaman->load(['user', 'detailPinjam.alat']);
            });
            return response()->json([
                'message' => 'Peminjaman berhasil diajukan. Menunggu persetujuan petugas.',
                'data' => new PeminjamanResource($peminjaman) //Dioptimalkan menggunakan Resource
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    

    
    public function show(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'message' => 'Detail peminjaman berhasil diambil.',
            'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat', 'pengembalian']))
        ]);

    }

    public function update(StorePeminjamanRequest $request, Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        
        if ($peminjaman->status !== 'diajukan') {
            return response()->json([
                'message' => "Peminjaman tidak dapat diubah karena status saat ini: {$peminjaman->status}."
            ], 400);
        }
        
        try {
            DB::transaction(function () use ($request, $peminjaman) {
                $peminjaman->update([
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                ]);
                
                $peminjaman->detailPinjam()->delete();

                foreach ($request->items as $item) {
                    // Ditambahkan lockForUpdate agar konsisten aman dari race condition saat update data draft
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);
                    
                    if ($alat->stok < $item['jumlah']) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                    }
                    
                    DetailPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['alat_id'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
            });
            
            return response()->json([
                'message' => 'Data permohonan peminjaman berhasil diperbarui.',
                'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();
        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($peminjaman->status !== 'diajukan') {
            return response()->json(['message' => 'Peminjaman tidak dapat dibatalkan.'], 400);
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjam()->delete(); // Hapus child record terlebih dahulu
            $peminjaman->delete();
        });
        
        return response()->json([
            'message' => 'Permohonan peminjaman berhasil dibatalkan dan dihapus.'
        ]);
           
    }

    public function approve(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->status !== 'diajukan') {
            return response()->json([
                'message' => "Persutujuan gagal. Status saat ini: {$peminjaman->status}."
            ], 400);
        }
        
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman->update(['status' => 'dipinjam']);
                foreach ($peminjaman->detailPinjam as $detail) {
                    // Mengunci baris alat demi validasi final sebelum stok dikurangi
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    
                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception("Persetujuan gagal. Stok alat '{$alat->nama_alat}' mendadak tidak mencukupi.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            });

            return response()->json([
                'message' => 'Peminjaman disetujui. Stok alat telah otomatis dikurangi.',
                'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function riwayat(): JsonResponse
    {
        $riwayat = Peminjaman::with(['detailPinjam.alat', 'pengembalian'])->where('user_id', auth()->id())->latest()->get();
        
        return response()->json([
            'message' => 'Riwayat peminjaman Anda.',
            'data' => PeminjamanResource::collection($riwayat)
        ]);
    }
}
