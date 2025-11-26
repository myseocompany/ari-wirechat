@extends('layout')

@section('content')
<style>
	.report-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 12px;
	}
	.report-chip {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 6px 10px;
		border-radius: 999px;
		background: #f3f4f6;
		font-size: 13px;
		color: #4b5563;
	}
	.table-report {
		width: 100%;
		border-collapse: collapse;
	}
	.table-report thead th {
		position: sticky;
		top: 0;
		background: #fff;
		box-shadow: 0 1px 0 rgba(0,0,0,0.06);
	}
.table-report th, .table-report td {
	padding: 10px 12px;
	border-bottom: 1px solid #e5e7eb;
}
.table-report {
	min-width: 960px;
}
.table-report th.rotate {
	height: 120px;
	vertical-align: bottom;
	padding: 4px 6px;
	text-align: right;
}
.rotate-header {
	writing-mode: vertical-rl;
	transform: rotate(180deg);
	white-space: nowrap;
	display: inline-block;
	font-weight: 600;
	color: #374151;
	text-align: right;
}
.table-report tbody tr:nth-child(even) {
	background: #f9fafb;
}
.table-report td.numeric {
	text-align: right;
		font-variant-numeric: tabular-nums;
	}
	.table-report tr.total-row {
		background: #eef2ff;
		font-weight: 600;
	}
	.actions-bar {
		display: flex;
		align-items: center;
		gap: 8px;
		margin: 8px 0 16px;
		flex-wrap: wrap;
	}
	.btn-secondary {
		padding: 6px 10px;
		border: 1px solid #d1d5db;
		border-radius: 6px;
		background: #fff;
		cursor: pointer;
	}
	.btn-secondary:hover {
		background: #f3f4f6;
	}
	.table-wrapper {
		width: 100%;
		overflow-x: auto;
	}
</style>

<div class="report-header">
	<div>
		<h1 style="margin:0;">Reporte de usuarios</h1>
		<div class="report-chip">
			<span>{{$filterLabel ?? 'Rango'}}</span>
			@if(isset($dateRange))
			<strong>{{$dateRange['from']}} → {{$dateRange['to']}}</strong>
			@endif
		</div>
	</div>
	<div class="actions-bar">
		<button type="button" class="btn-secondary" onclick="exportReportCSV()">Exportar CSV</button>
	</div>
</div>

<!--Inicio del formulario-->
<div>
  	<form action="/reports/users/" method="GET" id="filter_form">
  	<input type="hidden" name="search" id="search" @if(isset($request->search))value="{{$request->search}}"@endif>
 		 <select name="filter" class="custom-select" id="filter" onchange="update()">
        <option value="">Seleccione tiempo</option>
        <option value="0" @if ($request->filter == "0") selected="selected" @endif>hoy</option>
        <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>ayer</option>
        <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>esta semana</option>
        
        <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>semana pasada</option>
        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>mes pasado</option>
      	<option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>este mes</option>
      	<option value="-7" @if ($request->filter == "-7") selected="selected" @endif>ultimos 7 dias</option>
        <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>ultimos 30 dias</option>
        
      </select>
	  <select name="created_updated" class="custom-select" style="width:auto;">
		<option value="created" @if(($request->created_updated ?? 'created') === 'created') selected @endif>Por fecha de creación</option>
		<option value="updated" @if(($request->created_updated ?? '') === 'updated') selected @endif>Por fecha de actualización</option>
	  </select>
      <input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
      <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">

     
  
      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
  	</form>
  </div>

<!-- Fin del formulario -->




<div class="table-wrapper">
<table class="table-report" id="taskTable">
<thead class="thead-default">
	<tr>
		<th>Estado</th>
		@php
			// Filtra solo los estados con leads en alguna celda y precalcula conteos
			$statusData = [];
			$statusesWithLeads = [];
			foreach ($statuses as $status) {
				$rowTotal = 0;
				$statusCounts = [];
				foreach ($users as $user) {
					$count = $user->getTotalStatus($status->id, $request);
					$statusCounts[$user->id] = $count;
					$rowTotal += $count;
				}
				if ($rowTotal > 0) {
					$statusesWithLeads[] = $status;
					$statusData[$status->id] = $statusCounts;
				}
			}
		@endphp
		@foreach($users as $user)
			<th class="rotate"><span class="rotate-header">{{$user->name}}</span></th>
		@endforeach
		<th>Total</th>
	</tr>
</thead>
<tbody>
@foreach($statusesWithLeads as $status)
	@php $rowTotal = array_sum($statusData[$status->id]); @endphp
	<tr>
		<td>{{$status->name}}</td>
		@foreach($users as $user)
			@php
				$count = $statusData[$status->id][$user->id] ?? 0;
			@endphp
			<td class="numeric">{{$count > 0 ? $count : ''}}</td>
		@endforeach
		<td class="numeric"><strong>{{$rowTotal > 0 ? $rowTotal : ''}}</strong></td>
	</tr>
@endforeach

@php $grandTotal = 0; @endphp
<tr class="total-row">
	<td>Total</td>
	@foreach($users as $user)
		@php
			$colTotal = collect($statusesWithLeads)->sum(function($status) use ($statusData, $user) {
				return $statusData[$status->id][$user->id] ?? 0;
			});
			$grandTotal += $colTotal;
		@endphp
		<td class="numeric"><strong>{{$colTotal > 0 ? $colTotal : ''}}</strong></td>
	@endforeach
	<td class="numeric"><strong>{{$grandTotal > 0 ? $grandTotal : ''}}</strong></td>
</tr>

</tbody>
</table>
</div>

<script>
function exportReportCSV() {
	const table = document.getElementById('taskTable');
	if (!table) return;

	const rows = Array.from(table.querySelectorAll('tr'));
	const csv = rows.map(row => {
		const cells = Array.from(row.querySelectorAll('th,td'));
		return cells
			.map(cell => `"${(cell.innerText || '').trim().replace(/"/g, '""')}"`)
			.join(',');
	}).join('\n');

	const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
	const url = URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	link.download = 'reporte_usuarios.csv';
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	URL.revokeObjectURL(url);
}
</script>

@endsection
