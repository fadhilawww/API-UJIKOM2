@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Tambah Transaksi Peminjaman')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.peminjaman.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Peminjam (User)</label>
            <select name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                <input type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', date('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Rencana Tanggal Kembali</label>
                <input type="date" name="tgl_kembali_plan" value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Bagian Daftar Alat yang Dipinjam (Dinamis) -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Daftar Alat yang Dipinjam</label>
            <div id="alat-container" class="space-y-2">
                <div class="flex items-center gap-2 alat-row">
                    <select name="alat_id[]" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none">
                        <option value="">-- Pilih Alat --</option>
                        @foreach($alats as $alat)
                            <option value="{{ $alat->id }}">{{ $alat->nama_alat }} (Stok: {{ $alat->stok }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="jumlah[]" value="1" min="1" placeholder="Jumlah"
                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none">
                    <button type="button" onclick="removeRow(this)" class="bg-red-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-600 transition">X</button>
                </div>
            </div>
            <button type="button" onclick="addRow()" class="mt-3 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                + Tambah Alat Lain
            </button>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.peminjaman.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan Peminjaman</button>
        </div>
    </form>
</div>

<!-- Script Sederhana untuk Tambah/Hapus Baris Alat -->
<script>
    function addRow() {
        const container = document.getElementById('alat-container');
        const firstRow = container.querySelector('.alat-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = '';
        newRow.querySelector('input').value = '1';
        container.appendChild(newRow);
    }

    function removeRow(button) {
        const rows = document.querySelectorAll('.alat-row');
        if (rows.length > 1) {
            button.closest('.alat-row').remove();
        } else {
            alert('Minimal harus ada 1 alat yang dipilih.');
        }
    }
</script>
@endsection