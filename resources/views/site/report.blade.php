@extends('layout')

@section('content')

<h1>Reportes </h1>
<div class="list-group">
	<a href="/actions" class="list-group-item">Reporte de acciones</a>
</div>
<div class="list-group">
	<a href="/reports/users" class="list-group-item">Reporte de usuarios</a>
</div>
<div class="list-group">
	<a href="/reports/customers_time" class="list-group-item">Reporte de clientes</a>
</div>
<div class="list-group">
	<a href="/reports/users/customer/status" class="list-group-item">Reporte de usuarios por estados</a>
</div>
<div class="list-group">
	<a href="/reports/users/customer/actions" class="list-group-item">Reporte de usuarios por acciones</a>
</div>
<div class="list-group">
	<a href="/reports/views/customers_followup" class="list-group-item">Reporte de seguimientos</a>
</div>
<div class="list-group">
	<a href="https://lookerstudio.google.com/u/0/reporting/04988fbd-8e80-4f63-bfc8-a3b6f4c004d9/page/uIHMD" 
	class="list-group-item" target="_blank">Reporte satisfacción</a>
</div>

<div class="list-group">
	<a href="https://datastudio.google.com/u/0/reporting/79b0cc9b-f177-46bb-af07-12f28aae46c7/page/p_n5nyerzhvc?s=oHMv0PSK7Xo" 
	class="list-group-item" target="_blank">Reporte general</a>
</div>
@endsection