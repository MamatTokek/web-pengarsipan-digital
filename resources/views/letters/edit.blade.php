@extends('layouts.admin')

@section('content')

<div x-data="{ 
    showCopyModal: false,
    showErrorModal: false,
    showFileErrorModal: false, 
    copiedNumber: '', 
    openWord() {
        window.open('https://word.new', '_blank');
        this.showCopyModal = false;
    }
}">

<h1 class="text-3xl font-semibold text-gray-800 mb-6">Kelola Surat > Edit Surat</h1>

<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    
    <form action="{{ route('letters.update', $letter) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- 1. NOMOR SURAT FINAL (Readonly) --}}
        <div class="mb-4">
            <label for="letter_number" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
            <input type="text" name="letter_number" id="letter_number" required readonly
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-50 font-mono font-bold text-indigo-700
                         focus:ring-indigo-500 focus:border-indigo-500 @error('letter_number') border-red-500 @enderror"
                   value="{{ old('letter_number', $letter->letter_number) }}">
            @error('letter_number')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 2. Nama Surat --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Surat</label>
            <input type="text" name="name" id="name" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 
                         focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                   value="{{ old('name', $letter->name) }}">
        </div>

        {{-- 3. Kategori --}}
        <div class="mb-4">
            <label for="category_id" class="block text-sm font-medium text-gray-700">Pilih Kategori Utama</label>
            <select name="category_id" id="category_id" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 
                           focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $letter->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 4. BLOK PERAKITAN NOMOR SURAT --}}
        <div id="section_perakit" class="mb-4 p-4 bg-indigo-50 border border-indigo-100 rounded-lg opacity-50 pointer-events-none transition-all duration-300">
            <h3 class="text-xs font-bold text-indigo-800 mb-3 uppercase tracking-wider text-center">Perakit Nomor Surat</h3>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                {{-- Kode Jenis --}}
                <div id="container_kode">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Kode Jenis</label>
                    {{-- Input teks untuk Mode Surat Masuk --}}
                    <input type="text" id="auto_kode_input" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                    
                    {{-- Dropdown untuk Mode Surat Keluar --}}
                    <select id="auto_kode_select" class="hidden w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm">
                        <option value="">--</option>
                        @foreach($autoCategories as $cat)
                            <option value="{{ $cat->kode_surat }}">{{ $cat->kode_surat }} ({{ $cat->name }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Nomor Urut --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">No. Urut</label>
                    <input type="text" id="auto_no_urut" class="w-full p-2 border border-gray-300 rounded-md text-sm font-bold shadow-sm" placeholder="--">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                {{-- Instansi --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Instansi</label>
                    <input type="text" id="auto_instansi" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                </div>
                {{-- Bulan --}}
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
                {{-- Tahun --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Tahun</label>
                    <input type="text" id="auto_tahun" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                </div>
            </div>
            <p id="helper_text" class="text-[9px] text-indigo-500 mt-2 font-bold">Mode Edit: Sesuaikan perakit untuk mengubah nomor.</p>

            {{-- TOMBOL SALIN & BUAT (Sama seperti halaman Create) --}}
            <div id="container_btn_word" class="mt-4 border-t border-indigo-200 pt-3 {{ str_contains(strtolower($letter->category->name ?? ''), 'keluar') ? '' : 'hidden' }}">
                <button type="button" id="btn_copy_word" 
                        class="w-full flex items-center justify-center gap-2 bg-white border border-indigo-300 text-indigo-700 py-2 px-4 rounded-lg hover:bg-indigo-100 transition duration-150 shadow-sm text-[10px] font-bold uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Salin Nomor & Buat di Word
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label for="file" class="block text-sm font-medium text-gray-700">Upload Surat</label>
            <input type="file" name="file" id="file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700">
            <p class="mt-2 text-xs text-gray-500 text-truncate">File saat ini: <span class="font-medium text-indigo-700">{{ $letter->original_file_name }}</span>
                <br>(Kosongkan jika tidak ingin mengganti file)
            </p>
        </div>

        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Tindakan Kepala Desa</h3>
            <div class="flex items-center">
                <input type="checkbox" name="need_action" id="need_action" value="1" 
                    {{ old('need_action', $letter->need_action) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="need_action" class="ml-2 text-sm text-gray-600">Butuh tindakan</label>
            </div>
            <div id="admin_note_container" class="mt-3 {{ old('need_action', $letter->need_action) ? '' : 'hidden' }}">
                <textarea name="admin_note" id="admin_note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Catatan...">{{ old('admin_note', $letter->admin_note) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ request('source') === 'detail' ? route('letters.show_detail', $letter->id) : route('letters.index') }}" 
               class="py-2 px-6 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition duration-150">
                Batal
            </a>
            <button type="submit" id="btn_submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 transform active:scale-95">
                Simpan Perubahan
            </button>
        </div>
    </form>

    {{-- MODAL NOTIFIKASI SALIN NOMOR --}}
    <div x-show="showCopyModal" class="fixed inset-0 z-[150] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCopyModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Nomor Berhasil Disalin!</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Nomor surat <span class="font-mono font-bold text-indigo-600" x-text="copiedNumber"></span> telah disalin ke clipboard.</p>
                                <p class="text-sm text-gray-500 mt-2">Klik tombol di bawah untuk membuka Word Online dan tempelkan (Ctrl+V) nomor tersebut.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" @click="openWord()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Buka Word Online</button>
                    <button type="button" @click="showCopyModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PERINGATAN NOMOR BELUM LENGKAP --}}
    <div x-show="showErrorModal" class="fixed inset-0 z-[150] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showErrorModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
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
                    <button type="button" @click="showErrorModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PERINGATAN FORMAT FILE SALAH --}}
    <div x-show="showFileErrorModal" 
        class="fixed inset-0 z-[150] overflow-y-auto" 
        style="display: none;" 
        x-transition>
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFileErrorModal = false"></div>
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
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Format File Salah!</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Sistem hanya mendukung format PDF. Silahkan ganti file Anda.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showFileErrorModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const sectionPerakit = document.getElementById('section_perakit');
    const finalInput = document.getElementById('letter_number');
    const autoKodeInput = document.getElementById('auto_kode_input');
    const autoKodeSelect = document.getElementById('auto_kode_select');
    const autoUrut = document.getElementById('auto_no_urut');
    const autoInstansi = document.getElementById('auto_instansi');
    const autoBulan = document.getElementById('auto_bulan');
    const autoTahun = document.getElementById('auto_tahun');
    const helperText = document.getElementById('helper_text');
    const btnCopyWord = document.getElementById('btn_copy_word'); // Tambahkan ini
    const containerBtnWord = document.getElementById('container_btn_word');
    const fileInput = document.getElementById('file');
    const submitBtn = document.getElementById('btn_submit');

    function getCurrentKodeValue() {
        const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
        return selectedText.includes('keluar') ? autoKodeSelect.value : autoKodeInput.value;
    }

    function rakitNomor() {
        const selectedText = categorySelect.options[categorySelect.selectedIndex] ? 
                            categorySelect.options[categorySelect.selectedIndex].text.toLowerCase() : '';
        
        const kodeVal = getCurrentKodeValue();

        // MODIFIKASI: Hapus atau beri komentar pada bagian pengecekan surat keluar yang mengosongkan input
        // Agar surat keluar juga menampilkan format --/--/--/--/-- meskipun kode kosong
        /* if (selectedText.includes('keluar') && !kodeVal) {
            finalInput.value = '';
            return;
        } 
        */

        const kode = kodeVal || '--';
        const urut = autoUrut.value || '--';
        const instansi = autoInstansi.value || '--';
        const bulan = autoBulan.value || '--';
        const tahun = autoTahun.value || '--';
        
        finalInput.value = `${urut}/${kode}/${instansi}/${bulan}/${tahun}`;
    }

    function updateLayout() {
        const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
        
        if (selectedText.includes('masuk')) {
            autoUrut.readOnly = false;
            autoUrut.classList.remove('bg-gray-200');
            autoKodeInput.classList.remove('hidden');
            autoKodeSelect.classList.add('hidden');
            containerBtnWord.classList.add('hidden');
            // Update teks bantuan untuk surat masuk
            helperText.innerText = "Mode Surat Masuk: Isi manual semua field";
        } else if (selectedText.includes('keluar')) {
            autoUrut.readOnly = true;
            autoUrut.classList.add('bg-gray-200');
            autoKodeInput.classList.add('hidden');
            autoKodeSelect.classList.remove('hidden');
            containerBtnWord.classList.remove('hidden');
            // Update teks bantuan untuk surat keluar
            helperText.innerText = "Mode Surat Keluar: No Urut otomatis mengikuti Kode Jenis";
        }
    }

    // 1. Parsing Nomor Surat Lama saat halaman dimuat (Logika Edit)
    const existingNumber = "{{ $letter->letter_number }}";
    const parts = existingNumber.split('/');
    if (parts.length === 5) {
        autoUrut.value = parts[0];       // Indeks 0 = No. Urut
        autoKodeInput.value = parts[1];  // Indeks 1 = Kode Jenis (Untuk Surat Masuk)
        autoKodeSelect.value = parts[1]; // Indeks 1 = Kode Jenis (Untuk Surat Keluar)
        autoInstansi.value = parts[2];   // Indeks 2 = Instansi
        autoBulan.value = parts[3];      // Indeks 3 = Bulan
        autoTahun.value = parts[4];
    }

    // Jalankan inisialisasi awal
    if (categorySelect.value !== "") {
        sectionPerakit.classList.remove('opacity-50', 'pointer-events-none');
        updateLayout();
        rakitNomor();
    }

    // 2. Event Listeners
    categorySelect.addEventListener('change', function() {
        const selectedText = this.options[this.selectedIndex].text.toLowerCase();
        
        // Reset state perakit saat ganti kategori (Sesuai logika Create)
        autoKodeInput.value = '';
        autoKodeSelect.value = '';
        autoUrut.value = '';
        autoInstansi.value = '';
        autoBulan.value = '';
        autoTahun.value = '';
        
        if (this.value === "") {
            sectionPerakit.classList.add('opacity-50', 'pointer-events-none');
            // Update teks bantuan saat kategori kosong (Sama dengan Create)
            helperText.innerText = "Wajib pilih kategori di atas terlebih dahulu!";
            helperText.classList.replace('text-indigo-500', 'text-red-500');
            finalInput.value = '--/--/--/--/--';
            return;
        }

        sectionPerakit.classList.remove('opacity-50', 'pointer-events-none');
        helperText.classList.replace('text-red-500', 'text-indigo-500');
        updateLayout();
        rakitNomor();
    });

    autoKodeSelect.addEventListener('change', function() {
        const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
        if (selectedText.includes('keluar')) {
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

    [autoKodeInput, autoUrut, autoInstansi, autoBulan, autoTahun].forEach(el => {
        el.addEventListener('input', rakitNomor);
        el.addEventListener('change', rakitNomor);
    });

    document.getElementById('need_action').addEventListener('change', function() {
        document.getElementById('admin_note_container').classList.toggle('hidden', !this.checked);
    });

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const fileName = file.name;
            const ext = fileName.split('.').pop().toLowerCase();
            const allowed = ['pdf'];

            // Cara akses data Alpine yang mendukung versi 2 dan 3
            const el = document.querySelector('[x-data]');
            const alpineData = el.__x ? el.__x.$data : (window.Alpine ? Alpine.$data(el) : null);

            if (allowed.includes(ext)) {
                // Jika benar
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('hover:bg-indigo-700', 'active:scale-95');
            } else {
                // Jika salah
                if (alpineData) {
                    alpineData.showFileErrorModal = true; 
                } else {
                    alert("Format file salah! Gunakan PDF");
                }
                
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-indigo-700', 'active:scale-95');
            }
        }
    });
});
</script>

@endsection