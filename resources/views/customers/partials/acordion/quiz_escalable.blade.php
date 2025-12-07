@php
  $quizSummary = $quizSummary ?? null;
  $quizAnswers = $quizAnswers ?? collect();
  $quizQuestions = $quizQuestions ?? collect();
@endphp

<div class="card">
  <div class="card-header" id="headingQuizEscalable">
    <h3>
      <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseQuizEscalable" aria-expanded="true" aria-controls="collapseQuizEscalable">
        Quiz Escalable
      </button>
    </h3>
  </div>
  <div id="collapseQuizEscalable" class="collapse" aria-labelledby="headingQuizEscalable">
    <div class="card-body">
      @php
        $latestAt = $quizSummary?->created_at;
      @endphp

      @if($quizSummary)
        @php
          $summary = json_decode($quizSummary->value ?? '{}', true);
        @endphp
        <div class="mb-3">
          <strong>Fecha:</strong> {{ $quizSummary->created_at }}<br>
          @if(!empty($summary['final_score']))<strong>Puntaje final:</strong> {{ $summary['final_score'] }}<br>@endif
          @if(!empty($summary['stage']))<strong>Etapa:</strong> {{ $summary['stage'] }}<br>@endif
          @if(!empty($summary['completed_at']))<strong>Completado en:</strong> {{ $summary['completed_at'] }}<br>@endif
        </div>
      @else
        <p class="text-muted">Sin intentos guardados.</p>
      @endif

      @if($quizAnswers->count())
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>Pregunta</th>
                <th>Respuesta</th>
                <th>Score</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              @foreach($quizAnswers as $answer)
                @php
                  $payload = json_decode($answer->value ?? '{}', true);
                  $question = $quizQuestions[$answer->meta_data_id] ?? null;
                  $questionText = $question?->value ?? "Pregunta {$answer->meta_data_id}";
                  $answerText = null;
                  if ($question && isset($payload['answer_meta_id'])) {
                      $option = $question->CustomerMetaDataChildren->firstWhere('id', $payload['answer_meta_id']);
                      $answerText = $option?->value;
                  }
                @endphp
                <tr>
                  <td>{{ $questionText }}</td>
                  <td>{{ $answerText ?? ($payload['answer_meta_id'] ?? '') }}</td>
                  <td>{{ $payload['score'] ?? '' }}</td>
                  <td>{{ $answer->created_at }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
