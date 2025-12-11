@php
  $summaryData = $calculatorSummaryData ?? [];
  $answers = $calculatorAnswers ?? collect();
@endphp

<div class="card">
  <div class="card-header" id="headingCalculator">
    <h3>
      <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseCalculator" aria-expanded="false" aria-controls="collapseCalculator">
        Calculadora
      </button>
    </h3>
  </div>

  <div id="collapseCalculator" class="collapse" aria-labelledby="headingCalculator">
    <div class="p-3">
      @if($calculatorSummary)
        <div class="mb-3">
          <div><strong>Fecha:</strong> {{ $calculatorSummary->created_at }}</div>
          @if(isset($summaryData['stage']))
            <div><strong>Etapa:</strong> {{ $summaryData['stage'] }}</div>
          @endif
        </div>
      @else
        <p class="mb-3 text-muted">Sin resultados de calculadora aún.</p>
      @endif

      @if($answers->count())
        <div class="table">
          <table class="table table-striped">
            <thead>
              <tr>
                <th style="width: 40%;">Preguntas</th>
                <th>Respuestas</th>
              </tr>
            </thead>
            <tbody>
              @foreach($answers as $answer)
                @php
                  $data = is_array($answer->value) ? $answer->value : json_decode($answer->value, true);
                  $questionMeta = $calculatorQuestions->get($answer->meta_data_id);
                  $questionText = $data['question_text'] ?? ($questionMeta->value ?? ($answer->question_value ?? ('Pregunta #' . $answer->meta_data_id)));
                  $answerText = $data['answer_text'] ?? ($data['answer_meta_id'] ?? '');
                  $comment = $data['comment'] ?? null;
                @endphp
                <tr>
                  <th>{{ $questionText }}</th>
                  <td>
                    {{ $answerText }}
                    @if($comment)
                      <div class="text-muted"><small>{{ $comment }}</small></div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
