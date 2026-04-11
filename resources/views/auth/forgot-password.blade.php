<x-guest-layout>
    {{-- Tambahkan CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="mb-6 text-sm text-gray-600 leading-relaxed">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will update your password directly without a reset link.') }}
    </div>

    {{-- Form dengan ID untuk diproses via AJAX --}}
    <form id="resetPasswordForm" method="POST" action="{{ route('password.manual.update') }}" class="space-y-5">
        @csrf

        {{-- Input Email --}}
        <div>
            <x-input-label for="email" :value="__('Email Akun')" class="font-semibold text-gray-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                          type="email" name="email" :value="old('email')" required autofocus />
            <span id="error-email" class="text-sm text-red-600 mt-2 hidden"></span>
        </div>

        {{-- Input Password Baru --}}
        <div>
            <x-input-label for="password" :value="__('Password Baru')" class="font-semibold text-gray-700" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                          type="password" name="password" required />
            <span id="error-password" class="text-sm text-red-600 mt-2 hidden"></span>
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" class="font-semibold text-gray-700" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                          type="password" name="password_confirmation" required />
        </div>

        <div class="flex items-center justify-end mt-6 gap-4">
            {{-- Tombol Batal --}}
            <a href="{{ route('login') }}" 
               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-widest rounded-lg shadow-sm transition duration-150 ease-in-out border border-gray-300">
                {{ __('Batal') }}
            </a>

            {{-- Tombol Reset --}}
            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow-md transition duration-150 ease-in-out">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>

    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah form reload halaman otomatis

            const form = this;
            const formData = new FormData(form);
            const btnSubmit = document.getElementById('btnSubmit');

            // 1. Tampilkan Modal Loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memperbarui password Anda',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // 2. Kirim data via AJAX (Fetch API)
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok || response.status === 302 || response.status === 200) {
                    // 3. Jika Berhasil, Tampilkan Modal Sukses
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Password Anda telah berhasil diperbarui.',
                        icon: 'success',
                        confirmButtonText: 'Okee',
                        confirmButtonColor: '#1e293b',
                        borderRadius: '15px'
                    }).then((result) => {
                        // 4. Redirect ke Login setelah user menekan tombol di modal
                        window.location.href = "{{ route('login') }}";
                    });
                } else {
                    return response.json().then(data => { throw data; });
                }
            })
            .catch(error => {
                // Jika terjadi error validasi (email tidak ditemukan, dll)
                Swal.close();
                
                if (error.errors) {
                    if (error.errors.email) alert(error.errors.email[0]);
                    if (error.errors.password) alert(error.errors.password[0]);
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        });
    </script>
</x-guest-layout>