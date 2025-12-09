<p>Hola {{ $action->customer->user->name }}</p>

<p>Cliente: {{ $action->customer->name }}</p>

<p>Acción: {{ $action->note }}</p>

<p>Vence el: {{ optional($action->due_date)->format('Y-m-d H:i') }}</p>

@if(!empty($action->url))
  <p>Puedes revisarla aquí: <a href="{{ $action->url }}">{{ $action->url }}</a></p>
@endif
