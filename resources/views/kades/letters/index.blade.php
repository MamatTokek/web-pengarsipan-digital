{{-- resources/views/kades/letters/index.blade.php --}}

@extends('layouts.admin') 

@section('content') 

    <div class="py-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Dokumen') }}
        </h2>
    </div>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Dokumen</h3>

                    {{-- Form Pencarian --}}
                    <div class="flex justify-end mb-4">
                        <form method="GET" action="{{ route('kades.letters.index') }}" class="flex items-center">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Cari..." 
                                   value="{{ $search ?? '' }}"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mr-2">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Cari
                            </button>
                            @if ($search)
                            <a href="{{ route('kades.letters.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-800">
                                Reset
                            </a>
                            @endif 
                        </form>
                    </div>

                    {{-- Tabel Daftar Surat --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Surat</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($letters as $letter)
                                <tr>
                                    {{-- $letters->firstItem() + $loop->index untuk nomor urut paginasi yang benar --}}
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $letters->firstItem() + $loop->index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $letter->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $letter->category->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $letter->uploaded_at->format('d/m/Y') }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        {{-- Tombol Lihat (Detail) - Menggunakan route umum --}}
                                        <a href="{{ route('letters.show', $letter) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                            Lihat
                                        </a>
                                        
                                        {{-- Tombol Unduh (Download) - Menggunakan route umum --}}
                                        <a href="{{ route('letters.download', $letter) }}" class="text-green-600 hover:text-green-900">
                                            Unduh
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data surat yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse {{-- <-- PENUTUP FORELSE --}}
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Link Pagination --}}
                    <div class="mt-4">
                        {{ $letters->appends(['search' => $search])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection {{-- <-- BARIS TERAKHIR / PENUTUP SECTION --}}