<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', [ProductoController::class, 'index'])->name('home');

// Productos
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/categoria/{slug}', [ProductoController::class, 'categoria'])->name('productos.categoria');

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/

Auth::routes();

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Dashboard del usuario
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    
    // Carrito
    Route::prefix('carrito')->name('carrito.')->group(function () {
        Route::get('/', [CarritoController::class, 'index'])->name('index');
        Route::post('/agregar/{producto}', [CarritoController::class, 'agregar'])->name('agregar');
        Route::put('/actualizar/{item}', [CarritoController::class, 'actualizar'])->name('actualizar');
        Route::delete('/eliminar/{item}', [CarritoController::class, 'eliminar'])->name('eliminar');
        Route::delete('/vaciar', [CarritoController::class, 'vaciar'])->name('vaciar');
    });
    
    // Órdenes
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
        Route::get('/', [OrdenController::class, 'index'])->name('index');
        Route::get('/checkout', [OrdenController::class, 'checkout'])->name('checkout');
        Route::post('/procesar', [OrdenController::class, 'procesar'])->name('procesar');
        Route::get('/{id}', [OrdenController::class, 'show'])->name('show');
        Route::get('/{id}/confirmacion', [OrdenController::class, 'confirmacion'])->name('confirmacion');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas de Administrador
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // Aquí irán más rutas de admin (productos, órdenes, etc.)
});