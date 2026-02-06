<div class="space-y-4">
  @foreach ($model as $action)
    @php
      $customer = $action->customer;
      $status = $customer->status ?? null;
    @endphp

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-4 relative flex justify-between items-start">

      <div class="w-full">
        {{-- Estado de acción --}}
        @if($action->isPending() && !empty($action->due_date))
          <span class="text-xs text-red-500 font-semibold">⏰ Programado para: {{ \Carbon\Carbon::parse($action->due_date)->format('d M Y H:i') }}</span>
        @endif

        {{-- Nota principal --}}
        <div class="text-lg font-bold text-gray-800 mt-1">
          {{ $action->note }}
        </div>
        @if(!empty($action->url))
          @php
            $audioUrl = trim($action->url);
            if (Str::startsWith($audioUrl, '//')) {
              $audioUrl = 'https:'.$audioUrl;
            }
            if (! Str::startsWith($audioUrl, ['http://', 'https://'])) {
              $audioUrl = 'https://'.$audioUrl;
            }
            $lowerUrl = Str::lower($audioUrl);
            $isAudio = Str::contains($lowerUrl, [
              '.mp3', '.wav', '.ogg', '.oga', '.m4a', '.m4b', '.webm',
              'backend.channels.app/recording-files/',
            ]);
            $mime = Str::contains($lowerUrl, '.mp3') ? 'audio/mpeg' :
              (Str::contains($lowerUrl, '.wav') ? 'audio/wav' :
              (Str::contains($lowerUrl, ['.ogg', '.oga']) ? 'audio/ogg' :
              (Str::contains($lowerUrl, ['.m4a', '.m4b']) ? 'audio/mp4' :
              (Str::contains($lowerUrl, '.webm') ? 'audio/webm' : null))));
          @endphp
          @if($isAudio)
            <audio controls class="mt-2" @if(! $mime) src="{{ $audioUrl }}" @endif>
              @if($mime)
                <source src="{{ $audioUrl }}" type="{{ $mime }}">
              @endif
              Tu navegador no soporta el audio.
            </audio><br>
          @endif
        @endif
        @if($action->isCall())
          @php $transcription = $action->transcription; @endphp
          <div class="mt-2 space-y-2">
            @if($transcription && $transcription->status === 'done' && $transcription->transcript_text)
              <div class="whitespace-pre-line rounded-md border border-slate-200 bg-slate-50 p-2 text-sm text-slate-700">
                {{ $transcription->transcript_text }}
              </div>
            @elseif($transcription && in_array($transcription->status, ['pending', 'processing'], true))
              <div class="text-xs text-slate-500">Transcribiendo...</div>
            @elseif($transcription && $transcription->status === 'error')
              <div class="text-xs text-red-600">
                Error al transcribir: {{ $transcription->error_message ?? 'Error desconocido' }}
              </div>
            @endif

            @if(Auth::check() && Auth::user()->role_id == 1)
              <form method="POST" action="{{ route('actions.transcribe', $action) }}">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md border border-blue-600 px-2 py-1 text-xs font-semibold text-blue-600 transition hover:bg-blue-50">
                  Transcribir
                </button>
              </form>
            @endif
          </div>
        @endif
        @if($action->isPending() && $action->next_action_created_at)
          <div class="mt-1 text-xs text-slate-500">
            Última acción: {{ \Carbon\Carbon::parse($action->next_action_created_at)->format('d M Y H:i') }}
            @if($action->next_action_note)
              · {{ $action->next_action_note }}
            @endif
          </div>
        @endif

        {{-- Info acción --}}
        <div class="text-sm text-gray-600">
          {{ $action->getTypeName() }} 
          @if($action->creator)
            • Creado por {{ $action->creator->name }}
          @endif
        </div>
        @if(isset($customer))
        {{-- Info cliente --}}
        <div class="mt-2 text-sm">
          <a href="/customers/{{ $action->customer_id }}/show" class="text-blue-600 font-semibold">
            {{ $customer->name }}
          </a>
          @if($status)
            <span class="ml-2 inline-block px-2 py-0.5 text-xs text-white rounded" style="background-color: {{ $status->color }}">
              {{ $status->name }}
            </span>
          @endif

          <div class="text-gray-500 text-xs mt-1">
            📞 <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a> • ✉️ {{ $customer->email }}
            @if($customer->project) • 🏡 {{ $customer->project->name }} @endif
            @if($customer->source) • 🎯 {{ $customer->source->name }} @endif
          </div>
        </div>
        @endif

      </div>

      {{-- Checkbox para completar --}}
      @if($action->isPending())
        <div class="ml-4 flex-shrink-0">
          <input
            type="checkbox"
            data-toggle="modal"
            data-id="{{ $action->id }}"
            data-note="{{ $action->note }}"
            data-type-id="{{ $action->type_id }}"
            data-status-id="{{ optional($customer)->status_id }}"
            data-customer-name="{{ optional($customer)->name }}"
            class="w-6 h-6 rounded-full border-2 border-blue-500 text-blue-600 focus:ring-2 focus:ring-blue-400 checked:bg-blue-600 checked:border-transparent"
            onclick="this.checked=false"
          >
        </div>
      @endif
    </div>
  @endforeach
</div>
