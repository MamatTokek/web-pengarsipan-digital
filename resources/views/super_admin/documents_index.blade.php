@extends('layouts.admin')

@section('content')
{{-- Judul Halaman Sesuai Style Admin --}}
<h1 class="text-3xl font-semibold text-gray-800 mb-6">{{ __('Kelola Dokumen') }}</h1>

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

{{-- Container Utama dengan x-data gabungan dari Kades + Super Role Logic --}}
<div class="bg-white p-6 rounded-lg shadow-md" x-data="{ openFilter: false, openModal: false, previewUrl: '', previewTitle: '', qrUrl: '' }">

    {{-- Header Kontrol (Penyelarasan Sejajar items-end untuk Super Role) --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
        
        {{-- Sisi Kiri: Status Mode (Ciri Khas Halaman Super Role) --}}
        <div class="flex items-center gap-3">
            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-red-200">
                Mode Eksekusi Data
            </span>
        </div>

        {{-- Sisi Kanan: Filter Bertingkat + Pencarian Sejajar Sempurna --}}
        <div class="w-full md:w-auto flex justify-end">

            {{-- FORM UTAMA (Membungkus parameter agar menyatu dan mengunci posisi elemen sebaris) --}}
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-3 w-full md:w-auto">

                {{-- Pertahankan state parameter filter saat user mengetik teks di search bar --}}
                <input type="hidden" name="doc_type" value="{{ request('doc_type') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="month" value="{{ request('month') }}">
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">

                {{-- FILTER POPOVER BERGAYA KELOLA SURAT --}}
                <div class="relative inline-block text-left">
                    <button type="button" @click="openFilter = !openFilter"
                        class="flex items-center justify-center p-2 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        title="Filter">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </button>

                    {{-- Dropdown Filter Menu Menu (Diposisikan left-0 agar melebar ke kanan) --}}
                    <div x-show="openFilter" @click.away="openFilter = false"
                        class="absolute left-0 mt-2 w-64 origin-top-left bg-white border border-gray-200 rounded-md shadow-lg z-50 p-4"
                        x-transition style="display: none;">
                        
                        <div class="space-y-4">
                            {{-- Pertahankan teks pencarian saat filter diubah --}}
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            
                            {{-- 1. Filter Tipe Dokumen --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Dokumen</label>
                                <select name="doc_type" id="filter_doc_type" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Dokumen</option>
                                    <option value="letter" {{ $currentDocType == 'letter' ? 'selected' : '' }}>Surat</option>
                                    <option value="archive" {{ $currentDocType == 'archive' ? 'selected' : '' }}>Arsip</option>
                                </select>
                            </div>

                            {{-- 2. Filter Kategori Surat (Kondisional via JS) --}}
                            <div id="wrapper_cat_letter" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kategori Surat</label>
                                <select name="category" id="cat_letter_select" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Kategori Surat</option>
                                    @foreach($letterCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ $currentCategory == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2.5 Filter Status Surat (Kondisional via JS) --}}
                            <div id="wrapper_status_letter" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status Tindakan</label>
                                <select name="status" id="status_letter_select" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ $currentStatus == 'pending' ? 'selected' : '' }}>Perlu Tindakan</option>
                                    <option value="completed" {{ $currentStatus == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="no_action" {{ $currentStatus == 'no_action' ? 'selected' : '' }}>Surat Biasa</option>
                                </select>
                            </div>

                            {{-- 3. Filter Kategori Arsip (Kondisional via JS) --}}
                            <div id="wrapper_cat_archive" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kategori Arsip</label>
                                <select name="category" id="cat_archive_select" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Kategori Arsip</option>
                                    @foreach($archiveCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ $currentCategory == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 4. Filter Bulan --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Bulan</label>
                                <select name="month" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Bulan</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $currentMonth == $i ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            {{-- 5. Filter Tahun --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</label>
                                <select name="year" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tombol Terapkan & Reset untuk Halaman Super Role --}}
                            <div class="pt-2 space-y-2">
                                <button type="submit" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                    Terapkan Filter
                                </button>
                                @if($currentDocType || $currentCategory || $currentStatus || $currentMonth || $currentYear != date('Y') || request('search'))
                                    <a href="{{ url()->current() }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORM PENCARIAN --}}
                <div class="w-full md:w-64 relative">
                    <input type="text" 
                        name="search" 
                        placeholder="Cari dokumen..." 
                        value="{{ request('search') }}"
                        class="w-full p-2 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 a7 7 0 0114 0z" />
                    </svg>
                </div>

            </form>
        </div>
    </div>

    {{-- Bagian Tabel --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis & Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informasi Dokumen</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $document)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $documents->firstItem() + $loop->index }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex flex-col items-center gap-1.5">
                            @if($document->type_label === 'Surat')
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-blue-100 text-blue-700 uppercase tracking-wider">Surat</span>
                            @else
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-purple-100 text-purple-700 uppercase tracking-wider">Arsip</span>
                            @endif
                            
                            @if(isset($document->action_status) && $document->action_status === 'pending')
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-100 text-red-700 border border-red-200 uppercase">Perlu Tindakan</span>
                            @elseif(isset($document->action_status) && $document->action_status === 'completed')
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-green-100 text-green-700 border border-green-200 uppercase">Selesai</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900 leading-tight">{{ $document->name }}</div>
                        @if(isset($document->letter_number) && $document->letter_number)
                            <div class="text-sm text-indigo-600 font-mono mt-1 uppercase">
                                {{ $document->letter_number }}
                            </div>
                        @else
                            <div class="text-[11px] text-gray-400 mt-1">Tanpa Nomor Resmi</div>
                        @endif

                        @if(isset($document->admin_note) && $document->admin_note)
                        <div x-data="{ expanded: false }" 
                            class="mt-2 text-sm text-indigo-800 bg-indigo-50 rounded-lg border border-indigo-200 shadow-sm transition-all duration-300 w-fit max-w-[220px]">
                            <div class="px-3 py-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col">
                                        <span class="font-bold uppercase text-[9px] tracking-wider opacity-70 mb-1">Ket:</span> 
                                        {{-- Teks Catatan dengan line-clamp --}}
                                        <p :class="expanded ? '' : 'line-clamp-2'" class="font-medium">
                                            {{ $document->admin_note }}
                                        </p>
                                    </div>

                                    {{-- Toggle Anak Panah Saja --}}
                                    @if(strlen($document->admin_note) > 45)
                                    <button @click="expanded = !expanded" 
                                            type="button" 
                                            class="mt-4 p-1 rounded-full hover:bg-indigo-200 text-indigo-500 transition-colors focus:outline-none"
                                            title="Toggle Detail">
                                        <svg class="w-4 h-4 transform transition-transform duration-300" 
                                            :class="expanded ? 'rotate-180' : ''" 
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($document->reply_letter) && $document->reply_letter)
                            <div class="mt-2 text-sm text-green-800 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200 inline-flex items-center shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                                </svg>
                                <div>
                                    <span class="font-bold uppercase text-[11px] tracking-wider opacity-80 mr-1">Balasan:</span>
                                    <button @click="previewUrl = '{{ route('letters.show', $letter->replyLetter->id) }}?t={{ time() }}'; 
                                            previewTitle = '{{ $letter->replyLetter->name }}'; 
                                            qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $letter->replyLetter->uuid ? route('public.verify.letter', $letter->replyLetter->uuid) : '' }}');
                                            openModal = true" class="underline hover:text-green-900 font-bold text-left">
                                        {{ $document->replyLetter->name }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $document->category->name ?? '-' }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ \Carbon\Carbon::parse($document->uploaded_at)->format('d/m/Y') }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center space-x-3">
                            
                            @php
                                $routePrefix = $document->type_label === 'Surat' ? 'letters' : 'archives';
                                $dbType = $document->type_label === 'Surat' ? 'letter' : 'archive';
                                $verifyRoute = $document->type_label === 'Surat' ? 'public.verify.letter' : 'public.verify.archive';
                            @endphp

                            {{-- 1. LIHAT --}}
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

                            {{-- 2. DOWNLOAD --}}
                            <a href="{{ route($routePrefix . '.download', $document->id) }}" 
                               class="text-green-600 hover:text-green-900 transition-colors" title="Download">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>

                            {{-- 3. HAPUS PERMANEN (Super Role) --}}
                            <form id="delete-form-{{ $document->id }}-{{ $dbType }}" action="{{ route('super_admin.documents_destroy', ['id' => $document->id, 'type' => $document->type]) }}" method="POST" class="inline">
                                @csrf 
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('{{ $document->id }}', '{{ $dbType }}')" class="text-red-600 hover:text-red-900 transition-colors" title="Hapus Permanen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada dokumen yang ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $documents->appends(request()->query())->links() }}</div>

    {{-- MODAL PREVIEW DENGAN QR CODE --}}
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

