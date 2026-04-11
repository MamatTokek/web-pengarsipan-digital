<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-indigo-600 p-6 text-center">
                <div class="inline-flex p-3 rounded-full bg-white mb-4 shadow-sm">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-white text-xl font-bold">Dokumen Terverifikasi</h1>
                <p class="text-indigo-100 text-sm">Sistem Pengarsipan Digital Kantor Desa</p>
            </div>
            
            {{-- Content --}}
            <div class="p-6 space-y-5">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-xs text-gray-400 uppercase font-bold">Jenis Dokumen</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $type }}</span>
                </div>

                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Nama Dokumen</label>
                    <p class="text-gray-800 font-semibold text-lg">{{ $doc->name }}</p>
                </div>

                {{-- MODIFIKASI: Logika Tampilan Nomor Surat Berdasarkan Tipe Dokumen --}}
                <div class="p-3 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-lg">
                    <label class="text-[10px] text-indigo-500 uppercase font-bold tracking-widest">Nomor Surat Resmi</label>
                    <p class="text-indigo-900 font-mono font-bold">
                        @if($type == 'Arsip' && !$doc->letter_number)
                            <span class="text-gray-500 font-sans text-sm">Tanpa Nomor Resmi</span>
                        @else
                            {{ $doc->letter_number ?? 'Tidak Terdata' }}
                        @endif
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Kategori</label>
                        <p class="text-gray-800 font-medium">{{ $doc->category->name ?? 'Umum' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Tanggal Upload</label>
                        <p class="text-gray-800 font-medium">{{ $doc->uploaded_at->format('d M Y') }}</p>
                    </div>
                </div>

                @if($type == 'Arsip' && $doc->description)
                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Deskripsi</label>
                    <p class="text-gray-600 text-sm">{{ $doc->description }}</p>
                </div>
                @endif

                <div class="pt-4 border-t border-gray-100 text-center">
                    <div class="inline-block px-4 py-1.5 bg-green-50 border border-green-200 rounded-full">
                        <span class="text-xs font-bold text-green-700 uppercase">Status: Dokumen Asli</span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-4 leading-relaxed">
                        Data ini ditarik langsung dari database resmi sistem pengarsipan desa. 
                        Pastikan data di atas sesuai dengan dokumen fisik yang Anda pegang.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>