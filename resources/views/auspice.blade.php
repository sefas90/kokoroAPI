<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>
<span class="hidden">{{$count = 0}}</span>
@foreach($data['months'] as $months => $monthRow)
    <div class="container">
        <div>
        <table class="data-table">
            <thead>
            <tr>
                <th class="md-3" rowspan="2"><img src="../public/logo_kokoro.svg" width="80%"></th>
                <th class="md-3 center" rowspan="2"><h1>{{$data['businessName']}}</h1></th>
                <th class="md-3" colspan="2">Numero de orden: {{$data['order']}}</th>
            </tr>
            <tr>
                <th>Fecha de emision:<br>{{$data['date']}}</th>
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
        <table class="data-table-info">
            <tbody>
            <tr>
                <td>Medio</td>
                <td>Programa</td>
                <td>Horario</td>
                <td>Material</td>
                <td class="glued">Dur (seg.)</td>
                @for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $monthRow, $data['year']); $i++)
                <td class="days">{{ $i }}</td>
                @endfor
                <td class="glued">Spots</td>
                <td class="right"><div class="nowrap">Inversión</div>{{$data['currency']}}</td>
            </tr>
            @foreach($data['result'] as $key => $row)
            @if(isset($row->planing[$monthRow]))
            <tr class="text-all">
                <td>{{ $row->media_name }}</td>
                <td>{{ $row->show }}</td>
                <td>{{ substr($row->hourIni,0,-3) }} {{ substr($row->hourEnd,0,-3) }}</td>
                <td>{{ $row->material_name }}</td>
                <td>{{ $row->duration }}</td>
                @for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $monthRow, $data['year']); $i++)
                <td class="border-table">
                    @foreach($row->planing[$monthRow] as $k => $r)
                        @if ($i == $r->day)
                            <span class="selected">{{ $r->times_per_day }}</span>
                        @endif
                    @endforeach
                </td>
                @endfor
                <td>{{ $row->spots }}</td>
                <td class="right">{{ number_format($row->totalCost / $data['currencyValue'], 2, ',', '.') }}</td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
        <br>
            <div>
                <br>
            </div>
        <table class="data-table">
            <thead>
            <tr>
                <th class="md-5">Responsable:</th>
                <th class="md-5">Cliente:</th>
                <th class="md-5">Totales:</th>
                <th class="md-5">Ins:</th>
                <th class="md-5 right"><div class="nowrap">Inversión {{$data['currency']}}</div></th>
            </tr>
            <tr>
                <th rowspan="2">{{ $data['user'] }}</th>
                <th rowspan="2">{{ $data['client'] }}</th>
                <th>Total:</th>
                <th>{{ $data['totalSpots'] }}</th>
                <th class="right">{{ number_format($data['totalMount'] / $data['currencyValue'], 2, ',', '.') }}</th>
            </tr>
            <tr>
                <th>Total Orden:</th>
                <th>{{ $data['totalSpots'] }}</th>
                <th class="right">{{ number_format($data['totalMount'] / $data['currencyValue'], 2, ',', '.') }}</th>
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
                <td>{{ $data['billingToName'] }}<br>{{ $data['billingToNit'] }}</td>
                <td>{{ $data['billingAddress'] }}</td>
                <td>{{ $data['billingPolicies'] }}</td>
                <td>{{ $data['observation1'] }}</td>
                <td>
                    @if($data['status'] == 2)
                        <div class="canceled">{{ $data['status_value'] }}</div>
                    @endif
                    <div>{{ $data['observation2'] }}</div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    </div>
    @if ($count < count($data['months']) - 1)
    <span class="hidden">{{$count++}}</span>
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
