@php
    $reply = null;
    
    if ($letter->category_id == 2) {
        // Surat Keluar: Karena sistem timpa, balasannya adalah dirinya sendiri
        if ($letter->action_status === 'completed') {
            $reply = $letter;
        }
    } else {
        // Surat Masuk: Mencari balasan yang memiliki reply_to_id ke surat ini
        $reply = \App\Models\Letter::where('reply_to_id', $letter->id)->first();
    }
@endphp
@extends('layouts.admin')

@section('content')
<h1 class="text-3xl font-semibold text-gray-800">Daftar Dokumen > Lihat Dokumen</h1>
{{-- MODIFIKASI: Menambahkan variabel qrUrl untuk kontrol Modal & QR --}}
<div class="container mx-auto px-4 py-8" x-data="{ openModal: false, previewUrl: '', previewTitle: '', qrUrl: '' }">
    {{-- Header Halaman --}}
    <div class="mb-8">
        
    </div>

    {{-- Grid Utama Perbandingan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
        
        {{-- SISI KIRI: DETAIL SURAT MASUK --}}
        <div class="flex">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden flex flex-col w-full">
                <div class="bg-gray-800 px-6 py-4">
                    <h3 class="text-white font-bold flex items-center uppercase tracking-wider text-sm">
                        <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Referensi Surat Masuk
                    </h3>
                </div>
                
                <div class="p-6 flex-grow">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nama Surat</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $letter->name }}</dd>
                        </div>

                        {{-- MODIFIKASI: Menampilkan Nomor Surat Resmi Sisi Kiri --}}
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nomor Surat Resmi</dt>
                            <dd class="mt-1 text-sm font-mono text-indigo-600 font-bold uppercase">{{ $letter->letter_number ?? 'Tidak Terdata' }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Kategori</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold uppercase">
                                    {{ $letter->category->name ?? 'Tanpa Kategori' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tanggal Diterima</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($letter->uploaded_at)->format('d F Y') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Catatan Admin</dt>
                            <dd class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-700">
                                {{ $letter->admin_note ?? 'Tidak ada catatan khusus.' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="p-6 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Tombol Lihat dengan Modal & QR --}}
                        <button type="button" 
                            @click="previewUrl = '{{ route('letters.show', $letter->id) }}'; 
                                    previewTitle = '{{ $letter->name }}'; 
                                    qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $letter->uuid ? route('public.verify.letter', $letter->uuid) : '' }}');
                                    openModal = true"
                            class="flex items-center justify-center bg-gray-700 hover:bg-gray-800 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Surat
                        </button>

                        <a href="{{ route('letters.download', $letter->id) }}" class="flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Surat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: DETAIL SURAT BALASAN --}}
        <div class="flex">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden flex flex-col w-full">
                <div class="{{ $reply ? 'bg-green-600' : 'bg-orange-500' }} px-6 py-4">
                    <h3 class="text-white font-bold flex items-center uppercase tracking-wider text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        {{ $reply ? 'Dokumen Balasan (Selesai)' : 'Status: Perlu Tindakan' }}
                    </h3>
                </div>
                
                <div class="p-6 flex-grow">
                    @if($reply)
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nama Dokumen Balasan</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $reply->name }}</dd>
                            </div>

                            {{-- MODIFIKASI: Menampilkan Nomor Surat Resmi Sisi Kanan --}}
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nomor Surat Balasan</dt>
                                <dd class="mt-1 text-sm font-mono text-indigo-600 font-bold uppercase">{{ $reply->letter_number ?? 'Tidak Terdata' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tanggal Balasan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($reply->uploaded_at)->format('d F Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">Status Tindakan</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase flex items-center w-fit">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Sudah Dibalas
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold text-gray-500 uppercase tracking-widest">File Balasan</dt>
                                <dd class="mt-1 p-3 bg-indigo-50 border border-indigo-100 rounded-md text-sm text-indigo-700 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 11-2.828-2.828l6.414-6.414a4 4 0 015.656 5.656l-6.415 6.414a6 6 0 11-8.486-8.486L10.5 10" />
                                    </svg>
                                    {{ $reply->original_file_name }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-center py-10">
                            <div class="bg-orange-50 p-6 rounded-full mb-4">
                                <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Belum Ada Balasan</h4>
                            <p class="text-gray-500 text-sm mt-2 max-w-xs">Surat ini memerlukan tindakan balasan segera dari pihak pemerintah desa.</p>
                        </div>
                    @endif
                </div>

                <div class="p-6 border-t border-gray-100 space-y-3">
                    @if($reply)
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Tombol Lihat Balasan dengan QR --}}
                            <button type="button" 
                                @click="previewUrl = '{{ route('letters.show', $reply->id) }}?t={{ time() }}'; 
                                        previewTitle = '{{ $reply->name }}'; 
                                        qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $reply->uuid ? route('public.verify.letter', $reply->uuid) : '' }}');
                                        openModal = true"
                                class="flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat Balasan
                            </button>
                            <a href="{{ route('letters.download', $reply->id) }}" class="flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh Balasan
                            </a>
                        </div>
                        {{-- Tombol Edit Balasan --}}
                        <a href="{{ route('kades.documents.edit_reply', [$letter->id, 'source' => 'detail']) }}" class="flex items-center justify-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Balasan
                        </a>
                    @else
                        <a href="{{ route('kades.documents.reply', [$letter->id, 'source' => 'detail']) }}" class="flex items-center justify-center w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-150 transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Balas Surat Sekarang
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Navigasi Kembali --}}
    <div class="mt-10 flex justify-center">
        <a href="{{ route('kades.documents.index') }}" class="group flex items-center bg-white border-2 border-gray-200 text-gray-600 hover:text-gray-800 hover:border-gray-400 font-bold py-3 px-10 rounded-xl shadow-sm transition-all duration-200 uppercase text-xs tracking-[0.2em]">
            <svg class="w-5 h-5 mr-3 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Dokumen
        </a>
    </div>

    {{-- MODAL PREVIEW DENGAN QR CODE (Terintegrasi) --}}
    <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="openModal = false"></div>
            <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900" x-text="previewTitle"></h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="bg-white p-4">
                    <div class="flex flex-col md:flex-row gap-6">
                        {{-- QR Sidebar --}}
                        <div class="w-full md:w-1/4 flex flex-col items-center justify-start border-r pr-4 text-center">
                            <template x-if="qrUrl">
                                <div>
                                    <img :src="qrUrl" alt="QR Code" class="w-32 h-32 mx-auto border p-2 bg-white shadow-sm rounded-lg">
                                    <p class="mt-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-tight">
                                        Pindai untuk<br>Verifikasi Digital
                                    </p>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Dokumen Preview --}}
                        <div class="w-full md:w-3/4">
                            <template x-if="previewUrl">
                                <iframe :src="previewUrl" class="w-full h-[65vh] rounded border shadow-inner" frameborder="0"></iframe>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection