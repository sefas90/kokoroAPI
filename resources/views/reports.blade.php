<style type="text/css">
    .data-table {
        width: 100%;
    }
    table {
        border-collapse: collapse;
        border-spacing: 0;
        border: 1px solid #ddd;
    }
    th, td {
        text-align: left;
        padding: 5px;
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
                <th rowspan="2">KOKORO</th>
                <th rowspan="2">{{$data['businessName']}}</th>
                <th>Numero de orden: {{$data['order']}}</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>Fecha de emision: {{$data['date']}}</th>
            </tr>
            </thead>
        </table>
        <table class="data-table">
            <tbody>
            <tr>
                <td>Medio</td>
                <td>Programa</td>
                <td>Material</td>
                <td>Dur</td>
                <td>Spots</td>
                <td>Costo</td>
                <td>Inversion</td>
            </tr>
            @foreach($data['result'] as $key => $row)
            <tr>
                <td>{{ $row->media_name }}<td>
                <td>{{ $row->show }}<td>
                <td>{{ $row->material_name }}<td>
                <td>{{ $row->duration }}<td>
                <td>-<td>
                <td>{{ $row->cost }}<td>
                <td>-<td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Here's the magic. This MUST be inside body tag. Page count / total, centered at bottom of page --}}
<script type="text/php">
    if (isset($pdf)) {
        $text = "page {PAGE_NUM} / {PAGE_COUNT}";
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

