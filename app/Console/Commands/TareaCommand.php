<?php

namespace App\Console\Commands;

use App\Mail\PruebaMailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

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
