@extends('layouts.admin')

@section('content')
{{-- Judul Halaman Sesuai Style Admin --}}
<h1 class="text-3xl font-semibold text-gray-800 mb-6">{{ __('Daftar Dokumen') }}</h1>

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

{{-- Ditambahkan x-data untuk kontrol Modal & Dropdown Filter & QR --}}
<div class="bg-white p-6 rounded-lg shadow-md" x-data="{ openFilter: false, openModal: false, previewUrl: '', previewTitle: '', qrUrl: '' }">

    {{-- Header Kontrol --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        
        {{-- Sisi Kiri: Judul Tabel --}}
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Surat dan Arsip Desa</h3>
        </div>

        {{-- Sisi Kanan: Filter + Pencarian sejajar --}}
        <div class="flex items-center gap-3 w-full md:w-1/2 justify-end">

            {{-- TOMBOL FILTER --}}
            <div class="relative inline-block text-left">
                <button type="button" @click="openFilter = !openFilter"
                    class="flex items-center justify-center p-2 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    title="Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>

                {{-- Dropdown Filter Menu --}}
                <div x-show="openFilter" @click.away="openFilter = false"
                    class="absolute left-0 mt-2 w-64 origin-top-left bg-white border border-gray-200 rounded-md shadow-lg z-50 p-4"
                    x-transition style="display: none;">
                    
                    <form action="{{ route('kades.documents.index') }}" method="GET" class="space-y-4">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Jenis</label>
                            <select name="type" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua</option>
                                <option value="Surat" {{ request('type') == 'Surat' ? 'selected' : '' }}>Surat</option>
                                <option value="Arsip" {{ request('type') == 'Arsip' ? 'selected' : '' }}>Arsip</option>
                            </select>
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kategori</label>
                            <select name="category" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                            <select name="status" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Perlu Tindakan</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
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

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</label>
                            <select name="year" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button type="submit" 
                                    class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                Terapkan
                            </button>

                            @if(request('category') || request('status') || request('month') || request('year') || request('type') || request('search'))
                                <a href="{{ route('kades.documents.index') }}" 
                                    class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- FORM PENCARIAN --}}
            <div class="w-full md:w-64">
                <form method="GET" action="{{ route('kades.documents.index') }}">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    
                    <div class="relative">
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
    </div>

    {{-- Bagian Tabel --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis & Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Informasi Dokumen</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $document)
                <tr class="{{ $document->action_status === 'pending' ? 'bg-orange-50/50' : 'hover:bg-gray-50' }} transition-colors">
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
                            
                            @if($document->action_status === 'pending')
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-100 text-red-700 border border-red-200 uppercase">Perlu Tindakan</span>
                            @elseif($document->action_status === 'completed')
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-green-100 text-green-700 border border-green-200 uppercase">Selesai</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900 leading-tight">{{ $document->name }}</div>
                        
                        {{-- MODIFIKASI: Menambahkan Nomor Surat (Stacking) --}}
                        @if(isset($document->letter_number) && $document->letter_number)
                            <div class="text-sm text-indigo-600 font-mono mt-1 uppercase">
                                {{ $document->letter_number }}
                            </div>
                        @elseif($document->type_label === 'Arsip')
                            <div class="text-[11px] text-gray-400 mt-1">
                                Tanpa Nomor Resmi
                            </div>
                        @endif

                        @if($document->admin_note)
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

                        @if($document->action_status === 'completed')
                            @php
                                $reply = \App\Models\Letter::where('reply_to_id', $document->id)->first();
                            @endphp
                            @if($reply)
                                <div class="mt-2 text-sm text-green-800 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200 inline-flex items-center shadow-sm">
                                    <svg class="w-4 h-4 mr-2 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                                    </svg>
                                    <div>
                                        <span class="font-bold uppercase text-[11px] tracking-wider opacity-80 mr-1">Balasan:</span>
                                        <button @click="previewUrl = '{{ route('letters.show', $reply->id) }}?t={{ time() }}'; 
                                                previewTitle = '{{ $reply->name }}'; 
                                                qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $reply->uuid ? route('public.verify.letter', $reply->uuid) : '' }}');
                                                openModal = true" 
                                                class="underline hover:text-green-900 font-bold text-left">
                                            {{ $reply->name }}
                                        </button>
                                    </div>
                                </div>
                            @endif
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
                            // Pastikan kita mengambil data balasan untuk mendapatkan ID-nya
                            $replyData = \App\Models\Letter::where('reply_to_id', $document->id)->first();
                        @endphp

                        {{-- LOGIKA TOMBOL LIHAT & DOWNLOAD --}}
                        @php
                            // Definisikan rute verifikasi untuk QR Code di dalam modal
                            $verifyRoute = $document->type_label === 'Surat' ? 'public.verify.letter' : 'public.verify.archive';
                        @endphp

                        @if($document->type_label === 'Surat' && $document->category_id != 2 && ($document->action_status === 'pending' || $document->action_status === 'completed'))
                            {{-- JALUR SURAT MASUK (Bukan ID 2): Tetap masuk ke Halaman Detail Perbandingan --}}
                            <a href="{{ route('kades.documents.show_action', $document->id) }}" 
                            class="text-blue-600 hover:text-blue-900 transition-colors" 
                            title="Lihat Detail Tindakan">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        @else
                            {{-- JALUR SURAT KELUAR (ID 2) ATAU ARSIP: Langsung Munculkan MODAL PREVIEW --}}
                            <button type="button" 
                                @click="previewUrl = '{{ route($document->route_name_prefix . '.show', $document->id) }}?t={{ time() }}'; 
                                        previewTitle = '{{ $document->name }}'; 
                                        qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $document->uuid ? route($verifyRoute, $document->uuid) : '' }}');
                                        openModal = true"
                                class="text-blue-600 hover:text-blue-900 transition-colors" 
                                title="Pratinjau Dokumen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            {{-- Tombol Download hanya muncul di jalur modal --}}
                            <a href="{{ route($document->route_name_prefix . '.download', $document->id) }}" 
                            class="text-green-600 hover:text-green-900 transition-colors" title="Download">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>
                        @endif

                            {{-- LOGIKA TOMBOL BALAS / EDIT BALASAN --}}
                            @if($document->action_status === 'pending' && $document->type_label === 'Surat')
                                <a href="{{ route('kades.documents.reply', $document->id) }}" 
                                class="inline-flex items-center px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-bold rounded shadow-sm transition duration-150">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    BALAS
                                </a>
                                @elseif($document->action_status === 'completed' && $document->type_label === 'Surat')
                                {{-- Mengirim $document->id (ID Surat Asli) karena Route web.php mengharapkan {letter} --}}
                                <a href="{{ route('kades.documents.edit_reply', $document->id) }}" 
                                class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded shadow-sm transition duration-150" title="Edit Balasan">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    EDIT BALASAN
                                </a>
                            @endif
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