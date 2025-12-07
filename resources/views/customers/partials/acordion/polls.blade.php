@php
    // Gracefully handle missing data
    $metas = collect($metas ?? []);
@endphp

<div class="card">
    <div class="card-header" id="headingSurveys">
        <h3>
            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseSurveys" aria-expanded="true" aria-controls="collapseSurveys">
                Encuestas
            </button>
        </h3>
    </div>
    <div id="collapseSurveys" class="collapse" aria-labelledby="headingSurveys">
        @if($metas->isEmpty())
            <div class="p-3 text-muted">Sin respuestas de encuesta.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pregunta</th>
                            <th>Respuesta</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metas as $item)
                            <tr>
                                <td>{{ $item->name ?? 'Pregunta' }}</td>
                                <td>{{ $item->value ?? '' }}</td>
                                <td>{{ $item->created_at ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
