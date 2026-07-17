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
            
            {{-- Kartu 2: Total Surat (Aksen Merah) --}}
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

            {{-- Kartu 3: Total Arsip (Aksen Hijau) --}}
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500 flex items-center">
                <div class="p-3 rounded-lg bg-green-50 mr-4">
                    {{-- Mengganti ikon menjadi ikon Folder/Arsip --}}
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5M5 19v-4a2 2 0 002-2h12a2 2 0 002 2v4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL ARSIP</h3>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalArchives }}</p>
                </div>
            </div>
        </div>

        {{-- Filter dan Grafik --}}
        <div class="bg-white p-6 rounded-xl shadow-md mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Grafik Dokumen</h2>
            </div>

            {{-- Kanvas tempat merender grafik Chart.js --}}
            <div class="relative h-72 w-full">
                <canvas id="dashboardChart"></canvas>
            </div>
        </div>

        {{-- Tabel Dokumen Terbaru --}}
        <div class="bg-white p-6 rounded-lg shadow-md">

            {{-- Baris Flex Judul dan Filter Popover --}}
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Dokumen Terbaru</h2>

                {{-- FILTER POPOVER BERGAYA KELOLA SURAT --}}
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex items-center justify-center p-2 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        title="Filter Grafik">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-64 origin-top-right bg-white border border-gray-200 rounded-md shadow-lg z-50 p-4"
                        x-transition style="display: none;">
                        
                        {{-- Form diarahkan ke URL Dashboard saat ini --}}
                        <form action="{{ url()->current() }}" method="GET" class="space-y-4">
                            {{-- Pertahankan parameter search global --}}
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

                            {{-- 2. Filter Kategori Surat (Muncul hanya jika memilih opsi Surat) --}}
                            <div id="wrapper_cat_letter" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kategori Surat</label>
                                <select name="category" id="cat_letter_select" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Kategori Surat</option>
                                    @foreach($letterCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ $currentCategory == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2.5 Filter Status Surat (Kondisional: Ikut muncul hanya saat memilih opsi Surat) --}}
                            <div id="wrapper_status_letter" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status Tindakan</label>
                                <select name="status" id="status_letter_select" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ $currentStatus == 'pending' ? 'selected' : '' }}>Perlu Tindakan</option>
                                    <option value="completed" {{ $currentStatus == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="no_action" {{ $currentStatus == 'no_action' ? 'selected' : '' }}>Surat Biasa</option>
                                </select>
                            </div>

                            {{-- 3. Filter Kategori Arsip (Muncul hanya jika memilih opsi Arsip) --}}
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
                                        <option value="{{ $i }}" {{ $currentMonth == $i ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
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

                            {{-- Tombol Terapkan & Reset --}}
                            <div class="pt-2 space-y-2">
                                <button type="submit" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                    Terapkan Filter
                                </button>
                                @if($currentDocType || $currentCategory || $currentStatus || $currentMonth || $currentYear != date('Y'))
                                    <a href="{{ url()->current() }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Informasi Surat/Dokumen</th>
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

                                @if($document->type === 'letter' && $document->need_action)
                                    <div class="mt-1">
                                        @if($document->action_status === 'pending')
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-100 text-red-700 border border-red-200 uppercase inline-block">
                                            Perlu Tindakan
                                        </span>
                                        @elseif($document->action_status === 'completed')
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-green-100 text-green-700 border border-green-200 uppercase inline-block">
                                            Selesai
                                        </span>
                                        @endif
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
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 italic">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Render Canvas Chart.js
    const ctx = document.getElementById('dashboardChart').getContext('2d');
    const dataStatistik = {!! $chartData !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Dokumen Dikumpul',
                data: dataStatistik,
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                borderRadius: 5,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>