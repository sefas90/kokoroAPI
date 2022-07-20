<style type="text/css">
    .md-3 {
        width: 33%;
    }
    .md-4 {
        width: 25%;
    }
    .md-5 {
        width: 20%;
        max-width: 100px;
    }
    .data-table {
        width: 100%;
        font-size: 10px;
    }
    .data-table-info {
        width: 100%;
        font-size: 7px;
    }
    .selected {
        background-color: #efdfab;
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
    hr{
        page-break-after: always;
        border: none;
        margin: 0;
        padding: 0;
    }
    .nowrap {
        white-space: nowrap
    }

    .right {
        text-align: right;
    }
    .text-all {
        font-size: 8px;
    }
    @font-face {
        font-family: 'sweet_sans_prolight';
        src: url('storage/app/public/fonts/sweetsansprolight-webfont.woff2') format('woff2'),
        url('storage/app/public/fonts/sweetsansprolight-webfont.woff') format('woff');
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family: 'sweet_sans_prolight', sans-serif;
    }
    .center {
        text-align: center;
    }
    .hidden {
        display: none;
    }
    .canceled {
        font-size: 20px;
        color: red;
    }
    .days {
        width: 7px;
        max-width: 7px;
    }
    .glued {
        width: 9px;
        max-width: 9px;
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
@foreach($data as $guide => $guideRow)
    <span class="hidden">{{$count = 0}}</span>
    @foreach($guideRow['months'] as $months => $monthRow)
    <div class="container">
        <div>
            <table class="data-table">
                <thead>
                <tr>
                    <th class="md-3" rowspan="2"><img src="../public/logo_kokoro.svg" width="80%"></th>
                    <th class="md-3 center" rowspan="2"><h1>{{$guideRow['businessName']}}</h1></th>
                    <th class="md-3">Numero de orden: {{$guideRow['order']}}</th>
                    <th class="md-3">Producto: {{$guideRow['product']}}</th>
                </tr>
                <tr>
                    <th>Fecha de emision:<br>{{$guideRow['date']}}</th>
                    <th>
                        Mes:
                        @switch ($monthRow)
                            @case ('01')
                            {{'Enero'}}
                            @break
                            @case ('02')
                            {{'Febrero'}}
                            @break
                            @case ('03')
                            {{'Marzo'}}
                            @break
                            @case ('04')
                            {{'Abril'}}
                            @break
                            @case ('05')
                            {{'Mayo'}}
                            @break
                            @case ('06')
                            {{'Junio'}}
                            @break
                            @case ('07')
                            {{'Julio'}}
                            @break
                            @case ('08')
                            {{'Agosto'}}
                            @break
                            @case ('09')
                            {{'Septiembre'}}
                            @break
                            @case ('10')
                            {{'Octubre'}}
                            @break
                            @case ('11')
                            {{'Noviembre'}}
                            @break
                            @case ('12')
                            {{'Diciembre'}}
                            @break
                        @endswitch
                    </th>
                </tr>
                </thead>
            </table>
            <br>
            <table class="data-table-info">
                <tbody>
                <tr>
                    <td>Medio</td>
                    <td>Programa</td>
                    <td>Horario</td>
                    <td>Material</td>
                    <td class="glued">Dur (seg.)</td>
                    @for ($i = 1; $i <= $guideRow['daysInMonth']; $i++)
                    <td class="days">{{ $i }}</td>
                    @endfor
                    <td class="glued">Spots</td>
                    <td class="right glued"><div class="nowrap">C. Unitario</div>{{$guideRow['currency']}}</td>
                    <td class="right"><div class="nowrap">Inversión</div>{{$guideRow['currency']}}</td>
                </tr>
                @foreach($guideRow['result'] as $key => $row)
                @if(isset($row->planing[$monthRow]))
                <tr>
                    <td>{{ $row->media_name }}</td>
                    <td>{{ $row->show }}</td>
                    <td>{{ $row->hourIni }} {{$row->hourEnd}}</td>
                    <td>{{ $row->material_name }}</td>
                    <td>{{ $row->duration }} <span class="hidden">{{ $spots = 0 }}</span></td>
                    @for ($i = 1; $i <= $guideRow['daysInMonth']; $i++)
                    <td class="border-table">
                        @foreach($row->planing[$monthRow] as $k => $r)
                            @if ($i == $r->day)
                            <span class="selected">{{ $r->times_per_day }}</span>
                            <span class="hidden">{{ $spots += $r->times_per_day }}</span>
                            @endif
                        @endforeach
                    </td>
                    @endfor
                    <td>{{ $spots }}</td>
                    <td class="right">{{ number_format($row->totalCost / $spots / $guideRow['currencyValue'], 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($row->totalCost / $guideRow['currencyValue'], 2, ',', '.') }}</td>
                </tr>
                @endif
                @endforeach
                </tbody>
            </table>
            <br>
            <table class="data-table">
                <thead>
                <tr>
                    <th class="md-5">Responsable:</th>
                    <th class="md-5">Cliente:</th>
                    <th class="md-5">Totales</th>
                    <th class="md-5">Ins:</th>
                    <th class="md-5 right"><div class="nowrap">Inversión {{$guideRow['currency']}}</div></th>
                </tr>
                <tr>
                    <th rowspan="2">{{ $guideRow['user'] }}</th>
                    <th rowspan="2">{{ $guideRow['client'] }}</th>
                    <th>Total:</th>
                    <th>{{ $guideRow['totalSpots'] }}</th>
                    <th class="right">{{ number_format($guideRow['totalMount'] / $guideRow['currencyValue'], 2, ',', '.') }}</th>
                </tr>
                <tr>
                    <th>Total Orden:</th>
                    <th>{{ $guideRow['totalSpots'] }}</th>
                    <th class="right">{{ number_format($guideRow['totalMount'] / $guideRow['currencyValue'], 2, ',', '.') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Facturar a:</td>
                    <td>Direccion de facturacion:</td>
                    <td>Politicas de facturacion:</td>
                    <td colspan="2">Observaciones:</td>
                </tr>
                <tr>
                    <td class="md-5">{{ $guideRow['billingToName'] }}<br>{{ $guideRow['billingToNit'] }}</td>
                    <td class="md-5">{{ $guideRow['billingAddress'] }}</td>
                    <td class="md-5">{{ $guideRow['billingPolicies'] }}</td>
                    <td class="md-5">{{ $guideRow['observation1'] }}</td>
                    <td class="md-5">
                        @if($guideRow['status'] == 2)
                        <div class="canceled">{{ $guideRow['status_value'] }}</div>
                        @endif
                        <div>{{ $guideRow['observation2'] }}</div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    @if ($count < count($guideRow['months']) - 1)
    <span class="hidden">{{$count++}}</span>
    <hr>
    @endif
    @endforeach
    @if ($guide < count($data) - 1)
    <hr>
    @endif
@endforeach
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

