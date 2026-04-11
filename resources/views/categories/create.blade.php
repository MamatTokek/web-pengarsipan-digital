@extends('layouts.admin')

@section('content')
{{-- Breadcrumb navigasi yang jelas --}}
<h1 class="text-3xl font-semibold text-gray-800 mb-6">Kelola Arsip > Tambah Arsip > Tambah Kategori</h1>

<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        
        {{-- PENTING: Tambahkan input hidden ini untuk menangkap ID Edit jika ada --}}
        @if(request('from_edit'))
            <input type="hidden" name="from_edit" value="{{ request('from_edit') }}">
        @endif
        
        <div class="mb-8">
            <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">
                Nama Kategori Baru
            </label>
            
            <input type="text" name="name" id="name" required autofocus
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 
                          focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                   placeholder="Contoh: Surat Perizinan, Berkas Keuangan...">
            
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3 border-t border-gray-100 pt-6">
            {{-- MODIFIKASI: Tombol Batal juga harus dinamis kembali ke asal --}}
            @if(request('from_edit'))
                <a href="{{ route('archives.edit', request('from_edit')) }}" 
                   class="py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-150">
                    Batal
                </a>
            @else
                <a href="{{ route('archives.create') }}" 
                   class="py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-150">
                    Batal
                </a>
            @endif
            
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md shadow-md transition duration-150">
                Simpan Kategori
            </button>
        </div>
    </form>
</div>
@endsection