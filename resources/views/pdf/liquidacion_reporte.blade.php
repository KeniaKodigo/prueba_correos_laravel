<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Document</title>
</head>
<style>
    body{
        font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
    }

    header{
        height: 15%;
    }

    .div-header-1{
        float: right;
        width: 50%;
    }

    .div-header-2{
        float: left;
        width: 50%;
        text-align: left;
    }

    .section-liquidacion{
        float: right;
        width: 50%;
    }

    .section-periodo{
        float: left;
        width: 50%;
        text-align: left;
    }

    /** asignacion de la tabla */
    table{
        width: 100%;
    }

    thead{
        border: 1px solid #f4511e;
        background-color: #f4511e;
    }

    thead th{
        padding: 15px;
        color: white;
    }

    tbody tr td{
        padding-left: 25px;
        padding-right: 25px;
        padding-top: 15px;
    }

    .fila-total{
        border-top: 1px solid #f4511e;
    }

    .celda-total{
        padding-left: 400px;
    }

    h1, h6{
        font-weight: bold;
    }

</style>
<body>
    <header>
        <section class="section-header">
            <div class="div-header-2">
                <img src="{{ public_path() . $img_liquidacion }}" alt="" width="200px">
            </div>
            <div class="div-header-1">
                <p>
                    PACITEL 1980 INVERSIONES Y GESTION<br>
                    S.L C/ ORENSE, 18 28020 MADRID<br>
                    B-72732761
                </p><br><br><br><br><br>
            </div>
            <br><br>
        </section>
    </header>

    <section class="mt-4 apartado-liquidacion">
        <h6 class="section-liquidacion">LIQUIDACIÓN {{$mes_nombre}} N.º VT-{{$correlativo}}</h6>
        <h6 class="section-periodo">Detalle del periodo {{$fecha_actual}}</h6>
    </section>
    
    <section class="mt-2">
        <p>{{$usuario}}<br>
        NIF: 05789658-78<br>
        Calle La Reforma, Centro Comercial Plaza, locales 1-3, San Salvador<br>
        <strong>Alojamiento: </strong>{{$accomodation}}</p>
    </section>

    <section class="mt-5">
        <table>
            <thead>
                <th>CONCEPTO</th>
                <th>TOTAL</th>
            </thead>
            <tbody>
                <tr>
                    <td>Reservas</td>
                    <td>{{$montoBooking}} €</td>
                </tr>
                <tr>
                    <td>Comision DYGAV</td>
                    <td>{{round($montoDygav,2)}} €</td>
                </tr>
                <tr>
                    <td>Comision por plataformas</td>
                    @if ($montoExpensesCustomer > 0)
                        <td>{{round($montoExpensesCustomer,2)}} €</td>
                    @else
                        <td>{{$montoPartner}} €</td>
                    @endif
                    
                </tr>
                <tr>
                    <td>Limpiezas</td>
                    <td>{{$montoCleaning}} €</td>
                </tr>
                @if ($items != null)
                @php $suma_items = 0; @endphp
                    @foreach ($items as $arr_item)
                        @foreach ($arr_item as $value)
                            @php
                                //sumando todo los precios
                                $suma_items += $value['precio'];
                            @endphp
                        <tr>
                            <td>@php echo $value['item']; @endphp</td>
                            <td>@php echo $value['precio']; @endphp</td>
                        </tr>
                        @endforeach
                    @endforeach
                @else
                    @php
                        $suma_items = 0;
                    @endphp
                    <tr>
                        <td class="celda-inversion">Inversiones y mejoras</td>
                        <td class="celda-inversion">0 €</td>
                    </tr>
                @endif

                @php
                    //APARTADO DE CALCULOS, se resta la suma de los items al subtotal
                    $total_suma = abs($suma_items - $subtotal2);
                @endphp
                <tr class="fila-comision"></tr>
                <tr>
                    <td class="celda-total"><strong>TOTAL</strong></td>
                    <td>{{round($total_suma,2)}} €</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="mt-5">
        <p>
            PACITEL 1980 INVERSIONES Y GESTION<br>
            S.L C/ ORENSE, 18 28020 MADRID<br>
            B-72732761
        </p>
    </section>
</body>
</html>