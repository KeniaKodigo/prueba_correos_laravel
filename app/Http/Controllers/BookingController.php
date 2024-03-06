<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Mail\EnvioCorreos;
use App\Models\DetalleAlojamientos;
use App\Models\DetalleFactura;
use App\Models\DetalleLiquidacion;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function listUser(){
        $users = Http::get('https://dygav.es/api/accomodations/populate');
        $array_user = json_decode($users, true);
        $new_array = [];

        foreach ($array_user as $elemento) {
            if ($elemento["userId"] !== null) {
                $user = [];
                $user['userId'] = $elemento['userId']["_id"];
                $user['fullname'] = $elemento['userId']["fullname"];
                $user['email'] = $elemento['userId']["email"];
                $user['accomodationId'] = $elemento['accomodationId'];
                $new_array[] = $user;
            }
        }
        return response()->json($new_array);
    }

    //metodo para obtener la informacion de las facturas por usuario
    public function getInvoiceByUser($name){
        $invoice = DetalleFactura::where('name','=',$name)->get();

        return response()->json($invoice);
    }

    //metodo para obtener la informacion de las facturas
    public function getInvoice(){
        $invoice = DetalleFactura::all();

        return response()->json($invoice);
    }

    //metodo para obtener los detalle de los alojamientos por usuario
    public function accomodationUser($userId){
        $array_accomodations = DetalleAlojamientos::select('userId','accommodationId','accommodationName')->distinct('accommodationId')->where('userId','=',$userId)->get();
        $new_array = [];

        foreach($array_accomodations as $item){
            $id_accommodation = $item['accommodationId'];

            $bookings_user = Http::withHeaders([
                'X-AUTH-TOKEN' => 'e8f0d39c83dff242a8e47744d1d0fc5f76e4e2363752f6fe117f7d7e2924144d',
            ])->get("https://api.avaibook.com/api/owner/accommodations/$id_accommodation/");

            $booking_decode = json_decode($bookings_user, true);
            $accommodation = [];
            $accommodation['id'] = $booking_decode['id'];
            $accommodation['name'] = $booking_decode['name'];
            $accommodation['images'] = $booking_decode['images'][0];
            $accommodation['introduction'] = $booking_decode['introduction']['es'];
            $new_array[] = $accommodation;
        }
        return response()->json($new_array);
    }

    //metodo para agregar los items de gastos por accomodationId
    public function items_comision(Request $request, $accomodation_id){
        $info_alojamiento = DetalleAlojamientos::select('*')->where('accommodationId','=',$accomodation_id)->get();

        foreach($info_alojamiento as $value){
            $id = $value['id'];
            $comisiones = DetalleAlojamientos::find($id);
            $comisiones->item_expenses = $request->all();
            $comisiones->update();
        }
        return "Actualizado";
    }

    //metodo para agregar la comision por pagos en linea y la limpieza por accomodationId
    public function addCommissionPatnerCleaning(Request $request, $accomodation_id){
        $datos = array(
            "limpieza" => $request->input('limpieza'),
            "impuesto_pago" => $request->input('impuesto_pago')
        );

        $info_alojamiento = DetalleAlojamientos::select('*')->where('accommodationId','=',$accomodation_id)->where('partnerName','=','Booking.com')->get();
        foreach($info_alojamiento as $value){
            $id = $value['id'];
            $comisiones = DetalleAlojamientos::find($id);
            $comisiones->cleaning = $datos['limpieza'];
            $comisiones->taxesPayments = $datos['impuesto_pago'];
            $comisiones->update();
        }
        
        return $this->calculateImputedExpenses($accomodation_id);
    }

    //calcula los gatos imputados para el partnerFee
    public function calculateImputedExpenses($accomodation_id){
        $detalle = DetalleAlojamientos::select('*')->where('accommodationId','=',$accomodation_id)->where('partnerName','=','Booking.com')->get();

        foreach($detalle as $value){
            $id = $value['id'];
            $monto_booking = $value['totalAmount'];
            $monto_partner = $value['partnerFee'];
            $impuesto = $value['taxesPayments'];
            $comision_pago = $monto_booking * ($impuesto / 100);
            $total_gasto = $monto_partner + $comision_pago;

            $comisiones = DetalleAlojamientos::find($id);
            $comisiones->commissionPayments = $comision_pago;
            $comisiones->expensesCustomer = $total_gasto;
            $comisiones->update();
        }
        return "Actualizado";
    }

    //Guardar los detalle de cada alojamiento de los usuarios en la base de datos
    public function saveDetails(){
        $usuarios = Http::get('https://dygav.es/api/accomodations/populate');
        $arreglo_usuarios = json_decode($usuarios, true);

        foreach ($arreglo_usuarios as $elemento) {
            if ($elemento["userId"] !== null) {
                // Acceder a las propiedades de "userId"
                $accommodationId = $elemento['accomodationId'];
                $userId = $elemento["userId"];
                $userIdVal = $userId["_id"];
                $fullname = $userId["fullname"];
                $email = $userId["email"];

                $accommodation = $elemento['accomodationId'];
                $bookings_user = Http::withHeaders([
                    'X-AUTH-TOKEN' => 'e8f0d39c83dff242a8e47744d1d0fc5f76e4e2363752f6fe117f7d7e2924144d',
                ])->get("https://api.avaibook.com/api/owner/accommodations/$accommodation/calendar/");

                $booking_decode = json_decode($bookings_user, true);
                foreach($booking_decode as $item){

                    // Condiciones para buscar un registro existente
                    $conditions = [
                        'userId' => $userIdVal,
                        'booking' => $item['booking'],
                    ];

                    // Datos a insertar o actualizar
                    $data = [
                        'userId' => $userIdVal,
                        'name' => $fullname,
                        'email' => $email,
                        'accommodationId' => $accommodationId,
                    ];

                    if ($item['booking'] != "") {
                        $booking_id = $item['booking'];
                        $informacion = Http::withHeaders([
                            'X-AUTH-TOKEN' => 'e8f0d39c83dff242a8e47744d1d0fc5f76e4e2363752f6fe117f7d7e2924144d',
                        ])->get("https://api.avaibook.com/api/owner/bookings/$booking_id");
                        $info_decode = json_decode($informacion, true);

                        $data['accommodationName'] = $info_decode['accommodationName'];
                        $data['booking'] = $booking_id;
                        $data['inDate'] = $info_decode['checkInDate'];
                        $data['outDate'] = $info_decode['checkOutDate'];
                        $data['totalAmount'] = $info_decode['totalAmount'];

                        // Calcular la comisión del 15% de Dygav
                        $comision_dygav = $info_decode['totalAmount'] * 0.15;
                        $data['dygavFee'] = $comision_dygav;
                        $data['partnerName'] = $info_decode['partnerName'];
                        $data['partnerFee'] = $info_decode['partnerFee'];

                        //El metodo updateOrInsert registra lo nuevo y actualiza los datos que ya existen en la bd
                        DetalleAlojamientos::updateOrInsert($conditions, $data);
                        echo "Se guardo\n";
                    }
                }
            }
        }
    }

    //generar factura y liquidacion total de las reservaciones por mes para cada cliente
    public function generarDocByAlojamiento(){
        $fecha_actual = Carbon::now();
        //$mes_actual = Carbon::now()->month;
        $mes_anterior = Carbon::now()->subMonth()->month;
        $anio_actual = Carbon::now()->year;

        $mes_nombre = Carbon::now()->locale('es')->subMonth()->monthName;
        $img_factura = '/img/logo-factura.png';
        $img_liquidacion = '/img/Dygav.svg';

        $resultado = DetalleAlojamientos::select('userId','name','email')->distinct('userId')->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->get();

        foreach($resultado as $item){
            $userId = $item['userId'];
            $booking = DetalleAlojamientos::select('accommodationName','booking','inDate','outDate','totalAmount','dygavFee','partnerFee','cleaning','expensesCustomer','item_expenses')->distinct('booking')->where('userId','=',$userId)->get();
            $usuario = $item['name'];

            $contador = 1;
            //print_r($booking);
            foreach($booking as $book){
                
                $mes_salida = Carbon::parse($book['outDate'])->month;
                $anio_salida = Carbon::parse($book['outDate'])->year;
                if($mes_salida == 03 && $anio_salida == $anio_actual){
                    $correlativo = "000$contador";

                    //suma de los item de las reservaciones por mes
                    $montoBooking = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('totalAmount');

                    $montoDygav = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('dygavFee');

                    $montoPartner = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('partnerFee');

                    $montoCleaning = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('cleaning');

                    //monto expensesCustomer (donde se suma el partnerFee + comision)
                    $montoExpensesCustomer = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('expensesCustomer');

                    $accomodation = $book['accommodationName'];
                    //declaro una variable para el arreglo del json que esta en bd
                    $items = $book['item_expenses'];

                    //en la factura el subtotal no lleva el monto total de los bookings no incluye lo del partnerFee / comision
                    $subtotal = $montoDygav + $montoCleaning + $this->sum_expenses($items);

                    //en la liquidacion el subtotal lleva el monto total de los bookings
                    if($montoExpensesCustomer > 0){
                        $subtotal2 = $montoBooking - ($montoDygav + $montoExpensesCustomer + $montoCleaning);
                    }else{
                        $subtotal2 = $montoBooking - ($montoDygav + $montoPartner + $montoCleaning);
                    }

                    $total2 = $this->sum_expenses($items) - $subtotal2;
                }
                $contador++;
            }
            //reportes
            $pdf_fact = PDF::loadView('pdf.factura_reporte', compact('usuario','img_factura','mes_nombre','correlativo','montoBooking','montoDygav','montoPartner','subtotal','montoCleaning','fecha_actual','accomodation','items','montoExpensesCustomer'));

            $pdf_liq = PDF::loadView('pdf.liquidacion_reporte', compact('usuario','img_liquidacion','mes_nombre','correlativo','montoBooking','montoDygav','montoPartner','montoCleaning','fecha_actual','accomodation','subtotal2','items','montoExpensesCustomer'));

            $pdf_fact->save(public_path('storage')."/factura-$usuario-$correlativo-$mes_anterior.pdf");
            $pdf_liq->save(public_path('storage')."/liquidacion-$usuario-$correlativo-$mes_anterior.pdf");

            //seccion de cloudinary
            $pdfPathFactura = 'storage/factura-'.$usuario.'-'.$correlativo.'-'.$mes_anterior.'.pdf';
            $pdfPathLiquidacion = 'storage/liquidacion-'.$usuario.'-'.$correlativo.'-'.$mes_anterior.'.pdf';
            // Carpeta en Cloudinary donde se almacenará el PDF
            $uploadedFile1 = Cloudinary::upload(public_path($pdfPathFactura),['folder' => 'facturas',]);
            // Obtener la URL del PDF subido
            $pdfUrl1 = $uploadedFile1->getSecurePath();
            $uploadedFile2 = Cloudinary::upload(public_path($pdfPathLiquidacion), ['folder' => 'liquidaciones']);
            // Obtener la URL del PDF subido
            $pdfUrl2 = $uploadedFile2->getSecurePath();

            /*Guardando los detalles de la factura
            $factura = new DetalleFactura();
            $factura->name = $item['name'];
            $factura->accommodationName = $accomodation;
            $factura->total_booking = $montoBooking;
            $factura->total_dygav = $montoDygav;
            $factura->total_cleaning = $montoCleaning;
            $factura->total_expenses = $this->sum_expenses($items);
            $factura->subtotal = $subtotal;
            $taxes = $subtotal * 0.21;
            $factura->taxes = $taxes;
            $total = $subtotal + $taxes;
            $factura->total = $total;
            $factura->url_invoice = $pdfUrl1;
            $factura->save();

            //guardar datos de liquidacion
            $liquidacion = new DetalleLiquidacion();
            $liquidacion->name = $item['name'];
            $liquidacion->accommodationName = $accomodation;
            $liquidacion->total_booking = $montoBooking;
            $liquidacion->total_dygav = $montoDygav;
            if($montoExpensesCustomer > 0){
                $liquidacion->total_expensesCustomer = $montoExpensesCustomer;
            }else{
                $liquidacion->total_expensesCustomer = $montoPartner;
            }
            $liquidacion->total_cleaning = $montoCleaning;
            $liquidacion->total_expenses = $this->sum_expenses($items);
            $liquidacion->total = abs($total2);
            $liquidacion->url_settlement = $pdfUrl2;
            $liquidacion->save();*/

            /*Mail::to($item['email'])->send(new EnvioCorreos($usuario,$mes_anterior,$correlativo));
            echo "Enviado\n";*/
        }
    }

    public function accumulateAmountsAccommodations($userId, $accomodation){
        $fecha_actual = Carbon::now();
        $mes_actual = Carbon::now()->month;
        $anio_actual = Carbon::now()->year;
        $mes_nombre = Carbon::now()->locale('es')->monthName;
        $detail = [];

        $result = DetalleAlojamientos::select('*')->where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->get();

        foreach($result as $item){
            $detail['userId'] = $item['userId'];
            $detail['name'] = $item['name'];
            $detail['accomodationId'] = $item['accommodationId'];
            $detail['accomodation'] = $item['accommodationName'];

            //suma de los item de las reservaciones por mes
            $montoBooking = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('totalAmount');

            $montoDygav = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('dygavFee');

            $montoPartner = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('partnerFee');

            $montoCleaning = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('cleaning');

            //monto expensesCustomer (donde se suma el partnerFee + comision)
            $montoExpensesCustomer = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->sum('expensesCustomer');

            $contarBookings = DetalleAlojamientos::where('userId','=',$userId)->where('accommodationId','=',$accomodation)->whereMonth('outDate', '=' ,03)->whereYear('outDate', '=', $anio_actual)->count();

            $items_expenses = $item['item_expenses'];

            //Falta los gastos
            $detail['monto_bookings'] = $montoBooking;
            $detail['dygav'] = $montoDygav;
            $detail['partner'] = $montoPartner;
            $detail['limpieza'] = $montoCleaning;
            $detail['comisionPartner'] = $montoExpensesCustomer;
            $detail['mes_actual'] = $mes_nombre;
            $detail['bookings'] = $contarBookings;
            $detail['items_expenses'] = $this->sum_expenses($items_expenses);
        }
        $new_array[] = $detail;
        return response()->json($new_array);
    }

    //metodo para sumar los gastos de los items
    public function sum_expenses($items){
        $suma_items = 0;
        if($items != null){
            foreach($items as $arr_item){
                foreach($arr_item as $value){
                    $suma_items += $value['precio'];
                }
            }
        }else{
            $suma_items = 0;
        }
        return $suma_items;
    }

    //metodo para obtener el detalle de alojamientos de cada usuario
    public function detailsBookings(){
        $resultado = DetalleAlojamientos::select('*')->distinct('userId')->get();
        return response()->json($resultado);
    }

    //generar documento de prueba con el usuario jose
    public function generar_prueba(){
        $fecha_actual = Carbon::now();
        //$mes_actual = Carbon::now()->month;
        $mes_anterior = Carbon::now()->subMonth()->month;
        $anio_actual = Carbon::now()->year;

        $mes_nombre = Carbon::now()->locale('es')->subMonth()->monthName;
        $img_factura = '/img/logo-factura.png';
        $img_liquidacion = '/img/Dygav.svg';

        $resultado = DetalleAlojamientos::select('userId','name','email')->distinct('userId')->where('userId','=','65062d9c6fc3b01d7fb7bf81')->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->get();

        foreach($resultado as $item){
            $userId = $item['userId'];
            $booking = DetalleAlojamientos::select('accommodationName','booking','inDate','outDate','totalAmount','dygavFee','partnerFee','cleaning','expensesCustomer','item_expenses')->distinct('booking')->where('userId','=',$userId)->get();
            $usuario = $item['name'];

            $contador = 1;
            //print_r($booking);
            foreach($booking as $book){
                $mes_salida = Carbon::parse($book['outDate'])->month;
                $anio_salida = Carbon::parse($book['outDate'])->year;
                if($mes_salida == 02 && $anio_salida == $anio_actual){
                    $correlativo = "000$contador";

                    //suma de los item de las reservaciones por mes
                    $montoBooking = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->sum('totalAmount');

                    $montoDygav = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->sum('dygavFee');

                    $montoPartner = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->sum('partnerFee');

                    $montoCleaning = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->sum('cleaning');

                    //monto expensesCustomer (donde se suma el partnerFee + comision)
                    $montoExpensesCustomer = DetalleAlojamientos::where('userId', '=', $userId)->whereMonth('outDate', '=' ,02)->whereYear('outDate', '=', $anio_actual)->sum('expensesCustomer');

                    $accomodation = $book['accommodationName'];
                    
                    //en la factura el subtotal no lleva el monto total de los bookings no incluye lo del partnerFee / comision
                    $subtotal = $montoDygav + $montoCleaning;

                    //en la liquidacion el subtotal lleva el monto total de los bookings
                    $subtotal2 = $montoBooking - ($montoDygav + $montoExpensesCustomer + $montoCleaning);

                    //declaro una variable para el arreglo del json que esta en bd
                    $items = $book['item_expenses'];
                }
                $contador++;
            }
            //reportes
            $pdf_fact = PDF::loadView('pdf.factura_reporte', compact('usuario','img_factura','mes_nombre','correlativo','montoBooking','montoDygav','montoPartner','subtotal','montoCleaning','fecha_actual','accomodation','items','montoExpensesCustomer'));

            $pdf_liq = PDF::loadView('pdf.liquidacion_reporte', compact('usuario','img_liquidacion','mes_nombre','correlativo','montoBooking','montoDygav','montoPartner','montoCleaning','fecha_actual','accomodation','subtotal2','items','montoExpensesCustomer'));

            $pdf_fact->save(public_path('storage')."/factura-$usuario-$correlativo-$mes_anterior.pdf");
            $pdf_liq->save(public_path('storage')."/liquidacion-$usuario-$correlativo-$mes_anterior.pdf");

            /*Mail::to($item['email'])->send(new EnvioCorreos($usuario,$mes_anterior,$correlativo));
            echo "Enviado\n";*/
        }
    }
}
