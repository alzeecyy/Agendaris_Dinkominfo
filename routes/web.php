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
});

// Main Protected Application Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard & Calendars
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
    Route::get('/dashboard/events', [DashboardController::class, 'getEvents'])->name('dashboard.events');

    // Agenda Details
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
        Route::post('/agenda/{agenda}/notulensi/save', [NotulensiController::class, 'saveDraft'])->name('notulensi.save');
        Route::post('/agenda/{agenda}/notulensi/submit', [NotulensiController::class, 'submitForReview'])->name('notulensi.submit');
        Route::post('/agenda/{agenda}/notulensi/external', [NotulensiController::class, 'addExternal'])->name('notulensi.external.add');
        Route::delete('/notulensi/external/{participant}', [NotulensiController::class, 'deleteExternal'])->name('notulensi.external.delete');
    });

    // Roles: Ketua Only (Master & Bidang)
    Route::middleware(['role:ketua_master,ketua_bidang'])->group(function () {
        // Notulensi Review
        Route::get('/agenda/{agenda}/notulensi/review', [NotulensiController::class, 'review'])->name('notulensi.review');
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
