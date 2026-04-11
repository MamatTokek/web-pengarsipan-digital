<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-2 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div class="w-full sm:max-w-md mb-4 px-0"> 
            <a href="/">
                {{-- 
                    aspect-[3/1]: Mengatur kotak logo menjadi pipih (lebar 3, tinggi 1). 
                                Ubah ke [4/1] jika ingin lebih tipis lagi.
                    w-full: Memaksa lebar logo sama dengan kotak form (max-w-md).
                    object-cover: Memotong bagian atas/bawah gambar yang kosong agar teks memenuhi kotak.
                --}}
                <x-application-logo class="w-full aspect-[2.5/1] object-cover rounded-xl shadow-sm" />
            </a>
        </div>

            <div class="w-full sm:max-w-md px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>