<?php

use App\Http\Controllers\ApotekerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\QueueDisplayController;
use App\Http\Controllers\SusterController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Klinik Unpam 24 Jam
|--------------------------------------------------------------------------
*/

// Halaman Depan Publik & Monitor Antrian Real-Time
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->getDashboardRoute());
    }
    return redirect()->route('login');
})->name('home');

Route::get('/monitor', [QueueDisplayController::class, 'index'])->name('queue.monitor');
Route::get('/monitor/live', [QueueDisplayController::class, 'liveData'])->name('queue.monitor.live');

// Autentikasi: Satu Pintu Login Tunggal & Registrasi Pasien
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout & Pengarah Cepat
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route fallback untuk langsung ke dashboard masing-masing
    Route::get('/dashboard', function () {
        return redirect()->route(Auth::user()->getDashboardRoute());
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Role: PASIEN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:pasien')->prefix('pasien')->as('pasien.')->group(function () {
        Route::get('/dashboard', [PasienController::class, 'dashboard'])->name('dashboard');
        Route::post('/antrian', [PasienController::class, 'storeQueue'])->name('antrian.store');
        Route::delete('/antrian/{queue}', [PasienController::class, 'cancelQueue'])->name('antrian.cancel');
        Route::get('/antrian/status/{queue}', [PasienController::class, 'getQueueStatus'])->name('antrian.status');
        Route::get('/riwayat-medis', [PasienController::class, 'medicalRecords'])->name('riwayat-medis');
        Route::get('/riwayat-medis/{queue}', [PasienController::class, 'showMedicalRecord'])->name('riwayat-medis.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Role: SUSTER
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:suster')->prefix('suster')->as('suster.')->group(function () {
        Route::get('/dashboard', [SusterController::class, 'dashboard'])->name('dashboard');
        Route::post('/antrian/{queue}/verifikasi', [SusterController::class, 'verifyQueue'])->name('antrian.verifikasi');
        Route::post('/antrian/{queue}/pindah-poli', [SusterController::class, 'transferPolyclinic'])->name('antrian.pindah-poli');
        Route::post('/antrian/{queue}/skip', [SusterController::class, 'skipQueue'])->name('antrian.skip');
        Route::post('/antrian/{queue}/recall', [SusterController::class, 'recallQueue'])->name('antrian.recall');

        // Manajemen Akun Tenaga Medis (Suster, Dokter, Apoteker)
        Route::get('/staff', [SusterController::class, 'staffIndex'])->name('staff');
        Route::post('/staff', [SusterController::class, 'staffStore'])->name('staff.store');
        Route::patch('/staff/{user}/shift', [SusterController::class, 'updateStaffShift'])->name('staff.update-shift');
        Route::get('/staff/{user}/edit', [SusterController::class, 'staffEdit'])->name('staff.edit');
        Route::put('/staff/{user}', [SusterController::class, 'staffUpdate'])->name('staff.update');
        Route::delete('/staff/{user}', [SusterController::class, 'staffDestroy'])->name('staff.destroy');

        // Riwayat Medis Semua Pasien (Filter Fleksibel: Nama, Tanggal, Poli, Dokter)
        Route::get('/riwayat-medis', [SusterController::class, 'medicalRecords'])->name('riwayat-medis');
        Route::get('/riwayat-medis/{queue}', [SusterController::class, 'showMedicalRecord'])->name('riwayat-medis.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Role: DOKTER
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:dokter')->prefix('dokter')->as('dokter.')->group(function () {
        Route::get('/dashboard', [DokterController::class, 'dashboard'])->name('dashboard');
        Route::post('/antrian/{queue}/masuk', [DokterController::class, 'startExamination'])->name('antrian.masuk');
        Route::get('/antrian/{queue}/periksa', [DokterController::class, 'examinationForm'])->name('periksa');
        Route::post('/antrian/{queue}/selesai-periksa', [DokterController::class, 'submitExamination'])->name('antrian.selesai-periksa');
        Route::post('/antrian/{queue}/resepkan-ulang', [DokterController::class, 'resubmitPrescription'])->name('antrian.resepkan-ulang');
        Route::post('/antrian/{queue}/pindah-poli', [DokterController::class, 'transferPolyclinic'])->name('antrian.pindah-poli');
    });

    /*
    |--------------------------------------------------------------------------
    | Role: APOTEKER
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:apoteker')->prefix('apoteker')->as('apoteker.')->group(function () {
        Route::get('/dashboard', [ApotekerController::class, 'dashboard'])->name('dashboard');
        Route::post('/antrian/{queue}/verifikasi-resep', [ApotekerController::class, 'verifyPrescription'])->name('antrian.verifikasi-resep');
        Route::post('/antrian/{queue}/ajukan-penggantian-obat', [ApotekerController::class, 'requestPrescriptionReplacement'])->name('antrian.ajukan-penggantian-obat');
        Route::post('/antrian/{queue}/serahkan-obat', [ApotekerController::class, 'completeDispensing'])->name('antrian.serahkan-obat');
    });
});
