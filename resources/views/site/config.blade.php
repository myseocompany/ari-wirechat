@extends('layout')

@section('content')

<h1>Configuración </h1>
<div class="list-group">
	<a href="/customer_statuses" class="list-group-item">Estados de los Clientes</a>
</div>
<div class="list-group">
	<a href="/e-mails" class="list-group-item">Emails</a>
</div>
<div class="list-group">
	<a href="/audiences" class="list-group-item">Audiencias</a>
</div>
<div class="list-group">
	<a href="/satisfaction" class="list-group-item">Satisfacción</a>
</div>
<div class="list-group">
	<a href="/campaigns" class="list-group-item">Campañas</a>
</div>
<div class="list-group">
	<a href="/users" class="list-group-item">Usuarios</a>
</div>
<div class="list-group">
	<a href="/roles" class="list-group-item">Roles</a>
</div>
<div class="list-group">
	<a href="/request-logs" class="list-group-item">Logs de solicitudes API</a>
</div>
<div class="list-group">
	<a href="/customers/phase/4" class="list-group-item">PQR</a>
</div>
<div class="list-group">
	<a href="/customers/phase/2" class="list-group-item">Posventa</a>
</div>
<div class="list-group">
	<a href="/faq" class="list-group-item">Preguntas Frecuentes</a>
</div>
<div class="list-group">
    <div class="list-group-item">
        <h3 class="h5 mb-1">Etiquetas de clientes</h3>
        <p class="mb-2">Administra las etiquetas disponibles para clasificar clientes.</p>
        <a href="{{ route('tags.index') }}" class="btn btn-sm btn-primary">Gestionar etiquetas</a>
    </div>
</div>
<div class="list-group">
    <a href="/products" class="list-group-item">Productos</a>
</div>
<div class="list-group">
    <a href="/whatsapp-accounts" class="list-group-item">Cuentas de WhatsApp</a>
</div>
@endsection
