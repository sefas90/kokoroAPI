<style type="text/css">
    .md-3 {
        width: 33%;
    }
    .md-4 {
        width: 25%;
    }
    .md-5 {
        width: 20%;
    }
    .data-table {
        width: 100%;
        font-size: 10px;
    }
    .selected {
        background-color: aquamarine;
    }
    table {
        border-collapse: collapse;
        border-spacing: 0;
        border: 1px solid black;
    }
    th, td {
        text-align: left;
        padding: 5px;
        border: 1px solid black;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .title{
        width: 100%;
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>

<div class="container">
    <div>
        <table class="data-table">
            <thead>
            <tr>
                <th class="md-3" rowspan="2"><img src="../public/kokoro_logo.jpg" width="80%"></th>
                <th class="md-3" rowspan="2">{{$data['businessName']}}</th>
                <th class="md-3">Numero de orden: {{$data['order']}}</th>
            </tr>
            <tr>
                <th>Fecha de emision: {{$data['date']}}</th>
            </tr>
            </thead>
        </table>
        <table class="data-table">
            <tbody>
            <tr>
                <td>Medio</td>
                <td>Programa</td>
                <td>Horario</td>
                <td>Material</td>
                <td>Dur</td>
                @for ($i = 1; $i <= $data['daysInMonth']; $i++)
                <td>{{ $i }}</td>
                @endfor
                <td>Spots</td>
                <td>C. Unitario</td>
                <td>Inversion</td>
            </tr>
            @foreach($data['result'] as $key => $row)
            <tr>
                <td>{{ $row->media_name }}</td>
                <td>{{ $row->show }}</td>
                <td>{{ $row->hourIni }} {{$row->hourEnd}}</td>
                <td>{{ $row->material_name }}</td>
                <td>{{ $row->duration }}</td>
                @for ($i = 1; $i <= $data['daysInMonth']; $i++)
                    <td class="border-table">
                    @foreach($row->planing as $k => $r)
                        @if ($i == $r->day)
                        <span class="selected">{{ $r->times_per_day }}</span>
                        @endif
                    @endforeach
                    </td>
                @endfor
                <td>{{ $row->spots }}</td>
                <td>{{ number_format($row->cost, 2, '.', '') }}</td>
                <td>{{ number_format($row->spots * $row->cost, 2, '.', '') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <table class="data-table">
            <thead>
            <tr>
                <th class="md-5">Responsable</th>
                <th class="md-5">Cliente</th>
                <th class="md-5">Totales</th>
                <th class="md-5">Ins.</th>
                <th class="md-5">Inversion</th>
            </tr>
            <tr>
                <th rowspan="2">{{ $data['user'] }}</th>
                <th rowspan="2">{{ $data['client'] }}</th>
                <th>Total</th>
                <th>{{ $data['totalSpots'] }}</th>
                <th>{{ number_format($data['totalMount'], 2, '.', '') }}</th>
            </tr>
            <tr>
                <th>Total Orden</th>
                <th>{{ $data['totalSpots'] }}</th>
                <th>{{ number_format($data['totalMount'], 2, '.', '') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Facturar a:</td>
                <td>Direccion de facturacion:</td>
                <td>Politicas de facturacion:</td>
                <td colspan="2">Observaciones</td>
            </tr>
            <tr>
                <td>{{ $data['billingToName'] }}<br>{{ $data['billingToNit'] }}</td>
                <td>{{ $data['billingAddress'] }}</td>
                <td>{{ $data['billingPolicies'] }}</td>
                <td>{{ $data['observation1'] }}</td>
                <td>{{ $data['observation2'] }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/php">
    if (isset($pdf)) {
        $text = "Página {PAGE_NUM} / {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 35;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
</body>
</html>

