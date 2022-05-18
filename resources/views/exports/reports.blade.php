<table>
    <thead>
        <tr>
            <th>Anunciante</th>
            <th>Director</th>
            <th>Compañía</th>
            <th>Plan</th>
            <th>Campaña</th>
            <th>Medio</th>
            <th>Pauta</th>
            <th>Producto</th>
            <th>Versión</th>
            <th>Año</th>
            <th>Mes</th>
            <th>Semana del Año</th>
            <th>Soporte</th>
            <th>Grupo Publicitario</th>
            <th>Región</th>
            <th>Representante</th>
            <th>Presupuesto</th>
            <th>Orden</th>
            <th>Spots</th>
            <th>Inversión + IMP BOB</th>
            <th>Tipo de cambio</th>
            <th>Divisa + IMP {{$datas[1]->symbol}}</th>
            <th>Nro. Factura</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($datas[0] as $data)
        @if($data && $data->row)
            <tr>
                <td>{{ $data->row->client_name }}</td>
                <td>{{ $data->user }}</td>
                <td>KOKORO S.R.L.</td>
                <td>{{ $data->row->plan_name }}</td>
                <td>{{ $data->row->campaign_name }}</td>
                <td>{{ $data->row->media_type }}</td>
                <td>{{ $data->row->guide_name }}</td>
                <td>{{ $data->row->product }}</td>
                <td>{{ $data->row->material_name }}</td>
                <td>{{ $data->year }}</td>
                <td>{{ $data->month }}</td>
                <td>{{ $data->weekOfYear }}</td>
                <td>{{ $data->row->media_name }}</td>
                <td>{{ $data->row->business_name }}</td>
                <td>{{ $data->row->city }}</td>
                <td>{{ $data->row->representative }}</td>
                <td>{{ $data->row->budget }}</td>
                <td></td>
                @if (!empty($data->times_per_day))
                    <td>{{ $data->times_per_day }}</td>
                @else
                    <td>0</td>
                @endif
                <td>{{ number_format($data->totalCost / $data->passes * $data->times_per_day, 2, '.', '') }}</td>
                <td>{{ number_format($data->currencyValue, 2, '.', '') }}</td>
                <td>{{ number_format($data->totalCost / $data->passes * $data->times_per_day / $data->currencyValue, 2, '.', '') }}</td>
                <td>{{ $data->row->billing_number }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
