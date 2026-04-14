<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\FavoritosController;

// Rutas protegidas para el administrador
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard y gestión de usuarios
    Route::get('/dashboard', [AuthController::class, 'adminDashboard'])->name('admin-dashboard');
    Route::get('/consultarusuarios', [AuthController::class, 'consultarUsuarios'])->name('consultarusuarios');
    Route::delete('/usuarios/{id}', [AuthController::class, 'destroy'])->name('admin.usuarios.destroy');
    Route::post('/usuarios/{id}/promote', [AuthController::class, 'promoteToAdmin'])->name('admin.usuarios.promote');

    // Gestión de compras y productos
    Route::get('/consultarcompras', [VentasController::class, 'consultarCompras'])->name('consultarcompras');
    Route::delete('/eliminar-compra/{id}', [VentasController::class, 'eliminarCompra'])->name('admin.compras.destroy');
    Route::get('/consultarproductos', [ProductosController::class, 'index'])->name('consultarproductos');

    // Rutas de productos (Gestión administrativa)
    Route::post('productos/importar', [ProductosController::class, 'importar'])->name('admin.productos.importar');
    Route::resource('productos', ProductosController::class)->names([
        'index' => 'admin.productos.index',
        'create' => 'admin.productos.create',
        'store' => 'admin.productos.store',
        'edit' => 'admin.productos.edit',
        'update' => 'admin.productos.update',
        'destroy' => 'admin.productos.destroy',
    ]);
});

// Rutas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    // Carrito
    Route::get('/carrito', [VentasController::class, 'carrito'])->name('carrito');
    Route::post('/carrito/agregar', [VentasController::class, 'agregarAlCarrito'])->name('carrito.agregar');
    Route::delete('/carrito/{id}', [VentasController::class, 'eliminarDelCarrito'])->name('carrito.eliminar');
    Route::post('/carrito/confirmar', [VentasController::class, 'confirmarCompra'])->name('carrito.confirmar');

    // Favoritos
    Route::get('/favoritos', [FavoritosController::class, 'index'])->name('favoritos');
    Route::post('/favoritos/agregar', [FavoritosController::class, 'store'])->name('favoritos.agregar');
    Route::delete('/favoritos/{id}', [FavoritosController::class, 'destroy'])->name('favoritos.destroy');

    // Mis Compras
    Route::get('/mis-compras', [VentasController::class, 'misCompras'])->name('compras');
});

// Rutas públicas de catálogo
Route::get('/', [ProductosController::class, 'home'])->name('home');
Route::get('/catalogo', [ProductosController::class, 'catalogo'])->name('catalogo');
Route::get('/ropa', [ProductosController::class, 'ropa'])->name('ropa');
Route::get('/calzado', [ProductosController::class, 'calzado'])->name('calzado');

// Ruta para mostrar el formulario de registro
Route::get('/registro', [
    AuthController::class, 'registerForm'
])->name('registro');

// Ruta para manejar el registro del usuario
Route::post('/registro', [
    AuthController::class, 'register'
])->name('registro.store');


// Ruta para mostrar el formulario de inicio de sesión
Route::get('/acceso', [
    AuthController::class, 'loginForm'
])->name('acceso');

// Ruta para verificar el inicio de sesión
Route::post('/acceso', [
    AuthController::class, 'login'
])->name('acceso.store');

// Ruta para cerrar sesión
Route::post('/cerrar', [
    AuthController::class, 'logout'
])->name('cerrar');

