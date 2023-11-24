<?php

use App\Http\Controllers\PruebaController;
use App\Mail\PruebaMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//enviamos el correo y asignacion de ruta
/*Route::get('envio', function () {
    Mail::to('yanetprogrammin19@gmail.com')->send(new PruebaMailable);
    return "Mensaje Enviado";
})->name('envio_correo');*/

Route::get('/factura', [PruebaController::class, 'generarFactura']);

Route::get('/correo', [PruebaController::class, 'enviarCorreos']);
