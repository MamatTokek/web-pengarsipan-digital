<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\KadesController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController; 
use App\Http\Controllers\MessageController; 
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Auth\ManualResetPasswordController;

/*
|--------------------------------------------------------------------------
| 1. ROUTE UTAMA & AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        if ($role === 'kepala_desa') {
            return redirect()->route('kades.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php'; 


/*
|--------------------------------------------------------------------------
| 2. ROUTE GABUNGAN (Kepala Desa & Super Role)
|--------------------------------------------------------------------------
*/
// Rute ini dipisahkan agar Super Role bisa mengakses menu "Kelola Dokumen" 
// yang isinya sama dengan "Daftar Dokumen" Kepala Desa namun dengan View berbeda.
Route::middleware(['auth', 'verified', RoleMiddleware::class . ':kepala_desa,super_role'])->group(function () {
    
    // Daftar Dokumen Gabungan (Surat + Arsip)
    Route::get('/kades/documents', [DocumentController::class, 'index'])->name('kades.documents.index');
    
    // Fitur Log Aktivitas (Hanya Kades, tapi Super Role bisa diizinkan jika perlu)
    Route::get('/kades/activities', [ActivityController::class, 'index'])->name('kades.activities.index');
});


/*
|--------------------------------------------------------------------------
| 3. ROUTE KHUSUS KEPALA DESA (Hanya Role: kepala_desa)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', RoleMiddleware::class . ':kepala_desa'])->group(function () {
    
    Route::get('/kades/dashboard', [KadesController::class, 'index'])->name('kades.dashboard');
    Route::get('/kades/documents/{letter}/action', [DocumentController::class, 'showAction'])->name('kades.documents.show_action');
    Route::get('/kades/documents/{letter}/reply', [DocumentController::class, 'reply'])->name('kades.documents.reply');
    Route::post('/kades/documents/{letter}/reply', [DocumentController::class, 'storeReply'])->name('kades.documents.store-reply');
    Route::get('/kades/documents/{letter}/edit-reply', [DocumentController::class, 'editReply'])->name('kades.documents.edit_reply');
    Route::put('/kades/documents/{letter}/update-reply', [DocumentController::class, 'updateReply'])->name('kades.documents.update_reply');
});


/*
|--------------------------------------------------------------------------
| 4. ROUTE KHUSUS SUPER ROLE (Hanya Role: super_role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', RoleMiddleware::class . ':super_role'])->group(function () {
    
    Route::get('/super-admin/kelola-dokumen', [DocumentController::class, 'index'])->name('super_admin.documents_index');

    Route::delete('/super-admin/documents/{id}/{type}', [DocumentController::class, 'destroy'])->name('super_admin.documents_destroy');

    // Hak akses eksklusif untuk hapus permanen
    Route::delete('/letters/{letter}/force-delete', [LetterController::class, 'destroy'])->name('letters.force_delete');
    Route::delete('/archives/{archive}/force-delete', [ArchiveController::class, 'destroy'])->name('archives.force_delete');

});


/*
|--------------------------------------------------------------------------
| 5. ROUTE KHUSUS ADMIN (Hanya Role: admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', RoleMiddleware::class . ':admin'])->group(function () {
    
    // Admin tidak memiliki akses 'destroy' (hapus)
    Route::resource('letters', LetterController::class)->except(['show', 'destroy']);
    Route::resource('archives', ArchiveController::class)->except(['show', 'destroy']);
    Route::get('letters/{letter}/detail', [LetterController::class, 'showDetail'])->name('letters.show_detail');
});


/*
|--------------------------------------------------------------------------
| 6. ROUTE DASHBOARD & PESAN (Akses Admin & Super Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', RoleMiddleware::class . ':admin,super_role'])->group(function () {
    // Menampilkan dashboard yang sesuai melalui DashboardController@index
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengelolaan Kategori
    Route::resource('categories', CategoryController::class);
    
});


/*
|--------------------------------------------------------------------------
| 7. ROUTE UMUM (Semua Role Setelah Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    Route::resource('messages', MessageController::class);
    Route::get('letters/download/{letter}', [LetterController::class, 'download'])->name('letters.download');
    Route::get('archives/download/{archive}', [ArchiveController::class, 'download'])->name('archives.download');
    Route::get('letters/{letter}', [LetterController::class, 'show'])->name('letters.show');
    Route::get('archives/{archive}', [ArchiveController::class, 'show'])->name('archives.show'); 
    Route::get('/get-next-number/{kode}', [LetterController::class, 'getNextNumber'])->name('letters.get_next_number');
});


/*
|--------------------------------------------------------------------------
| 8. VERIFIKASI PUBLIK
|--------------------------------------------------------------------------
*/
Route::get('/verifikasi/surat/{uuid}', [LetterController::class, 'publicVerify'])->name('public.verify.letter');
Route::get('/verifikasi/arsip/{uuid}', [ArchiveController::class, 'publicVerify'])->name('public.verify.archive');

// Tambahkan rute manual Anda
Route::get('forgot-password', [ManualResetPasswordController::class, 'create'])
    ->name('password.request');

Route::post('forgot-password', [ManualResetPasswordController::class, 'store'])
    ->name('password.manual.update');