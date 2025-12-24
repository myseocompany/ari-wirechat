@extends('layout')
@section('content')
<h2>Detalle de campaña: {{ $campaign->name }}</h2>

@if($questions->isEmpty())
	<p>Esta campaña no tiene preguntas configuradas.</p>
@elseif($responsesByCustomer->isEmpty())
	<p>No hay respuestas registradas para esta campaña.</p>
@else
	<p>Clientes que respondieron: {{ $responsesByCustomer->count() }}</p>
	<div class="table-responsive-sm">
		<table class="table">
			<thead>
				<tr>
					<th>Cliente</th>
					<th>Teléfono</th>
					@foreach($questions as $question)
						<th>{{ $question->value }}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach($responsesByCustomer as $response)
					<tr>
						<td>
							<a href="{{ route('customers.show', $response['customer']->customer_id) }}">
								{{ $response['customer']->name ?? 'Sin nombre' }}
							</a>
						</td>
						<td>{{ $response['customer']->phone ?: $response['customer']->phone2 }}</td>
						@foreach($questions as $question)
							@php($answer = $response['answers']->get($question->id))
							<td>{{ $answer?->value ?? '-' }}</td>
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif
@endsection
