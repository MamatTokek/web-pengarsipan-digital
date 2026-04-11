@extends('layouts.admin')

@section('content')
{{-- JUDUL HALAMAN (Konsisten dengan menu lain) --}}
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Pesan > Lihat Pesan</h1>

<div class="max-w-4xl mx-auto px-4 py-8">

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Header Pesan --}}
        <div class="bg-gray-50 px-8 py-6 border-b border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $message->subject }}</h1>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="font-bold text-indigo-600 mr-2">{{ $message->sender->name }}</span>
                        <span class="mr-2">&bull;</span>
                        <span>{{ $message->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                {{-- Badge Penerima --}}
                <div class="text-right">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        @if($message->target_role)
                            Role: {{ str_replace('_', ' ', $message->target_role) }}
                        @else
                            Personal
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Isi Pesan --}}
        <div class="px-8 py-10">
            <div class="prose max-w-none text-gray-700 leading-relaxed text-lg">
                {!! nl2br(e($message->body)) !!}
            </div>
        </div>

        {{-- Action Footer --}}
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-500">
                    Pesan ini dikirim melalui sistem pengarsipan digital.
                </div>

                <div class="flex items-center space-x-3">
                    {{-- TOMBOL KEMBALI --}}
                    <a href="{{ route('messages.index') }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-50 transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </a>

                    {{-- FITUR KHUSUS SUPER ROLE (Hanya tampil jika super_role) --}}
                    @if(Auth::user()->role === 'super_role')
                        <div x-data="{ showConfirm: false }" class="flex items-center space-x-2">
                            <a href="{{ route('super_admin.documents_index') }}" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Buka Kelola Dokumen
                            </a>
                            
                            @if(Str::contains(strtolower($message->subject), ['hapus', 'delete', 'hilangkan']))
                                <button onclick="confirmAction()" 
                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition shadow-sm">
                                    Panduan Hapus Data
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- TOMBOL BALAS PESAN (Tampil untuk SEMUA Role) --}}
                    <a href="{{ route('messages.create', ['receiver_id' => $message->sender_id, 'subject' => 'Re: ' . $message->subject]) }}" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Balas Pesan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script SweetAlert untuk Panduan Super Role --}}
<script>
    function confirmAction() {
        Swal.fire({
            title: 'Instruksi Super Role',
            text: 'Untuk mengelola atau menghapus data, silakan cari dokumen yang dimaksud di menu Kelola Dokumen, lalu gunakan aksi yang tersedia.',
            icon: 'warning',
            confirmButtonColor: '#6366F1',
            confirmButtonText: 'Saya Mengerti'
        });
    }
</script>
@endsection