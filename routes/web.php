<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;

// Página principal
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (solo para usuarios autenticados y verificados)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo de rutas para usuarios autenticados
Route::middleware('auth')->group(function () {
    // Gestión del perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   
    // Rutas específicas para administradores
    Route::middleware([CheckAdmin::class])->group(function () {
        // CRUD para usuarios, exclusivo para administradores
        Route::resource('users', UserController::class);
        
        // CRUD completo para reservas, solo para administradores
        Route::resource('reservations', ReservationController::class)->except(['create', 'store', 'index']);
    });
   
    // Rutas para clientes e invitados autenticados
    Route::group([], function () {
        // Vista del calendario
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        
        // Si el usuario no está autenticado como cliente, se le redirige al registro antes de hacer una reserva
        Route::middleware(['guest'])->group(function () {
            Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create')->middleware('auth');
        });
        
        // Listado de reservas y creación de reservas
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
    });
});

// Rutas específicas para administradores para gestionar las pistas
Route::middleware(['auth', CheckAdmin::class])->group(function () {
    Route::resource('courts', CourtController::class);
});

require __DIR__.'/auth.php';