<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HostTypeController;
use App\Http\Controllers\TimeTypeController;
use App\Http\Controllers\PlanTypeController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\GuestAccessController;
use App\Http\Controllers\AccessReportController;
use App\Http\Controllers\ManualGuestAccessController;
use App\Http\Controllers\PlanController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Ruta del chatbot para mostrar después de escanear QR
Route::get('chatbot', [ChatbotController::class, 'show'])->name('chatbot.show');

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas para tipos de eventos
    Route::resource('event-types', EventTypeController::class);

    // Rutas para eventos
    Route::resource('events', EventController::class);

    // Rutas para tipos de anfitrión
    Route::resource('host-types', HostTypeController::class);

    // Rutas para tipos de tiempo
    Route::resource('time-types', TimeTypeController::class);

    // Rutas para tipos de plano
    Route::resource('plan-types', PlanTypeController::class);

    // Rutas para anfitriones
    Route::post('/hosts', [HostController::class, 'store'])->name('hosts.store');
    Route::put('/hosts/{host}', [HostController::class, 'update'])->name('hosts.update');
    Route::delete('/hosts/{host}', [HostController::class, 'destroy'])->name('hosts.destroy');

    // Rutas para tiempos
    Route::resource('times', TimeController::class)->only(['store', 'update', 'destroy']);

    // Rutas para notas
    Route::resource('notes', NoteController::class)->only(['store', 'update', 'destroy']);

    // Rutas para invitados
    Route::get('/events/{event}/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/events/{event}/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
    Route::get('/events/{event}/guests/download-qr', [GuestController::class, 'downloadAllQR'])->name('guests.download-all-qr');

    // Rutas para gestión de personal de seguridad
    Route::resource('security', SecurityController::class);

    // Rutas para planos
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
});

// Rutas públicas para verificación de QR y acceso
Route::get('/guest/verify/{qr_code}', [GuestController::class, 'verifyAccess'])->name('guest.verify');
Route::post('/guest/{guest}/access', [GuestController::class, 'registerAccess'])->name('guest.access');

// Rutas para control de acceso
Route::middleware(['auth'])->group(function () {
    Route::get('guest-accesses', [GuestAccessController::class, 'index'])->name('guest-accesses.index');
    Route::get('guest-accesses/scan', [GuestAccessController::class, 'scan'])->name('guest-accesses.scan');
    Route::post('guest-accesses', [GuestAccessController::class, 'store'])->name('guest-accesses.store');

    // Rutas para registro manual de acceso
    Route::get('manual-guest-accesses', [ManualGuestAccessController::class, 'index'])->name('manual-guest-accesses.index');
    Route::get('manual-guest-accesses/validate/{guest}', [ManualGuestAccessController::class, 'validateGuest'])->name('manual-guest-accesses.validate');
    Route::post('manual-guest-accesses', [ManualGuestAccessController::class, 'store'])->name('manual-guest-accesses.store');
});

Route::post('/api/guest-accesses/validate', [GuestAccessController::class, 'validateAccess'])
    ->middleware(['auth'])
    ->name('guest-accesses.validate');

// Rutas para reportes de acceso
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/access', [AccessReportController::class, 'index'])->name('reports.access');
    Route::get('/reports/access/{event}/export', [AccessReportController::class, 'export'])->name('reports.access.export');
});

Route::post('/chatbot/message', [ChatbotController::class, 'processMessage'])->name('chatbot.message');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
