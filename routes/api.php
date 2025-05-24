<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\GuestAccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta de autenticación
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta de validación de acceso
Route::post('/guest-accesses/validate', [GuestAccessController::class, 'validateAccess'])
    ->name('guest-accesses.validate');

// Rutas del chatbot RASA (sin autenticación)
Route::prefix('chatbot')->group(function () {
    Route::get('/events', [EventApiController::class, 'listEvents'])->name('api.events.list');
    Route::get('/event/info', [EventApiController::class, 'getEventInfo'])->name('api.event.info');
    Route::get('/event/times', [EventApiController::class, 'getEventTimes'])->name('api.event.times');
    Route::get('/event/location', [EventApiController::class, 'getEventLocation'])->name('api.event.location');
    Route::get('/event/hosts', [EventApiController::class, 'getEventHosts'])->name('api.event.hosts');
    Route::get('/event/plans', [EventApiController::class, 'getEventPlans'])->name('api.event.plans');
    Route::get('/event/types', [EventApiController::class, 'getEventTypes'])->name('api.event.types');
    Route::get('/guest-info/{guestId}', [EventApiController::class, 'getGuestInfo'])->name('api.guest.info');
});
