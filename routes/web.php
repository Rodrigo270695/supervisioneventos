<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ChatbotController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Ruta del chatbot para mostrar después de escanear QR
Route::get('chatbot', [ChatbotController::class, 'show'])->name('chatbot.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
