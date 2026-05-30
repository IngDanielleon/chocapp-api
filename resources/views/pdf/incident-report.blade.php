<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body        { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; margin: 0; padding: 0; }
        .header     { background: #E8302A; color: #fff; padding: 20px; text-align: center; }
        .header h1  { margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header p   { margin: 4px 0 0; font-size: 11px; opacity: 0.85; }
        .content    { padding: 16px 20px; }
        .section    { margin: 16px 0; border-left: 4px solid #E8302A; padding-left: 12px; }
        .section h2 { font-size: 13px; margin: 0 0 8px; color: #E8302A; }
        .grid       { width: 100%; border-collapse: collapse; }
        .grid td    { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .grid td:first-child { font-weight: bold; width: 40%; color: #555; }
        .photos     { margin-top: 8px; }
        .photo-row  { display: block; margin-bottom: 8px; }
        .photo-box  { display: inline-block; width: 160px; text-align: center; margin: 4px; vertical-align: top; }
        .photo-box img  { width: 160px; height: 110px; object-fit: cover; border-radius: 4px; }
        .photo-box span { font-size: 9px; color: #666; display: block; margin-top: 2px; }
        .badge          { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-reportado    { background: #fef3c7; color: #92400e; }
        .badge-en_revision  { background: #dbeafe; color: #1e40af; }
        .badge-finalizado   { background: #d1fae5; color: #065f46; }
        .badge-borrador     { background: #f3f4f6; color: #374151; }
        .footer     { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

<div class="header">
    <h1>ChocApp — Reporte Oficial de Accidente</h1>
    <p>Generado el {{ $generatedAt }} | ID: {{ $incident->id }}</p>
</div>

<div class="content">

<div class="section">
    <h2>Información del Incidente</h2>
    <table class="grid">
        <tr><td>Estado</td><td>
            <span class="badge badge-{{ strtolower($incident->status->value) }}">
                {{ $incident->status->label() }}
            </span>
        </td></tr>
        <tr><td>Título</td><td>{{ $incident->title }}</td></tr>
        <tr><td>Fecha</td><td>{{ $incident->incident_date->format('d/m/Y') }}</td></tr>
        <tr><td>Hora</td><td>{{ $incident->incident_time }}</td></tr>
        <tr><td>Dirección</td><td>{{ $incident->location_address }}</td></tr>
        <tr><td>Coordenadas</td><td>{{ $incident->latitude }}, {{ $incident->longitude }}</td></tr>
        <tr><td>Condición Climática</td><td>{{ $incident->weather_condition }}</td></tr>
        <tr><td>Estado de la Vía</td><td>{{ $incident->road_condition }}</td></tr>
        @if($incident->police_report_number)
        <tr><td>N° Denuncia Policial</td><td>{{ $incident->police_report_number }}</td></tr>
        @endif
    </table>
    <p style="margin-top:8px; line-height:1.5">{{ $incident->description }}</p>
</div>

<div class="section">
    <h2>Vehículo del Reportante</h2>
    <table class="grid">
        <tr><td>Placa</td><td>{{ $incident->vehicle->plate }}</td></tr>
        <tr><td>Marca / Modelo</td><td>{{ $incident->vehicle->brand }} {{ $incident->vehicle->model }}</td></tr>
        <tr><td>Año / Color</td><td>{{ $incident->vehicle->year }} / {{ $incident->vehicle->color }}</td></tr>
        <tr><td>Tipo</td><td>{{ $incident->vehicle->type instanceof \App\Enums\VehicleTypeEnum ? $incident->vehicle->type->label() : $incident->vehicle->type }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Conductor / Propietario</h2>
    <table class="grid">
        <tr><td>Nombre</td><td>{{ $incident->user->name }}</td></tr>
        <tr><td>Documento</td><td>{{ $incident->user->id_type }}: {{ $incident->user->id_number }}</td></tr>
        <tr><td>Teléfono</td><td>{{ $incident->user->phone_number }}</td></tr>
        <tr><td>Correo</td><td>{{ $incident->user->email }}</td></tr>
    </table>
</div>

@if($incident->thirdParties->count() > 0)
<div class="section">
    <h2>Terceros Implicados ({{ $incident->thirdParties->count() }})</h2>
    @foreach($incident->thirdParties as $i => $party)
    <p style="font-weight:bold; margin:8px 0 4px;">Tercero {{ $i + 1 }} — {{ $party->party_type }}</p>
    <table class="grid" style="margin-bottom:8px">
        @if($party->plate)<tr><td>Placa</td><td>{{ $party->plate }}</td></tr>@endif
        @if($party->brand)<tr><td>Marca / Modelo</td><td>{{ $party->brand }} {{ $party->model }}</td></tr>@endif
        @if($party->color)<tr><td>Color</td><td>{{ $party->color }}</td></tr>@endif
        @if($party->driver_name)<tr><td>Conductor</td><td>{{ $party->driver_name }}</td></tr>@endif
        @if($party->driver_id)<tr><td>Documento</td><td>{{ $party->driver_id }}</td></tr>@endif
        @if($party->driver_phone)<tr><td>Teléfono</td><td>{{ $party->driver_phone }}</td></tr>@endif
        @if($party->insurance_company)<tr><td>Aseguradora</td><td>{{ $party->insurance_company }} — Póliza: {{ $party->insurance_policy }}</td></tr>@endif
    </table>
    @endforeach
</div>
@endif

@if($incident->photos->count() > 0)
<div class="section">
    <h2>Evidencia Fotográfica ({{ $incident->photos->count() }} fotos)</h2>
    <div class="photos">
        @foreach($incident->photos as $photo)
        <div class="photo-box">
            <img src="{{ $photo->image_url }}" alt="{{ $photo->angle }}">
            <span>{{ $photo->angle }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="footer">
    <p>Este reporte fue generado automáticamente por ChocApp y puede ser utilizado como evidencia legal.</p>
    <p>ChocApp — Tu aliado en el camino | chocapp.reddantechnology.com | {{ $generatedAt }}</p>
</div>

</div>
</body>
</html>
