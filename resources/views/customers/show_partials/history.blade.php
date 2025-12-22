<h2 class="mt-6 text-lg font-semibold text-slate-900">Historial</h2>

@php
    $timelineItems = $timelineItems ?? collect($histories)->map(function ($history) {
        return [
            'type' => 'history',
            'date' => $history->updated_at,
            'model' => $history,
        ];
    });
    $historyOwnerMap = $historyOwnerMap ?? [];
@endphp

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    @foreach($timelineItems as $index => $item)
        @if($item['type'] === 'history')
            @php
                $history = $item['model'];
                $ownerInfo = $historyOwnerMap[$history->id] ?? null;
                $nextOwner = $ownerInfo['next_owner_id'] ?? null;
                $nextOwnerName = $ownerInfo['next_owner_name'] ?? 'Sin asignar';
                $isReassigned = $nextOwner !== null && $nextOwner != $history->user_id;
                $timestamp = \Carbon\Carbon::parse($history->updated_at);
            @endphp

            <div class="border-b border-slate-200 py-3">
                <div class="mb-1 text-xs text-slate-500">
                    <i class="fa fa-clock-o"></i>
                    {{ $timestamp->format('d/m/Y H:i') }}
                    <span class="text-slate-400">({{ $timestamp->diffForHumans() }})</span>
                </div>

                @if(isset($history->user) && !empty($history->user_id))
                    <span>
                        <i class="fa fa-user"></i> Propietario:
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">{{$history->user->name}}</span>
                    </span>

                    @if($isReassigned)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                            <i class="fa fa-exchange"></i> Reasignado (antes: {{$nextOwnerName}})
                        </span>
                    @endif
                @else
                    <span>
                        <i class="fa fa-user-slash"></i> Sin asignar
                    </span>
                @endif

                @if(isset($history->updated_user))
                    <br>
                    <span class="text-slate-700">
                        <i class="fa fa-pencil"></i> Cambiado por: {{$history->updated_user->name}}
                    </span>
                @endif

                <span>
                    <i class="fa fa-flag"></i> Estado:
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background-color: {{ isset($history->status) ? $history->status->color : 'gray' }}">
                        {{ isset($history->status) ? $history->status->name : $history->status_id }}
                    </span>
                </span>
            </div>
        @elseif($item['type'] === 'chat')
            @php
                $message = $item['model'];
                $conversationName = optional($message->conversation->group)->name ?: 'Chat '.$message->conversation_id;
                $senderName = $message->sendable?->name ?? 'Sistema';
                $isCustomer = $message->sendable_type === $model->getMorphClass() && $message->sendable_id === $model->id;
                $timestamp = \Carbon\Carbon::parse($message->created_at);
                $body = trim((string) ($message->body ?? ''));
                $body = $body !== '' ? $body : 'Mensaje sin texto';
            @endphp

            <div class="border-b border-slate-200 py-3">
                <div class="mb-1 text-xs text-slate-500">
                    <i class="fa fa-commenting"></i>
                    {{ $timestamp->format('d/m/Y H:i') }}
                    <span class="text-slate-400">({{ $timestamp->diffForHumans() }})</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-700">
                    <span class="inline-flex items-center rounded-full bg-slate-900 px-2 py-0.5 text-[11px] font-semibold text-white">WireChat</span>
                    <span class="font-semibold text-slate-900">{{ $senderName }}</span>
                    <span class="text-xs text-slate-500">en {{ $conversationName }}</span>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $isCustomer ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $isCustomer ? 'Entrante' : 'Saliente' }}
                    </span>
                </div>
                <div class="mt-1 text-sm text-slate-700">
                    {{ \Illuminate\Support\Str::limit($body, 160) }}
                </div>
                <a href="/chats/{{ $message->conversation_id }}" class="mt-2 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-700">
                    Ver conversación
                </a>
            </div>
        @endif
    @endforeach
</div>
