<?php
// Wajib: Agar bisa menggunakan Auth::user()->role
use Illuminate\Support\Facades\Auth;
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Aplikasi Arsip Digital Desa Baturan</title>
    {{-- Memuat aset frontend yang sudah dikompilasi oleh NPM/Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
</head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body class="bg-gray-100">
    <div class="flex h-screen">
        {{-- START SIDEBAR --}}
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-4 text-xl font-bold border-b border-gray-700">
                {{-- Tautan Dashboard di Judul --}}
                <a href="{{ route('dashboard') }}" class="text-white hover:text-gray-300 transition duration-150">
                    Arsip Desa Baturan
                </a>
            </div>
            
            <nav class="p-4">
                <ul>
                    {{-- 1. DASHBOARD (Wajib ada untuk semua role) --}}
                    <li class="mb-2">
                        @php
                            $dashboardRoute = Auth::check() && Auth::user()->role === 'kepala_desa' ? 'kades.dashboard' : 'dashboard';
                            $isActive = request()->routeIs('dashboard') || request()->routeIs('kades.dashboard');
                        @endphp
                        
                        <a href="{{ route($dashboardRoute) }}" 
                        class="flex items-center p-2 rounded-lg 
                        {{ $isActive ? 'bg-gray-700' : 'hover:bg-gray-700' }}"> 
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l-2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1z"></path></svg>
                            Dashboard
                        </a>
                    </li>
                    
                    {{-- 2. MENU KHUSUS ADMIN --}}
                    @if (Auth::check() && Auth::user()->role === 'admin')
                        <li class="mb-2">
                            <a href="{{ route('letters.index') }}" 
                            class="flex items-center p-2 rounded-lg hover:bg-gray-700 
                            {{ request()->routeIs('letters.*') ? 'bg-gray-700' : '' }}"> 
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Kelola Surat
                            </a>
                        </li>
                        
                        <li class="mb-2">
                            <a href="{{ route('archives.index') }}" 
                            class="flex items-center p-2 rounded-lg hover:bg-gray-700
                            {{ request()->routeIs('archives.*') ? 'bg-gray-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                Kelola Arsip
                            </a>
                        </li>
                    @endif

                    {{-- 3. MENU KHUSUS SUPER ROLE --}}
                    @if (Auth::check() && Auth::user()->role === 'super_role')
                        {{-- Menu Kelola Dokumen (Gabungan Surat & Arsip) --}}
                        <li class="mb-2">
                            <a href="{{ route('kades.documents.index') }}" 
                                class="flex items-center p-2 rounded-lg hover:bg-gray-700
                                {{ request()->routeIs('kades.documents.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Kelola Dokumen
                            </a>
                        </li>
                    @endif
                    
                    {{-- 4. MENU KHUSUS KEPALA DESA --}}
                    @if (Auth::check() && Auth::user()->role === 'kepala_desa')
                        <li class="mb-2">
                            <a href="{{ route('kades.documents.index') }}" 
                                class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-700
                                {{ request()->routeIs('kades.documents.*') ? 'bg-gray-700' : '' }}">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                    Daftar Dokumen
                                </div>

                                @php
                                    $notifCount = \App\Models\Letter::where('need_action', true)
                                                                    ->where('action_status', 'pending')
                                                                    ->count();
                                @endphp
                                @if($notifCount > 0)
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                        {{ $notifCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('kades.activities.index') }}" 
                                class="flex items-center p-2 rounded-lg hover:bg-gray-700
                                {{ request()->routeIs('kades.activities.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Aktivitas
                            </a>
                        </li>
                    @endif
                    
                    {{-- 5. MENU PESAN --}}
                    <li class="mb-2">
                        <a href="{{ route('messages.index') }}" 
                            class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-700
                            {{ request()->routeIs('messages.*') ? 'bg-gray-700' : '' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                                Pesan
                            </div>

                            @php
                                // Hitung pesan yang ditujukan untuk user/role ini,
                                // TAPI belum ada di tabel 'message_reads' (pivot) untuk user ini.
                                $unreadMessagesCount = \App\Models\Message::where(function($q) {
                                    $q->where('receiver_id', Auth::id())
                                    ->orWhere('target_role', Auth::user()->role)
                                    ->orWhere('target_role', 'all'); // Menghitung pesan untuk semua role
                                })
                                ->whereDoesntHave('readers', function($q) {
                                    $q->where('user_id', Auth::id());
                                })
                                ->count();
                            @endphp

                            @if($unreadMessagesCount > 0)
                                <span class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                    {{ $unreadMessagesCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-6 overflow-y-auto">
            <header class="flex justify-end items-center mb-6">
                <div class="relative">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">
                            {{ Auth::user()->name ?? 'User' }} 
                            <span class="text-[10px] bg-gray-200 px-1.5 py-0.5 rounded-md uppercase font-bold ml-1">
                                {{ str_replace('_', ' ', Auth::user()->role) }}
                            </span>
                        </span>
                        <button id="profileDropdownBtn" class="p-2 bg-white rounded-full text-gray-500 hover:text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 9a2 2 0 01-2 2H5a2 2 0 01-2-2v-1.5a2.5 2.5 0 012.5-2.5h13a2.5 2.5 0 012.5 2.5V19z"></path></svg>
                        </button>
                    </div>

                    <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50 hidden">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('profileDropdownBtn');
            const menu = document.getElementById('profileDropdownMenu');
            if(btn) {
                btn.addEventListener('click', function (event) {
                    menu.classList.toggle('hidden');
                    event.stopPropagation();
                });
            }
            window.addEventListener('click', function (event) {
                if (menu && !btn.contains(event.target) && !menu.contains(event.target)) {
                    menu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>