<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenciasController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VehiculosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view("welcome");
});
Route::post('/usuarios', [UsuariosController::class, 'addUser']);
Route::get('/usuarios', [UsuariosController::class, 'getUsers']);
Route::get('/usuarios/user/{id}', [UsuariosController::class, 'getUserById']);
Route::get('/usuarios/dni/{dni}', [UsuariosController::class, 'getUserByDNI']);
Route::get('/usuarios/conductor/activos/{dni}', [UsuariosController::class, 'getUserActivesForDNI']);
Route::delete('/usuarios/{id}', [UsuariosController::class, 'deleteUsers']);
Route::post('/login', [UsuariosController::class, 'login']);

Route::controller(VehiculosController::class)->group(function () {
    Route::get('/vehiculos', 'getVehiculos');
    Route::get('/vehiculos/dni/{dni}', 'getVehiculoByDNI');
    Route::get('/vehiculos/nombre/{nombre}', 'getVehiculoByName');
    Route::delete('/vehiculos/{id}', 'deleteVehiculos');
    Route::post('/vehiculos', 'addVehiculo');
    //ACTUALIZAR EL ESTADO DE LA NAVEGACIÓN
    Route::post('vehiculos/state/guardar-vehiculo-solicitud', 'guardarVehiculoSolicitud');
    Route::post('vehiculos/state/crear-reporte-viaje', 'crearReporteViaje');
    Route::post('vehiculos/state/actualizar-reporte-viaje-final-real', 'actualizarReporteViajeFinalReal');

    // ASIENTOS DISPONIBLES.
    Route::get('/vehiculos/asientos/{dni}', 'getAsientosDisponibles');
    Route::get('/vehiculos/incidencias/{dni}', 'getIncidenciasConductor');
    Route::get('/vehiculos/estado/viaje/{dni}/{estado}', 'getIsActiveTravel');

});
Route::controller(DashboardController::class)->group(function () {
    Route::get('/dashboard', 'getDashboard');
});

Route::get('/incidencias', [IncidenciasController::class, 'listarIncidencias']);
Route::post('/incidencias', [IncidenciasController::class, 'crearIncidencias']);
