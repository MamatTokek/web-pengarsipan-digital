@extends('layouts.admin')

@section('content')
<h1 class="text-3xl font-semibold text-gray-800">Daftar Dokumen > Balas Surat</h1>

{{-- Container Utama dengan x-data Alpine.js untuk kontrol Modal Preview & Modal Salin Nomor --}}
<div class="container mx-auto px-4 py-8" x-data="{ 
    openModal: false, 
    previewUrl: '', 
    previewTitle: '', 
    qrUrl: '',
    showCopyModal: false,
    showErrorModal: false,
    showFileErrorModal: false, 
    copiedNumber: '', 
    openWord() {
        window.open('https://word.new', '_blank');
        this.showCopyModal = false;
    }
}">
    {{-- Header Halaman --}}
    <div class="mb-8">
        
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
        
        {{-- SISI KIRI: DETAIL SURAT ASLI (REFERENSI) --}}
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

        {{-- SISI KANAN: FORM UNGGAH BALASAN --}}
        <div class="flex">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden flex flex-col w-full">
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-white font-bold flex items-center uppercase tracking-wider text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Formulir Balasan
                    </h3>
                </div>
                
                <form action="{{ route('kades.documents.store-reply', $letter->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow">
                    @csrf

                    <div class="p-6 space-y-4 flex-grow">
                    {{-- 1. NOMOR SURAT --}}
                    <div class="mb-4">
                        <label for="letter_number" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                        <input type="text" name="letter_number" id="letter_number" required readonly
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 font-mono font-bold text-indigo-700 
                                {{ $letter->category_id == 2 ? 'bg-gray-100 cursor-not-allowed' : 'bg-gray-50' }}"
                            value="{{ old('letter_number', $letter->category_id == 2 ? $letter->letter_number : '--/--/--/--/--') }}">
                        @error('letter_number')
                            <p class="text-red-500 text-xs mt-1 font-semibold flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- 2. NAMA DOKUMEN --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Surat</label>
                        <input type="text" name="name" id="name" required 
                            {{ $letter->category_id == 2 ? 'readonly' : '' }}
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 
                                {{ $letter->category_id == 2 ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'focus:ring-indigo-500 focus:border-indigo-500' }}"
                            value="{{ old('name', $letter->category_id == 2 ? $letter->name : '') }}">
                    </div>

                        {{-- 3. PERAKIT NOMOR SURAT --}}
                        @if($letter->category_id != 2)
                        <div id="section_perakit" class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                            <h3 class="text-xs font-bold text-indigo-800 mb-3 uppercase tracking-wider text-center">Perakit Nomor Surat</h3>
                            
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Kode Jenis</label>
                                    <select id="auto_kode_select" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500">
                                        <option value="">--</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->kode_surat }}">{{ $cat->kode_surat }} ({{ $cat->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase">No. Urut</label>
                                    <input type="text" id="auto_no_urut" class="w-full p-2 border bg-gray-200 border-gray-300 rounded-md text-sm font-bold shadow-sm" readonly placeholder="--">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Instansi</label>
                                    <input type="text" id="auto_instansi" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Bulan</label>
                                    <select id="auto_bulan" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm">
                                        <option value="">--</option>
                                        @php $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII']; @endphp
                                        @foreach($romans as $roman)
                                            <option value="{{ $roman }}">{{ $roman }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Tahun</label>
                                    <input type="text" id="auto_tahun" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                                </div>
                            </div>

                            {{-- TOMBOL SALIN & BUAT --}}
                            <div id="container_btn_word" class="mt-4 border-t border-indigo-200 pt-3">
                                <button type="button" id="btn_copy_word" 
                                        class="w-full flex items-center justify-center gap-2 bg-white border border-indigo-300 text-indigo-700 py-2 px-4 rounded-lg hover:bg-indigo-100 transition shadow-sm text-[10px] font-bold uppercase tracking-wider">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Salin Nomor & Buat di Word
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- UPLOAD FILE --}}
                        <div>
                            <label for="file" class="block text-sm font-bold text-gray-700 tracking-wider mb-2">Upload Surat</label>
                            <input type="file" name="file" id="file" accept=".pdf" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 grid grid-cols-2 gap-4">
                        <a href="{{ request('source') === 'detail' ? route('kades.documents.show_action', $letter->id) : route('kades.documents.index') }}" 
                           class="flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg transition transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            Batal
                        </a>
                        <button type="submit" id="btn_send_reply" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform hover:-translate-y-0.5 uppercase tracking-wider text-sm">
                            {{ $letter->category_id == 2 ? 'Simpan' : 'Kirim Balasan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW SURAT MASUK --}}
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
                                    <p class="mt-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-tight">Pindai untuk<br>Verifikasi Digital</p>
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

    {{-- MODAL NOTIFIKASI SALIN NOMOR --}}
    <div x-show="showCopyModal" class="fixed inset-0 z-[150] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCopyModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Nomor Berhasil Disalin!</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Nomor surat <span class="font-mono font-bold text-indigo-600" x-text="copiedNumber"></span> telah disalin ke clipboard.</p>
                                <p class="text-sm text-gray-500 mt-2">Klik tombol di bawah untuk membuka Word Online dan buat balasan Anda.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" @click="openWord()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Buka Word Online
                    </button>
                    <button type="button" @click="showCopyModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PERINGATAN NOMOR BELUM LENGKAP --}}
    <div x-show="showErrorModal" 
         class="fixed inset-0 z-[150] overflow-y-auto" 
         style="display: none;" 
         x-transition>
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showErrorModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Nomor Belum Lengkap!</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Silakan lengkapi seluruh field pada blok perakit nomor surat terlebih dahulu sebelum menyalin.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showErrorModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Letakkan di bagian bawah sebelum penutup x-data --}}
    <div x-show="showFileErrorModal" class="fixed inset-0 z-[150] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFileErrorModal = false"></div>
            <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Format File Tidak Valid</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Bapak Kepala Desa, mohon maaf, sistem hanya dapat menerima file balasan dalam format PDF. Silakan ganti file Anda terlebih dahulu.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showFileErrorModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Siap, Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const autoKodeSelect = document.getElementById('auto_kode_select');
    const autoUrut = document.getElementById('auto_no_urut');
    const finalInput = document.getElementById('letter_number');
    const autoInstansi = document.getElementById('auto_instansi');
    const autoBulan = document.getElementById('auto_bulan');
    const autoTahun = document.getElementById('auto_tahun');
    const btnCopyWord = document.getElementById('btn_copy_word');
    const containerBtnWord = document.getElementById('container_btn_word');
    const replyInput = document.getElementById('file');
    const sendButton = document.getElementById('btn_send_reply');

    function rakitNomor() {
        const kode = autoKodeSelect.value || '--';
        const urut = autoUrut.value || '--';
        const instansi = autoInstansi.value || '--';
        const bulan = autoBulan.value || '--';
        const tahun = autoTahun.value || '--';
        finalInput.value = `${urut}/${kode}/${instansi}/${bulan}/${tahun}`;
    }

    autoKodeSelect.addEventListener('change', function() {
        if (this.value) {
            fetch(`/get-next-number/${this.value}`)
                .then(response => response.json())
                .then(data => {
                    autoUrut.value = data.next_number;
                    rakitNomor();
                });
        } else {
            autoUrut.value = '';
            rakitNomor();
        }
    });

    btnCopyWord.addEventListener('click', function(e) {
        e.preventDefault();
        const letterNumber = finalInput.value;

        const el = document.querySelector('[x-data]');
        let alpineData = el.__x ? el.__x.$data : Alpine.$data(el);

        if (!letterNumber || letterNumber === '--/--/--/--/--' || letterNumber.includes('--')) {
            alpineData.showErrorModal = true;
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(letterNumber).then(() => {
                alpineData.copiedNumber = letterNumber;
                alpineData.showCopyModal = true;
            });
        } else {
            let textArea = document.createElement("textarea");
            textArea.value = letterNumber;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            alpineData.copiedNumber = letterNumber;
            alpineData.showCopyModal = true;
            document.body.removeChild(textArea);
        }
    });

    [autoInstansi, autoBulan, autoTahun].forEach(el => {
        el.addEventListener('input', rakitNomor);
        el.addEventListener('change', rakitNomor);
    });

    replyInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const ext = file.name.split('.').pop().toLowerCase();
            
            // Ambil instance AlpineData
            const el = document.querySelector('[x-data]');
            const alpineData = el.__x ? el.__x.$data : (window.Alpine ? Alpine.$data(el) : null);

            if (ext === 'pdf') {
                // Jika Benar (PDF)
                sendButton.disabled = false;
                sendButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                // Jika Salah
                if (alpineData) {
                    alpineData.showFileErrorModal = true;
                } else {
                    alert("Format file harus PDF!");
                }
                
                sendButton.disabled = true;
                sendButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    });
});
</script>
@endsection