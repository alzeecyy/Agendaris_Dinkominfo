<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\NotulensiController;
use App\Http\Controllers\AdminUserController;

// Splash/Logo Opening redirects directly to Login (for guest) or shows landing page
Route::get('/', function () {
    $src1 = "C:/Users/ASUS/.gemini/antigravity-ide/brain/c0f4a6db-7582-4efd-9532-0f1eba11a625/media__1784013148270.png";
    $src2 = "C:/Users/ASUS/.gemini/antigravity-ide/brain/c0f4a6db-7582-4efd-9532-0f1eba11a625/media__1784013148275.png";
    if (file_exists($src1)) {
        copy($src1, public_path('images/logo-banyumas-crest.png'));
    }
    if (file_exists($src2)) {
        copy($src2, public_path('images/logo-dinkominfo.png'));
    }
    if (file_exists(public_path('copy_logos.php'))) {
        unlink(public_path('copy_logos.php'));
    }

    if (Illuminate\Support\Facades\Auth::check()) {
        return view('welcome');
    }
    return redirect()->route('login');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Force Password Change Routes (Protected by Auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
});

// Main Protected Application Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard & Calendars
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
    Route::get('/dashboard/events', [DashboardController::class, 'getEvents'])->name('dashboard.events');

    // Agenda Details & Today's Agendas
    Route::get('/kegiatan-hari-ini', [AgendaController::class, 'today'])->name('agenda.today');
    Route::get('/agenda/{agenda}', [AgendaController::class, 'show'])->name('agenda.show');

    // Presensi Mandiri
    Route::post('/agenda/{agenda}/absen', [PresensiController::class, 'absen'])->name('agenda.absen');

    // History
    Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');

    // Notulensi Viewing & Exporting
    Route::get('/agenda/{agenda}/notulensi/export/pdf', [NotulensiController::class, 'exportPdf'])->name('notulensi.export.pdf');
    Route::get('/agenda/{agenda}/notulensi/export/docx', [NotulensiController::class, 'exportDocx'])->name('notulensi.export.docx');

    // Roles: Secretaries Only (Master & Bidang)
    Route::middleware(['role:sekretaris_master,sekretaris_bidang'])->group(function () {
        // Agenda CRUD
        Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
        Route::put('/agenda/{agenda}', [AgendaController::class, 'update'])->name('agenda.update');
        Route::delete('/agenda/{agenda}', [AgendaController::class, 'destroy'])->name('agenda.destroy');

        // Presensi Manual Corrections
        Route::post('/agenda/{agenda}/absen/koreksi', [PresensiController::class, 'koreksi'])->name('agenda.absen.koreksi');

        // Notulensi Editing
        Route::get('/agenda/{agenda}/notulensi/edit', [NotulensiController::class, 'edit'])->name('notulensi.edit');
        Route::post('/agenda/{agenda}/notulensi/upload', [NotulensiController::class, 'uploadAudio'])->name('notulensi.upload');
        Route::post('/agenda/{agenda}/notulensi/process-audio', [NotulensiController::class, 'processAudio'])->name('notulensi.process.audio');
        Route::get('/agenda/{agenda}/notulensi/status', [NotulensiController::class, 'checkStatus'])->name('notulensi.status');
        Route::delete('/agenda/{agenda}/notulensi/audio/{index}', [NotulensiController::class, 'deleteAudio'])->name('notulensi.audio.delete');
        Route::post('/agenda/{agenda}/notulensi/save', [NotulensiController::class, 'saveDraft'])->name('notulensi.save');
        Route::post('/agenda/{agenda}/notulensi/submit', [NotulensiController::class, 'submitForReview'])->name('notulensi.submit');
        Route::post('/agenda/{agenda}/notulensi/regenerate', [NotulensiController::class, 'regenerate'])->name('notulensi.regenerate');
        Route::post('/agenda/{agenda}/notulensi/external', [NotulensiController::class, 'addExternal'])->name('notulensi.external.add');
        Route::delete('/notulensi/external/{participant}', [NotulensiController::class, 'deleteExternal'])->name('notulensi.external.delete');
    });

    // Notulensi Review (Accessible by Ketua and Secretary for preview)
    Route::get('/agenda/{agenda}/notulensi/review', [NotulensiController::class, 'review'])->name('notulensi.review');

    // Roles: Ketua Only (Master & Bidang)
    Route::middleware(['role:ketua_master,ketua_bidang'])->group(function () {
        // Notulensi Review Actions
        Route::post('/agenda/{agenda}/notulensi/review/approve', [NotulensiController::class, 'approve'])->name('notulensi.review.approve');
        Route::post('/agenda/{agenda}/notulensi/review/revision', [NotulensiController::class, 'requestRevision'])->name('notulensi.review.revision');
    });

    // Roles: Admin Only
    Route::middleware(['role:admin'])->group(function () {
        // Admin User CRUD
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::post('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

        // Admin Bidang CRUD
        Route::get('/admin/bidang', [AdminUserController::class, 'bidangIndex'])->name('admin.bidang.index');
        Route::post('/admin/bidang', [AdminUserController::class, 'bidangStore'])->name('admin.bidang.store');
        Route::put('/admin/bidang/{bidang}', [AdminUserController::class, 'bidangUpdate'])->name('admin.bidang.update');
    });
});
