@extends('layouts.admin')

@section('content')
<h1 class="text-3xl font-semibold text-gray-800 mb-6">Pesan</h1>

{{-- NOTIFIKASI SUKSES (Dapat ditutup manual) --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" 
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm" 
        role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
            <svg class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
@endif

{{-- NOTIFIKASI ERROR (Opsional, jika ingin konsisten) --}}
@if (session('error'))
    <div x-data="{ show: true }" x-show="show" 
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-sm" 
        role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
            <svg class="fill-current h-6 w-6 text-red-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
@endif

{{-- Kontainer Utama --}}
<div class="bg-white p-6 rounded-lg shadow-md">

    {{-- Header Kontrol --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        
        {{-- SISI KIRI: Tombol Tulis Pesan Baru --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('messages.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 text-sm">
                <svg class="w-5 h-5 inline-block mr-1 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tulis Pesan Baru
            </a>
        </div>

        {{-- SISI KANAN: Filter Dropdown --}}
        <div class="flex items-center gap-3">
            <div class="relative inline-block text-left" x-data="{ open: false }">
                {{-- Tombol Ikon Filter --}}
                <button type="button" @click="open = !open"
                    class="flex items-center justify-center p-2 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    title="Filter Pesan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-64 origin-top-right bg-white border border-gray-200 rounded-md shadow-lg z-50 p-4"
                    x-transition style="display: none;">
                    
                    <form action="{{ route('messages.index') }}" method="GET" class="space-y-4">
                        {{-- Filter Status Pesan --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Jenis Pesan</label>
                            <select name="status" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Pesan</option>
                                <option value="masuk" {{ request('status') == 'masuk' ? 'selected' : '' }}>Pesan Masuk</option>
                                <option value="terkirim" {{ request('status') == 'terkirim' ? 'selected' : '' }}>Pesan Terkirim</option>
                            </select>
                        </div>

                        {{-- Filter Bulan --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Bulan</label>
                            <select name="month" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filter Tahun --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</label>
                            <select name="year" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Tahun</option>
                                @php $currentYear = date('Y'); @endphp
                                @for ($y = $currentYear; $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="pt-2 space-y-2">
                            <button type="submit" 
                                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                Terapkan
                            </button>

                            @if(request('status') || request('month') || request('year'))
                                <a href="{{ route('messages.index') }}" 
                                    class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                {{ request('status') ? 'Kotak ' . request('status') : 'Kotak Masuk Internal' }}
            </span>
        </div>
    </div>

    {{-- BAGIAN TABEL --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Informasi</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Subjek & Isi Pesan</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Waktu</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($messages as $message)
                @php
                    $isSent = $message->sender_id === auth()->id(); // Cek apakah ini pesan terkirim
                @endphp
                <tr class="{{ !$message->isReadBy(auth()->id()) && !$isSent ? 'bg-indigo-50/50' : '' }} hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $messages->firstItem() + $loop->index }}
                    </td>

                    {{-- KOLOM STATUS BARU --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($isSent)
                            <span class="px-2 py-1 text-[10px] font-bold rounded-md bg-green-100 text-green-700 uppercase tracking-wider border border-green-200">Terkirim</span>
                        @else
                            <span class="px-2 py-1 text-[10px] font-bold rounded-md bg-blue-100 text-blue-700 uppercase tracking-wider border border-blue-200">Masuk</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @php
                                $isSent = $message->sender_id === auth()->id(); // Menentukan apakah pesan terkirim atau masuk
                                
                                if ($isSent) {
                                    $displayUser = $message->receiver->name ?? ($message->target_role === 'all' ? 'Semua Pengguna' : 'Role: '.ucwords($message->target_role));
                                    $prefix = 'Ke';
                                    $prefixColor = 'text-green-600';
                                    $avatarBg = 'bg-green-100 text-green-700';
                                    $roleLabel = 'Penerima';
                                } else {
                                    $displayUser = $message->sender->name;
                                    $prefix = 'Dari';
                                    $prefixColor = 'text-indigo-600';
                                    $avatarBg = 'bg-indigo-100 text-indigo-700';
                                    $roleLabel = str_replace('_', ' ', $message->sender->role);
                                }
                            @endphp

                            {{-- 1. Prefix Teks (Diletakkan paling depan) --}}
                            <div class="mr-3 text-[10px] font-bold uppercase tracking-tighter {{ $prefixColor }} min-w-[30px]">
                                {{ $prefix }}:
                            </div>

                            {{-- 2. Avatar --}}
                            <div class="h-9 w-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-xs font-bold mr-3 uppercase border shadow-sm flex-shrink-0">
                                {{ substr($displayUser, 0, 2) }}
                            </div>

                            {{-- 3. Informasi Nama dan Role --}}
                            <div class="overflow-hidden">
                                <div class="text-sm font-bold text-gray-900 leading-tight truncate">
                                    {{ $displayUser }}
                                </div>
                                <div class="text-[10px] text-gray-500 font-mono uppercase mt-0.5 tracking-wider">
                                    {{ $roleLabel }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm">
                        {{-- Hanya menampilkan Subjek --}}
                        <div class="text-gray-900 {{ !$message->is_read ? 'font-extrabold' : 'font-bold' }}">
                            {{ $message->subject }}
                        </div>
                        {{-- TAMBAHKAN BARIS INI: Menampilkan potongan isi pesan --}}
                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                            {{ Str::limit($message->body, 50) }}
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500 text-center whitespace-nowrap">
                        {{ $message->created_at->diffForHumans() }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-2">
                            {{-- Action Menggunakan Tulisan BUKA --}}
                            <a href="{{ route('messages.show', $message->id) }}" 
                                class="text-indigo-600 hover:text-indigo-900 font-extrabold text-xs uppercase tracking-widest transition-colors" 
                                title="Buka Pesan">
                                BUKA
                            </a>

                            {{-- PERBAIKAN LOGIKA TITIK MERAH --}}
                            {{-- Titik merah hanya muncul jika: --}}
                            {{-- 1. BUKAN pesan terkirim (!$isSent) --}}
                            {{-- 2. DAN belum dibaca oleh user yang sedang login (!$message->isReadBy(auth()->id())) --}}
                            @if(!$isSent && !$message->isReadBy(auth()->id()))
                                <span class="flex h-2 w-2 rounded-full bg-red-500 animate-pulse" title="Pesan Baru Belum Dibaca"></span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        {{-- Ubah colspan menjadi 6 agar tulisan benar-benar berada di tengah tabel --}}
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 font-medium">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span>Belum ada pesan masuk atau terkirim di kotak pesan Anda.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $messages->appends(request()->query())->links() }}
    </div>
</div>
@endsection