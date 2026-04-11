@extends('layouts.admin')

@section('content')
{{-- Judul Halaman Sesuai Style Dashboard --}}
<h1 class="text-3xl font-semibold text-gray-800 mb-6">Aktivitas</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    {{-- Header Tabel Sesuai Style Dashboard --}}
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Terkini</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    {{-- Judul Kolom: font-medium text-xs text-gray-500 uppercase --}}
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activities as $activity)
                <tr class="hover:bg-gray-50 transition-colors">
                    {{-- Kolom 1: Pengguna --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white font-bold
                                {{ $activity->user->role === 'admin' ? 'bg-indigo-500' : 'bg-emerald-500' }}">
                                {{ substr($activity->user->name, 0, 1) }}
                            </div>
                            <div class="ml-3 text-left">
                                {{-- font-bold text-sm text-gray-900 disamakan dengan Daftar Dokumen --}}
                                <p class="text-sm font-bold text-gray-900 leading-none mb-1">{{ $activity->user->name }}</p>
                                <p class="text-[10px] uppercase text-gray-400 font-semibold tracking-wide">{{ str_replace('_', ' ', $activity->user->role) }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom 2: Aktivitas --}}
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center gap-2">
                            {{-- 1. Keterangan Aktivitas dengan huruf kapital di awal --}}
                            <div class="text-sm text-center text-gray-600">
                                {{ ucfirst($activity->description) }}
                                <span class="font-bold text-indigo-600">"{{ $activity->subject_name }}"</span>
                            </div>

                            {{-- 2. Badge Jenis (Sekarang di posisi bawah) --}}
                            @if($activity->type === 'Surat')
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-blue-100 text-blue-700 uppercase tracking-wider w-fit">Surat</span>
                            @else
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-purple-100 text-purple-700 uppercase tracking-wider w-fit">Arsip</span>
                            @endif
                        </div>
                    </td>

                    {{-- Kolom 3: Tanggal --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        {{-- text-sm text-gray-500 disamakan dengan kolom tanggal di Daftar Dokumen --}}
                        <div class="text-sm text-gray-500">
                            {{ $activity->created_at->translatedFormat('d/m/Y') }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $activity->created_at->diffForHumans() }} ({{ $activity->created_at->format('H:i') }})
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                        Belum ada aktivitas terekam dalam sistem.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Paginasi --}}
    <div class="mt-4">
        {{ $activities->appends(request()->query())->links() }}
    </div>
</div>
@endsection