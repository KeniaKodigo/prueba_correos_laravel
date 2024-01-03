<?php

namespace App\Console\Commands;

use App\Mail\PruebaMailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TareaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tarea:correo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de correos automaticos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
    }
}
