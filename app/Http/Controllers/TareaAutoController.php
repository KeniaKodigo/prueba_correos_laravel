<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\PruebaMailable;

class TareaAutoController extends Controller
{
    public function __invoke(){
        $usuarios = Http::get("http://localhost/api_proveedores_fsj17/public/api/proveedores_activos");

        $pdf = PDF::loadView('pdf.factura');

        $correos = $usuarios['detalle'];
        foreach($correos as $value){
            //echo $value['correo'];
            Mail::to($value['correo'])->send(new PruebaMailable($pdf));
            //echo "Mensaje Enviado";
        }
    }
}
