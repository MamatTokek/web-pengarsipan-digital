@extends('layouts.admin')

@section('content')

{{-- Container Utama dengan x-data Alpine.js untuk kontrol Modal, Perakit, dan Notifikasi --}}
<div x-data="{ 
    showCategoryModal: false, 
    newCatName: '', 
    isSaving: false, 
    usePerakit: false,
    showFileErrorModal: false,
    successMessage: '', // Variabel penampung notifikasi sukses
    togglePerakit() {
        const finalInput = document.getElementById('letter_number');
        if (this.usePerakit) {
            finalInput.value = '--/--/--/--/--';
            // Trigger perakitan ulang jika sudah ada kategori terpilih
            if (typeof rakitNomor === 'function') rakitNomor();
        } else {
            finalInput.value = '';
        }
    }
}">

    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Kelola Arsip > Tambah Arsip</h1>

    {{-- NOTIFIKASI SUKSES (Muncul dinamis saat kategori berhasil ditambahkan) --}}
    <template x-if="successMessage">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm transition-all" role="alert">
            <span class="block sm:inline" x-text="successMessage"></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="successMessage = ''">
                <svg class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </span>
        </div>
    </template>

    <div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
        
        <form action="{{ route('archives.store') }}" method="POST" enctype="multipart/form-data" id="archiveForm">
            @csrf

            {{-- 1. NOMOR SURAT FINAL (Readonly Permanen) --}}
            <div class="mb-4">
                <label for="letter_number" class="block text-sm font-medium text-gray-700">Nomor Surat (Opsional)</label>
                <input type="text" name="letter_number" id="letter_number" readonly
                       :class="usePerakit ? 'bg-gray-50 font-mono font-bold text-indigo-700' : 'bg-gray-50 text-gray-500'"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 @error('letter_number') border-red-500 @enderror"
                       value="{{ old('letter_number') }}" placeholder="Gunakan perakit di bawah untuk mengisi...">
                @error('letter_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 2. Checkbox Kendali (Di bawah field Nomor Surat) --}}
            <div class="mb-6 flex items-center bg-gray-50 p-3 rounded-md border border-gray-200">
                <input type="checkbox" id="use_perakit_check" x-model="usePerakit" @change="togglePerakit()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="use_perakit_check" class="ml-2 text-sm font-bold text-gray-700 cursor-pointer">Gunakan Perakit Nomor Surat (Opsional)</label>
            </div>

            {{-- 3. Nama Dokumen --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Dokumen</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}">
            </div>

            {{-- 4. Kategori Utama (Di bawah Nama Dokumen) --}}
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori Dokumen</label>
                <div class="flex gap-2">
                    <select name="category_id" id="category_id_select" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 @error('category_id') border-red-500 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" @click="showCategoryModal = true" 
                            class="mt-1 p-2.5 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 text-indigo-600 shadow-sm transition-colors"
                            title="Tambah Kategori Baru">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- 5. BLOK PERAKITAN --}}
            <div id="section_perakit" x-show="usePerakit" x-transition
                 class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-lg opacity-50 pointer-events-none transition-all duration-300">
                <h3 class="text-xs font-bold text-indigo-800 mb-3 uppercase tracking-wider text-center">Panel Perakit Nomor</h3>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div id="container_kode">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Kode Jenis</label>
                        <input type="text" id="auto_kode_input" class="w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm" placeholder="--">
                        <select id="auto_kode_select" class="hidden w-full p-2 border border-gray-300 rounded-md text-sm shadow-sm">
                            <option value="">--</option>
                            @foreach($autoCategories as $cat)
                                <option value="{{ $cat->kode_surat }}">{{ $cat->kode_surat }} ({{ $cat->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">No. Urut</label>
                        <input type="text" id="auto_no_urut" class="w-full p-2 border border-gray-300 rounded-md text-sm font-bold shadow-sm" placeholder="--">
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
                <p id="helper_text" class="text-[9px] text-red-500 mt-2 font-bold">Pilih Kategori Dokumen di atas terlebih dahulu!</p>
            </div>

            {{-- 6. Deskripsi --}}
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" id="description" rows="3" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            </div>
            
            {{-- 7. Upload File --}}
            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700">Upload File Arsip</label>
                <input type="file" name="file" id="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('file') border-red-500 @enderror">
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('archives.index') }}" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" id="btn_submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 transform active:scale-95">
                    Tambah Arsip
                </button>
            </div>
        </form>
    </div>

    {{-- MODAL TAMBAH KATEGORI --}}
    <div x-show="showCategoryModal" class="fixed inset-0 z-[110] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCategoryModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Tambah Kategori Baru</h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                                <input type="text" x-model="newCatName" @keydown.enter="saveCategory()" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Dokumen Desa">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" @click="saveCategory()" :disabled="isSaving" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isSaving">Simpan</span>
                        <span x-show="isSaving">Menyimpan...</span>
                    </button>
                    <button type="button" @click="showCategoryModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PERINGATAN FORMAT FILE SALAH --}}
    <div x-show="showFileErrorModal" class="fixed inset-0 z-[150] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFileErrorModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center sm:text-left">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Format File Tidak Sesuai!</h3>
                            <p class="text-sm text-gray-500 mt-2">Untuk pengarsipan, gunakan format PDF, Word (DOC/DOCX), atau Gambar (JPG, JPEG, PNG).</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showFileErrorModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-orange-700 sm:ml-3 sm:w-auto sm:text-sm">Siap, Mengerti</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function rakitNomor() {
    let alpineData = document.querySelector('[x-data]').__x ? document.querySelector('[x-data]').__x.$data : Alpine.$data(document.querySelector('[x-data]'));
    if (!alpineData.usePerakit) return;

    const categorySelect = document.getElementById('category_id_select');
    const autoKodeInput = document.getElementById('auto_kode_input');
    const autoKodeSelect = document.getElementById('auto_kode_select');
    const autoUrut = document.getElementById('auto_no_urut');
    const autoInstansi = document.getElementById('auto_instansi');
    const autoBulan = document.getElementById('auto_bulan');
    const autoTahun = document.getElementById('auto_tahun');
    const finalInput = document.getElementById('letter_number');

    const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
    const kodeVal = selectedText.includes('keluar') ? autoKodeSelect.value : autoKodeInput.value;

    const kode = kodeVal || '--';
    const urut = autoUrut.value || '--';
    const instansi = autoInstansi.value || '--';
    const bulan = autoBulan.value || '--';
    const tahun = autoTahun.value || '--';
    
    finalInput.value = `${kode}/${urut}/${instansi}/${bulan}/${tahun}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id_select');
    const sectionPerakit = document.getElementById('section_perakit');
    const autoKodeInput = document.getElementById('auto_kode_input');
    const autoKodeSelect = document.getElementById('auto_kode_select');
    const autoUrut = document.getElementById('auto_no_urut');
    const autoInstansi = document.getElementById('auto_instansi');
    const autoBulan = document.getElementById('auto_bulan');
    const autoTahun = document.getElementById('auto_tahun');
    const helperText = document.getElementById('helper_text');
    const fileInput = document.getElementById('file');
    const submitBtn = document.getElementById('btn_submit');

    categorySelect.addEventListener('change', function() {
        const selectedText = this.options[this.selectedIndex].text.toLowerCase();
        
        autoKodeInput.value = '';
        autoKodeSelect.value = '';
        autoUrut.value = '';
        autoInstansi.value = '';
        autoBulan.value = '';
        autoTahun.value = '';
        
        if (this.value === "") {
            sectionPerakit.classList.add('opacity-50', 'pointer-events-none');
            helperText.innerText = "Pilih Kategori Dokumen di atas terlebih dahulu!";
            helperText.classList.replace('text-indigo-400', 'text-red-500');
            rakitNomor();
            return;
        }

        sectionPerakit.classList.remove('opacity-50', 'pointer-events-none');
        helperText.classList.replace('text-red-500', 'text-indigo-400');

        if (selectedText.includes('masuk')) {
            autoUrut.readOnly = false;
            autoUrut.classList.remove('bg-gray-200');
            autoKodeInput.classList.remove('hidden');
            autoKodeSelect.classList.add('hidden');
            helperText.innerText = "Mode Surat Masuk: Isi manual semua field";
        } else if (selectedText.includes('keluar')) {
            autoUrut.readOnly = true;
            autoUrut.classList.add('bg-gray-200');
            autoKodeInput.classList.add('hidden');
            autoKodeSelect.classList.remove('hidden');
            helperText.innerText = "Mode Surat Keluar: No Urut otomatis mengikuti Kode Jenis";
        }
        rakitNomor();
    });

    autoKodeSelect.addEventListener('change', function() {
        const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
        if (selectedText.includes('keluar') && this.value) {
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

    [autoKodeInput, autoUrut, autoInstansi, autoBulan, autoTahun].forEach(el => {
        el.addEventListener('input', rakitNomor);
        el.addEventListener('change', rakitNomor);
    });

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const ext = file.name.split('.').pop().toLowerCase();
            
            // Daftar format yang diizinkan untuk arsip
            const allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            const el = document.querySelector('[x-data]');
            const alpineData = el.__x ? el.__x.$data : (window.Alpine ? Alpine.$data(el) : null);

            if (allowed.includes(ext)) {
                // Jika format benar
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('hover:bg-indigo-700', 'active:scale-95');
            } else {
                // Jika format salah
                if (alpineData) {
                    alpineData.showFileErrorModal = true; 
                } else {
                    alert("Format file tidak didukung! Gunakan PDF, Word, atau Gambar.");
                }
                
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-indigo-700', 'active:scale-95');
            }
        }
    });
});

function saveCategory() {
    const el = document.querySelector('[x-data]');
    let alpineData = el.__x ? el.__x.$data : Alpine.$data(el); // Penanganan versi Alpine
    const name = alpineData.newCatName;

    if (!name || name.trim() === "") {
        alert('Nama kategori tidak boleh kosong!');
        return;
    }

    alpineData.isSaving = true;

    fetch("{{ route('categories.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({ name: name, type: 'archive' })
    })
    .then(response => {
        if (response.status === 403) throw new Error('Anda tidak memiliki izin (Hanya Super Role)');
        if (!response.ok) throw new Error('Gagal menyimpan kategori');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const select = document.getElementById('category_id_select');
            const option = new Option(data.category.name, data.category.id);
            select.add(option);
            select.value = data.category.id;

            alpineData.showCategoryModal = false;
            alpineData.newCatName = '';
            
            // TAMPILKAN NOTIFIKASI SUKSES PADA HALAMAN
            alpineData.successMessage = 'Kategori ' + data.category.name + ' berhasil ditambahkan!';

            select.dispatchEvent(new Event('change'));
        }
    })
    .catch(error => { alert('Terjadi kesalahan: ' + error.message); })
    .finally(() => { alpineData.isSaving = false; });
}
</script>

@endsection