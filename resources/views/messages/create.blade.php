@extends('layouts.admin')

@section('content')

{{-- Container Utama --}}
<div>
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Pesan > Tulis Pesan Baru</h1>

    <div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
        
        <form action="{{ route('messages.store') }}" method="POST" id="messageForm">
            @csrf

            {{-- 1. PILIH PENERIMA (Dropdown tunggal dengan opsi 'Semua') --}}
            <div class="mb-4">
                <label for="receiver_id" class="block text-sm font-medium text-gray-700">Pilih Penerima</label>
                <select name="receiver_id" id="receiver_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Penerima --</option>
                    {{-- Opsi untuk mengirim ke semua role --}}
                    <option value="all" {{ request('target_role') ? 'selected' : '' }}>Semua</option>
                    
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('receiver_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ strtoupper(str_replace('_', ' ', $user->role)) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. SUBJEK PESAN --}}
            <div class="mb-4">
                <label for="subject" class="block text-sm font-medium text-gray-700">Subjek Pesan</label>
                <input type="text" name="subject" id="subject" value="{{ request('subject') }}" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       placeholder="Misal: Permohonan Hapus Arsip #123">
            </div>

            {{-- 3. ISI PESAN --}}
            <div class="mb-6">
                <label for="body" class="block text-sm font-medium text-gray-700">Isi Pesan</label>
                <textarea name="body" id="body" rows="6" required
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500" 
                          placeholder="Jelaskan alasan atau instruksi Anda di sini...">{{ request('body') }}</textarea>
            </div>

            {{-- TOMBOL AKSI (Identik dengan Tambah Surat/Arsip) --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('messages.index') }}" 
                   class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 transform active:scale-95">
                    Kirim Pesan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection