<?php

use App\Http\Controllers\PruebaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CorreosController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/pokemones',[PruebaController::class, 'index']);

//Rutas de endpoints para la ejecucion de alojamientos
Route::middleware(['auth:sanctum', 'headers-dygav'])->group(function () {
    //Ruta para enlistar los usuarios y el id del alojamiento
    Route::get('/lista_usuarios', [BookingController::class, 'listUser']);
    //Ruta de alojamientos por usuario
    Route::get('/listaAlojamientosByUsuario/{userId}', [BookingController::class, 'accomodationUser']);
    Route::get('/guardar', [BookingController::class, 'saveDetails']);
    Route::get('/generarCorreo', [BookingController::class, 'generarDocByAlojamiento']);
    //ruta para la asignacion de comision por patnerFee y limpieza
    Route::put('/agregarComisionLimpieza/{accomodation_id}',[BookingController::class, 'addCommissionPatnerCleaning']);
    //ruta para la asignacion de items de gastos por accomodation
    Route::put('/agregarGastos/{accomodation_id}',[BookingController::class, 'items_comision']);
    //obtener todos los datos de las reservaciones 
    Route::get('/datos', [BookingController::class, 'detailsBookings']);
    //Ruta para mostrar la acumulacion de reservacion del usuario y alojamiento
    Route::get('/detalle_acumulado/{userId}/{accomodation}', [BookingController::class, 'accumulateAmountsAccommodations']);

    //PRUEBA DE DATOS
    Route::get('/generar_prueba', [BookingController::class, 'generar_prueba']);
});

Route::post('accesoToken', [LoginController::class, 'acceder']);
//ruta para obtener todas las facturas
Route::get('/facturas', [BookingController::class, 'getInvoice']);
//ruta para obtener facturas por usuario
Route::get('/factura_usuario/{name}', [BookingController::class, 'getInvoiceByUser']);