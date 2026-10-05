<?php

namespace App\Observers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class AlatObserver
{
    
    public function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
           
    }

    public function created(Alat $alat): void
    {
        $this->catatLog("Menambahkan master data alat baru: {$alat->nama_alat} (ID: {$alat->id})");
    }

    public function updated(Alat $alat): void
    {
        $perubahanArray = array_diff(array_keys($alat->getChanges()), ['updated_at']);
        
        if (!empty($perubahanArray)) {
            $perubahan = implode(', ', $perubahanArray);
            $this->catatLog("Memperbarui data alat '{$alat->nama_alat}' (Kolom yang diubah: {$perubahan})");
        }
    }

    public function deleted(Alat $alat): void
    {
        $this->catatLog("Menghapus master data alat: {$alat->nama_alat} (ID: {$alat->id})");
    }

}