{{-- SweetAlert Logic untuk Super Role --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const docTypeSelect = document.getElementById('filter_doc_type');
    const wrapperLetter = document.getElementById('wrapper_cat_letter');
    const wrapperArchive = document.getElementById('wrapper_cat_archive');
    const catLetterSelect = document.getElementById('cat_letter_select');
    const catArchiveSelect = document.getElementById('cat_archive_select');
    const wrapperStatus = document.getElementById('wrapper_status_letter');
    const statusLetterSelect = document.getElementById('status_letter_select');

    // Fungsi pengatur visibilitas filter kategori bertingkat
    function handleBertingkatFilter() {
        const selectedValue = docTypeSelect.value;
        if (selectedValue === 'letter') {
            wrapperLetter.classList.remove('hidden');
            wrapperStatus.classList.remove('hidden');
            wrapperArchive.classList.add('hidden');
            catArchiveSelect.value = ""; 
            catLetterSelect.disabled = false;
            statusLetterSelect.disabled = false;
            catArchiveSelect.disabled = true;
        } else if (selectedValue === 'archive') {
            wrapperArchive.classList.remove('hidden');
            wrapperLetter.classList.add('hidden');
            wrapperStatus.classList.add('hidden');
            catLetterSelect.value = "";
            statusLetterSelect.value = ""; 
            catArchiveSelect.disabled = false;
            catLetterSelect.disabled = true;
            statusLetterSelect.disabled = true;
        } else {
            wrapperLetter.classList.add('hidden');
            wrapperArchive.classList.add('hidden');
            wrapperStatus.classList.add('hidden');
            catLetterSelect.value = "";
            catArchiveSelect.value = "";
            statusLetterSelect.value = "";
            catLetterSelect.disabled = true;
            catArchiveSelect.disabled = true;
            statusLetterSelect.disabled = true;
        }
    }

    // Jalankan saat load awal untuk menahan old value seleksi filter
    handleBertingkatFilter();
    docTypeSelect.addEventListener('change', handleBertingkatFilter);
});

    function confirmDelete(id, type) {
        Swal.fire({
            title: 'Hapus Data Secara Permanen?',
            text: "Tindakan ini akan menghapus file dari sistem selamanya dan tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6366F1',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id + '-' + type).submit();
            }
        })
    }
</script>
@endsection