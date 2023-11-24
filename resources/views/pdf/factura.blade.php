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

    .div-header-1{
        float: left;
        width: 50%;
    }

    .div-header-2{
        float: right;
        width: 50%;
        text-align: center;
    }

    /** asignacion de la tabla */
    table{
        width: 100%;
    }

    thead{
        border: 1px solid #df5826;
        background-color: #df5826;
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
        border-top: 1px solid #df5826;
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
            <div class="div-header-1">
                <h1>FACTURA</h1>
                <p>
                    NO: 01234 <br>
                    DATE: 20/11/2023<br>
                    PACITEL 1980 INVERSIONES Y GESTION<br>
                    S.L C/ ORENSE, 18 28020 MADRID
                </p><br>
            </div>
            <div class="div-header-2">
                <img src="{{ public_path() . $img }}" alt="">
                <p class="parrafo-2">Digitalización y gestión de
                    alquileres vacacionales</p><br><br>
            </div>
            <br><br>
        </section>
    </header>
    
    <section class="mt-4">
        <h6>Liquidacion {{$mes_nombre}}</h6>
        <p>{{$usuario}}<br>
        NIF: 05789658-78<br>
        Calle La Reforma, Centro Comercial Plaza, locales 1-3, San Salvador</p>
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
                    <td>0.00 €</td>
                </tr>
                <tr>
                    <td>Comision por gestion</td>
                    <td>0.00 €</td>
                </tr>
                <tr>
                    <td>Limpiezas</td>
                    <td>0.00 €</td>
                </tr>
                <tr>
                    <td class="celda-inversion">Inversiones y mejoras</td>
                    <td class="celda-inversion">0.00 €</td>
                </tr>
                <tr class="fila-comision"></tr>
                <tr>
                    <td>Comision agencias (booking)</td>
                    <td>0.00 €</td>
                </tr>
                <tr class="fila-total">
                    <td class="celda-total"><strong>SUB TOTAL</strong></td>
                    <td>{{$sub_total}} €</td>
                </tr>
                <tr>
                    <td class="celda-total"><strong>TAX (10%)</strong></td>
                    <td>0 €</td>
                </tr>
                <tr>
                    <td class="celda-total"><strong>TOTAL</strong></td>
                    <td>{{$sub_total}} €</td>
                </tr>
            </tbody>
        </table>
    </section>
</body>
</html>