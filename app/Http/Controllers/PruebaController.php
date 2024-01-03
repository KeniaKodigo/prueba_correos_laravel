<?php

namespace App\Http\Controllers;

use App\Mail\PruebaMailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Faker\Provider\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PruebaController extends Controller
{
    public function index(){
        $pokemones = Http::get("https://pokeapi.co/api/v2/pokemon/");

        return $pokemones['results'];
    }

    public function generarFactura(){
        /*$img = '/img/logo-factura.png';
        $pdf = PDF::loadView('pdf.factura', compact('img'));
        
        /**
         * stream() => visualiza el pdf y tiene la opcion de descargar
         * download() => descargar el pdf de inmediato
         */
        //return $pdf->stream('factura.pdf');
        //$pdf->save(public_path("pdf/hola.pdf"));
        //return "enviado";
        //echo now();

        setlocale(LC_ALL, 'es');
        $mes = Carbon::now()->month(-1)->formatLocalized('%B');
        echo $mes;

    }

    public function enviarCorreos(){
        //$usuarios = Http::get("http://www.pruebaclientes.local/public/api/proveedores_activos");
        $clientes = json_decode(file_get_contents(public_path() . "/clientes.json"), true);

        $mes_actual = Carbon::now()->month;
        setlocale(LC_ALL, 'es');
        $mes_nombre = Carbon::now()->month(-1)->formatLocalized('%B');
        $img = '/img/logo-factura.png';
        
        $correos = $clientes['detalle'];
        foreach($correos as $value){
            $mes_factura_salida = Carbon::parse($value['fecha_salida'])->month;
            //echo $mes_factura_salida . "<br>";
            if($mes_factura_salida == $mes_actual){
                $usuario = $value['nombre'];
                $sub_total = $value['monto_total'];
                $pdf = PDF::loadView('pdf.factura', compact('usuario','img','mes_nombre','sub_total'));
                $pdf->save(public_path('storage')."/$usuario-$mes_actual.pdf");
                //seccion de cloudinary
                $pdfPathFactura = 'storage/'.$usuario.'-'.$mes_actual.'.pdf';
                $uploadedFile1 = Cloudinary::upload(public_path($pdfPathFactura),['folder' => 'facturas',]);
                $pdfUrl1 = $uploadedFile1->getSecurePath();
                //echo $value['correo'];
                /*Mail::to($value['correo'])->send(new PruebaMailable($usuario,$mes_actual));*/
                echo "Mensaje Enviado";
            }
        }
        
        //return $clientes['detalle'];
        //Mail::to('yanetprogrammin19@gmail.com')->send(new PruebaMailable);
        //return "Mensaje Enviado";

        //return $usuarios['detalle'];
    }
}
