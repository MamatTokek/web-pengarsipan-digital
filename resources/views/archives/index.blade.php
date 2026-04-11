@extends('layouts.admin')

@section('content')
<h1 class="text-3xl font-semibold text-gray-800 mb-6">Kelola Arsip</h1>

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

{{-- Kontrol Modal terpusat di x-data ini --}}
<div class="bg-white p-6 rounded-lg shadow-md" x-data="{ openModal: false, previewUrl: '', previewTitle: '', qrUrl: '' }">

    {{-- Header Kontrol --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">

        {{-- SISI KIRI: Tombol Tambah --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('archives.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150">
                <svg class="w-5 h-5 inline-block mr-1 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Arsip
            </a>
        </div>

        {{-- SISI KANAN: Filter + Pencarian --}}
        <div class="flex items-center gap-3 w-full md:w-1/3">

            {{-- FILTER --}}
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="flex items-center justify-center p-2 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    title="Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false"
                    class="absolute left-0 mt-2 w-64 origin-top-left bg-white border border-gray-200 rounded-md shadow-lg z-50 p-4"
                    x-transition style="display: none;">
                    <form action="{{ route('archives.index') }}" method="GET" class="space-y-4">
                        <input type="hidden" name="search" value="{{ request('search') }}">

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
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Bulan</label>
                            <select name="month" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</label>
                            <select name="year" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                <option value="">Semua Tahun</option>
                                @php $currentYear = date('Y'); $startYear = 2022; @endphp
                                @for ($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button type="submit" 
                                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                Terapkan
                            </button>

                            @if(request('search') || request('category') || request('month') || request('year'))
                                <a href="{{ route('archives.index') }}" 
                                class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- FORM PENCARIAN --}}
            <form action="{{ route('archives.index') }}" method="GET" class="w-full">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="month" value="{{ request('month') }}">
                <input type="hidden" name="year" value="{{ request('year') }}">

                <div class="relative">
                    <input type="text" name="search" placeholder="Cari Dokumen, Kategori..."
                        value="{{ request('search') }}"
                        class="w-full p-2 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">

                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 a7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>
        </div>
    </div>

    {{-- BAGIAN TABEL --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No.</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Informasi Dokumen</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Kategori</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Tanggal Upload</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($archives as $archive)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $archives->firstItem() + $loop->index }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 leading-tight">
                            {{ $archive->name }}
                        </div>
                        @if($archive->letter_number)
                        <div class="text-sm text-indigo-600 font-mono mt-1 uppercase">
                            {{ $archive->letter_number }}
                        </div>
                        @else
                        <div class="text-[11px] text-gray-400 mt-1">
                            Tanpa Nomor Resmi
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($archive->description, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $archive->category->name ?? 'Tidak Ada' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $archive->uploaded_at->format('d/m/Y') }}</td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center space-x-3">
                            <button type="button" 
                                @click="previewUrl = '{{ route('archives.show', $archive->id) }}?t={{ time() }}'; 
                                        previewTitle = '{{ $archive->name }}'; 
                                        qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('{{ $archive->uuid ? route('public.verify.archive', $archive->uuid) : '' }}');
                                        openModal = true"
                                class="text-blue-600 hover:text-blue-900 transition-colors" 
                                title="Pratinjau">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>

                            <a href="{{ route('archives.download', $archive->id) }}" class="text-green-600 hover:text-green-900 transition-colors" title="Unduh">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>

                            <a href="{{ route('archives.edit', $archive->id) }}" class="text-yellow-600 hover:text-yellow-900 transition-colors" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>

                            {{-- PENGGANTI HAPUS: MINTA HAPUS (KIRIM PESAN KE SUPER ROLE) --}}
                            <a href="{{ route('messages.create', ['subject' => 'Permohonan Hapus Arsip: ' . ($archive->letter_number ?? 'Tanpa Nomor'), 'body' => 'Kepada Super Role, mohon bantuannya untuk menghapus arsip dengan Nama: ' . $archive->name . '']) }}" 
                               class="text-red-500 hover:text-red-700 transition-colors" 
                               title="Minta Hapus Arsip">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data arsip yang diunggah.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $archives->appends(request()->query())->links() }}
    </div>

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