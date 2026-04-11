@extends('layouts.admin')

@section('content')

    {{-- Judul Halaman --}}
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Dashboard</h1>
    
    {{-- Container utama dengan x-data untuk kontrol Modal --}}
    <div x-data="{ openModal: false, previewUrl: '', previewTitle: '', qrUrl: '' }">

        {{-- 2. Ringkasan Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Kartu 1: Total Dokumen (Aksen Biru) --}}
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-600 flex items-center">
                <div class="p-3 rounded-lg bg-blue-50 mr-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL DOKUMEN</h3>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalDocuments }}</p>
                </div>
            </div>
            
            {{-- Kartu 2: Total Kategori (Aksen Hijau) --}}
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500 flex items-center">
                <div class="p-3 rounded-lg bg-green-50 mr-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL KATEGORI</h3>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalCategories }}</p>
                </div>
            </div>
            
            {{-- Kartu 3: Total Surat (Aksen Merah) --}}
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-red-500 flex items-center">
                <div class="p-3 rounded-lg bg-red-50 mr-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.8 5.2a2 2 0 002.4 0L21 8m-1 11H4a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL SURAT</h3>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalLetters }}</p>
                </div>
            </div>
        </div>

        {{-- Tabel Dokumen Terbaru --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Dokumen & Surat Terbaru</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Informasi Dokumen</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Jenis</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">File Asli</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Kategori</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Tanggal Upload</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($latestDocuments as $document)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-4">
                                {{-- MODIFIKASI: Nama Dokumen dan Nomor Surat (Stacking) --}}
                                <div class="text-sm font-bold text-gray-900 leading-tight">
                                    {{ $document->name }}
                                </div>
                                @if($document->letter_number)
                                    <div class="text-sm text-indigo-600 font-mono mt-1 uppercase">
                                        {{ $document->letter_number }}
                                    </div>
                                @else
                                    <div class="text-[11px] text-gray-400 mt-1">
                                        Tanpa Nomor Resmi
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                @if(trim($document->type) == 'letter')
                                    <span class="px-2 py-1 text-xs font-bold rounded-md bg-blue-100 text-blue-700 uppercase tracking-wider">
                                        Surat
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold rounded-md bg-purple-100 text-purple-700 uppercase tracking-wider">
                                        Arsip
                                    </span>
                                @endif
                            </td> 

                            <td class="px-6 py-4 text-sm text-gray-500 text-left">
                                <span>{{ $document->original_file_name ?? 'N/A' }}</span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500 text-center whitespace-nowrap">
                                {{ $document->category->name ?? 'Tidak Ada' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500 text-center whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($document->uploaded_at)->format('d/m/Y') }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-3">
                                    @php
                                        $routePrefix = $document->type == 'letter' ? 'letters' : 'archives';
                                        $verifyRoute = $document->type == 'letter' ? 'public.verify.letter' : 'public.verify.archive';
                                    @endphp
                                    
                                    {{-- Tombol Lihat memicu Modal Preview lengkap dengan QR Code --}}
                                    <button type="button" 
                                        @click="previewUrl = '{{ route($routePrefix . '.show', $document->id) }}?t={{ time() }}'; 
                                                previewTitle = '{{ $document->name }}'; 
                                                qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $document->uuid ? route($verifyRoute, $document->uuid) : '' }}');
                                                openModal = true"
                                        class="text-blue-600 hover:text-blue-900 transition-colors" title="Pratinjau">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    {{-- Unduh --}}
                                    <a href="{{ route($routePrefix . '.download', $document->id) }}" 
                                    class="text-green-600 hover:text-green-900 transition-colors" title="Unduh">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada dokumen atau surat terbaru.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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