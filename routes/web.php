<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\PolizaController;
use App\Http\Controllers\AseguradoraController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\OperadorAmbientalController;

// Cambio de contraseña obligatorio (primer login) - fuera del must.change.password
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

Route::middleware(['auth', 'must.change.password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/certificate', [ProfileController::class, 'uploadCertificate'])->name('profile.certificate.update');

    // Rutas del Sistema de Pólizas
    Route::get('polizas/export/excel', [PolizaController::class, 'exportExcel'])->name('polizas.export_excel');
    Route::get('polizas/export/pdf', [PolizaController::class, 'exportPdf'])->name('polizas.export_pdf');
    Route::resource('polizas', PolizaController::class);
    Route::post('polizas/{poliza}/renovar', [PolizaController::class, 'renovar'])->name('polizas.renovar');
    Route::match(['get', 'post'], 'polizas/{poliza}/oficio-pdf', [PolizaController::class, 'generarOficioPdf'])->name('polizas.oficio_pdf');
    Route::post('polizas/{poliza}/regenerar-oficio', [PolizaController::class, 'regenerarOficio'])->name('polizas.regenerar_oficio');
    Route::get('polizas/{poliza}/renovacion-pdf', [PolizaController::class, 'getPdfRenovacion'])->name('polizas.renovacion_pdf');
    Route::post('polizas/{poliza}/renovacion-firmar', [PolizaController::class, 'firmarRenovacion'])->name('polizas.renovacion_firmar');
    Route::post('polizas/{poliza}/enviar-oficio', [PolizaController::class, 'enviarOficio'])->name('polizas.enviar_oficio');
    Route::delete('polizas/{polizaNueva}/eliminar-renovacion', [PolizaController::class, 'eliminarRenovacion'])->name('polizas.eliminar_renovacion');
    Route::resource('aseguradoras', AseguradoraController::class);
    // Sucursales (anidadas bajo aseguradora)
    Route::post('aseguradoras/{aseguradora}/sucursales', [AseguradoraController::class, 'storeSucursal'])->name('aseguradoras.sucursales.store');
    Route::put('aseguradoras/{aseguradora}/sucursales/{sucursal}', [AseguradoraController::class, 'updateSucursal'])->name('aseguradoras.sucursales.update');
    Route::delete('aseguradoras/{aseguradora}/sucursales/{sucursal}', [AseguradoraController::class, 'destroySucursal'])->name('aseguradoras.sucursales.destroy');
    Route::resource('ciudades', CiudadController::class);
    Route::resource('contratistas', ContratistaController::class);
    Route::resource('operadores-ambientales', OperadorAmbientalController::class);
    Route::resource('administradores', AdministradorController::class);
    Route::resource('contratos', ContratoController::class);

    // Módulo de Usuarios (Solo Administrador / Super Admin)
    Route::middleware(['role:Administrador|Super Admin'])->group(function () {
        Route::resource('users', UserController::class);

        // Auditoría Global
        Route::get('/auditoria', [\App\Http\Controllers\AuditoriaController::class, 'index'])->name('auditoria.index');

        // Configuración Global y Secuencias
        Route::get('/configuracion', [\App\Http\Controllers\ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('/configuracion/oficios', [\App\Http\Controllers\ConfiguracionController::class, 'guardarSecuenciaOficios'])->name('configuracion.oficios');
        Route::post('/configuracion/polizas', [\App\Http\Controllers\ConfiguracionController::class, 'guardarSecuenciaPolizas'])->name('configuracion.polizas');
        Route::post('/configuracion/correo', [\App\Http\Controllers\ConfiguracionController::class, 'guardarConfiguracionCorreo'])->name('configuracion.correo');
        Route::post('/configuracion/dias-anticipacion', [\App\Http\Controllers\ConfiguracionController::class, 'guardarDiasAnticipacion'])->name('configuracion.dias_anticipacion');
    });
});

require __DIR__ . '/auth.php';
