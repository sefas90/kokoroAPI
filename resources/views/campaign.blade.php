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
    .data-table-info {
        width: 100%;
        font-size: 9px;
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
        font-size: 9px;
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
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
@foreach($data as $guide => $guideRow)
<div class="container">
    <div>
        <table class="data-table">
            <thead>
            <tr>
                <th class="md-3" rowspan="2"><img src="../public/kokoro_logo.jpg" width="80%"></th>
                <th class="md-3 center" rowspan="2"><h1>{{$guideRow['businessName']}}</h1></th>
                <th class="md-3">Numero de orden: {{$guideRow['order']}}</th>
            </tr>
            <tr>
                <th>Fecha de emision: {{$guideRow['date']}}</th>
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
                <td>Dur</td>
                @for ($i = 1; $i <= $guideRow['daysInMonth']; $i++)
                <td>{{ $i }}</td>
                @endfor
                <td>Spots</td>
                <td class="right"><div class="nowrap">C. Unitario</div>{{$guideRow['currency']}}</td>
                <td class="right"><div class="nowrap">Inversión</div>{{$guideRow['currency']}}</td>
            </tr>
            @foreach($guideRow['result'] as $key => $row)
            <tr>
                <td>{{ $row->media_name }}</td>
                <td>{{ $row->show }}</td>
                <td>{{ $row->hourIni }} {{$row->hourEnd}}</td>
                <td>{{ $row->material_name }}</td>
                <td>{{ $row->duration }}</td>
                @for ($i = 1; $i <= $guideRow['daysInMonth']; $i++)
                <td class="border-table">
                    @foreach($row->planing as $k => $r)
                    @if ($i == $r->day)
                    <span class="selected">{{ $r->times_per_day }}</span>
                    @endif
                    @endforeach
                </td>
                @endfor
                <td>{{ $row->spots }}</td>
                <td class="right">{{ number_format($row->unitCost * $guideRow['currencyValue'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($row->totalCost * $guideRow['currencyValue'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <br>
        <table class="data-table">
            <thead>
            <tr>
                <th class="md-5" colspan="2">Cliente/Agencia</th>
                <th class="md-5">Totales</th>
                <th class="md-5">Ins.</th>
                <th class="md-5 right"><div class="nowrap">Inversión {{$guideRow['currency']}}</div></th>
            </tr>
            <tr>
                <th rowspan="2" colspan="2">{{ $guideRow['user'] }}</th>
                <th>Total</th>
                <th>{{ $guideRow['totalSpots'] }}</th>
                <th class="md-5 right">{{ number_format($guideRow['totalMount'] * $guideRow['currencyValue'], 2, ',', '.') }}</th>
            </tr>
            <tr>
                <th>Total Orden</th>
                <th>{{ $guideRow['totalSpots'] }}</th>
                <th class="md-5 right">{{ number_format($guideRow['totalMount'] * $guideRow['currencyValue'], 2, ',', '.') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Facturar a:</td>
                <td>Direccion de facturacion:</td>
                <td colspan="3">Observaciones</td>
            </tr>
            <tr>
                <td>{{ $guideRow['billingPolicies'] }}</td>
                <td>{{ $guideRow['billingAddress'] }}</td>
                <td>{{ $guideRow['observation1'] }}</td>
                <td colspan="2">{{ $guideRow['observation2'] }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
@if ($guide < count($data)-1)
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

