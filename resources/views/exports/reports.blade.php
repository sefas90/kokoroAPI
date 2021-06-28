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
            <th>Semana del Mes</th>
            <th>Soporte</th>
            <th>Grupo Publicitario</th>
            <th>Región</th>
            <th>Representante</th>
            <th>Presupuesto</th>
            <th>Orden</th>
            <th>Spots</th>
            <th>Inversión + IMP BOB</th>
            <th>Divisa + IMP {{$datas[1]->symbol}}</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($datas[0] as $data)
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
            <td>{{ $data->week }}</td>
            <td>{{ $data->row->media_name }}</td>
            <td>{{ $data->row->business_name }}</td>
            <td>{{ $data->row->city }}</td>
            <td>{{ $data->row->representative }}</td>
            <td>{{ $data->row->budget }}</td>
            <td>{{ $data->row->order_number }}.{{ $data->row->version }}</td>
            @if (!empty($data->times_per_day))
            <td>{{ $data->times_per_day }}</td>
            @else
            <td>0</td>
            @endif
            @if (!empty($data->times_per_day))
            <td>{{ number_format($data->times_per_day * $data->cost, 2, '.', '') }}</td>
            @else
            <td>0</td>
            @endif
            <td>{{ number_format($data->badge, 2, '.', '') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
