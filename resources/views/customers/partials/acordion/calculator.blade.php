@php
  $summaryData = $calculatorSummaryData ?? [];
  $answers = $calculatorAnswers ?? collect();
@endphp

<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
  <div class="border-b border-slate-200 px-4 py-3" id="headingCalculator">
    <h3 class="text-base font-semibold text-slate-900">Calculadora</h3>
  </div>
  <div class="space-y-3 px-4 py-3 text-sm text-slate-700">
      @if($calculatorSummary)
        <div class="mb-3">
          <div><strong>Fecha:</strong> {{ $calculatorSummary->created_at }}</div>
          @if(isset($summaryData['stage']))
            <div><strong>Etapa:</strong> {{ $summaryData['stage'] }}</div>
          @endif
        </div>
      @else
        <p class="mb-3 text-slate-500">Sin resultados de calculadora aún.</p>
      @endif

      @if($answers->count())
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600" style="width: 40%;">Preguntas</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Respuestas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              @foreach($answers as $answer)
                @php
                  $data = is_array($answer->value) ? $answer->value : json_decode($answer->value, true);
                  $questionMeta = $calculatorQuestions->get($answer->meta_data_id);
                  $questionText = $data['question_text'] ?? ($questionMeta->value ?? ($answer->question_value ?? ('Pregunta #' . $answer->meta_data_id)));
                  $answerText = $data['answer_text'] ?? ($data['answer_meta_id'] ?? '');
                  $comment = $data['comment'] ?? null;
                @endphp
                <tr>
                  <th class="px-3 py-2 font-semibold text-slate-900">{{ $questionText }}</th>
                  <td class="px-3 py-2">
                    {{ $answerText }}
                    @if($comment)
                      <div class="text-xs text-slate-500">{{ $comment }}</div>
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
