<div class="flex h-full min-h-full w-full rounded-lg">
    <div class="relative h-full w-full overflow-y-auto md:w-[360px] lg:w-[400px] xl:w-[500px]">
        <div class="flex h-full w-full flex-col overflow-hidden border-r bg-white/95 p-3 transition-all dark:border-gray-700 dark:bg-gray-900">
            <header class="sticky top-0 z-10 w-full py-2">
                <section class="mb-4 flex items-center justify-between border-b pb-2">
                    <div class="flex items-center gap-2 truncate">
                        <h2 class="text-2xl font-bold dark:text-white">Mis conversaciones</h2>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ count($conversations) }}
                    </span>
                </section>

                <section>
                    <div class="grid grid-cols-12 items-center rounded-lg bg-gray-100 px-2 dark:bg-gray-800">
                        <label for="assigned-chats-search" class="col-span-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="h-5 w-5 dark:text-gray-300">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </label>
                        <input id="assigned-chats-search" name="assigned_chats_search" maxlength="100" type="search"
                            wire:model.live.debounce.400ms="search" placeholder="Buscar por phone o phone2"
                            autocomplete="off"
                            class="col-span-11 w-full border-0 bg-inherit text-sm outline-none focus:outline-none focus:ring-0 hover:ring-0 dark:text-white">
                    </div>
                    @if ((int) auth()->user()->role_id === 1)
                        <label class="mt-3 flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="adminOnlyMyAssigned" class="rounded border-slate-300 text-slate-700 focus:ring-slate-500">
                            Solo mis clientes asignados
                        </label>
                    @endif
                </section>
            </header>

            <main x-data wire:poll.10s @scroll.self.debounce="
                scrollTop = $el.scrollTop;
                scrollHeight = $el.scrollHeight;
                clientHeight = $el.clientHeight;

                if ((scrollTop + clientHeight) >= (scrollHeight - 1) && $wire.canLoadMore) {
                    await $nextTick();
                    $wire.loadMore();
                }
            " class="grow overflow-y-auto py-2">
                <div x-cloak wire:loading.delay.class.remove="hidden" wire:target="search" class="hidden transition-all duration-300">
                    <x-wirechat::loading-spin />
                </div>

                @if (count($conversations) > 0)
                    @php
                        $customerMorph = app(\App\Models\Customer::class)->getMorphClass();
                    @endphp

                    <ul wire:loading.delay.long.remove wire:target="search" class="grid w-full space-y-2 p-2">
                        @foreach ($conversations as $conversation)
                            @php
                                $customerParticipant = $conversation->participants->first(function ($participant) use ($customerMorph) {
                                    return $participant->participantable_type === $customerMorph;
                                });
                                $customer = $customerParticipant?->participantable;
                                $lastMessage = $conversation->lastMessage;
                                $displayName = $customer?->display_name ?? 'Conversacion';
                                $customerUrl = $customer ? route('customers.show', $customer->id) : null;
                            @endphp

                            <li wire:key="assigned-conversation-{{ $conversation->id }}">
                                <article
                                    wire:click="selectConversation({{ $conversation->id }})"
                                    class="relative w-full cursor-pointer rounded-sm px-2 py-3 transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $selectedConversationId === (int) $conversation->id ? 'bg-gray-50 dark:bg-gray-800' : '' }}"
                                >
                                    <div class="flex gap-4">
                                        <div class="shrink-0">
                                            <x-wirechat::avatar
                                                src="{{ $customer?->cover_url ?? null }}"
                                                class="h-12 w-12"
                                            />
                                        </div>

                                        <aside class="grid w-full grid-cols-12">
                                            <div class="col-span-12 w-full overflow-hidden border-b border-gray-100 p-1 pb-2 leading-5 dark:border-gray-700">
                                                <div class="mb-1 flex items-center justify-between gap-3">
                                                    <h6 class="truncate font-medium text-gray-900 dark:text-white">{{ $displayName }}</h6>
                                                </div>
                                                @if ($lastMessage)
                                                    <div class="flex items-center gap-2">
                                                        <p class="truncate text-sm text-gray-600 dark:text-white">
                                                            {{ $lastMessage->body !== '' ? $lastMessage->body : ($lastMessage->hasAttachment() ? '📎 Attachment' : '') }}
                                                        </p>
                                                        <span class="shrink-0 px-1 text-xs font-medium text-gray-800 dark:text-gray-100">
                                                            @if ($lastMessage->created_at->diffInMinutes(now()) < 1)
                                                                now
                                                            @else
                                                                {{ $lastMessage->created_at->shortAbsoluteDiffForHumans() }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="mt-2 flex items-center gap-3 text-xs">
                                                    @if ($customerUrl)
                                                        <a class="font-semibold text-blue-600 hover:text-blue-700" href="{{ $customerUrl }}">
                                                            Ver cliente
                                                        </a>
                                                    @endif
                                                    <span class="font-semibold text-slate-500">
                                                        Asesor: {{ $customer?->user?->name ?? 'Sin asesor' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </aside>
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>

                    @if ($canLoadMore)
                        @include('wirechat::livewire.chats.includes.load-more-button')
                    @endif
                @else
                    <div class="flex h-full w-full items-center justify-center">
                        <h6 class="font-bold text-gray-700 dark:text-white">No conversations yet</h6>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <main class="relative hidden h-full min-h-full w-full overflow-y-auto dark:border-gray-700 md:grid">
        @if ($selectedConversation)
            @php
                $customerMorph = app(\App\Models\Customer::class)->getMorphClass();
                $customerParticipant = $selectedConversation->participants->first(function ($participant) use ($customerMorph) {
                    return $participant->participantable_type === $customerMorph;
                });
                $customer = $customerParticipant?->participantable;
            @endphp
            <div class="grid h-full min-h-full grid-rows-[auto,1fr]">
                <header class="sticky top-0 z-10 border-b bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <x-wirechat::avatar src="{{ $customer?->cover_url ?? null }}" class="h-10 w-10" />
                            <div class="flex flex-col">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $customer?->display_name ?? 'Conversacion' }}
                                </h3>
                            </div>
                        </div>
                        @if ($customer)
                            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('customers.show', $customer->id) }}">
                                Ver cliente
                            </a>
                        @endif
                    </div>
                </header>

                <section class="flex h-full flex-col gap-3 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-950/40">
                    @forelse ($selectedMessages as $message)
                        @php
                            $senderName = $message->sendable?->display_name ?? 'Sistema';
                            $isMine = $message->sendable_type === auth()->user()->getMorphClass() && (int) $message->sendable_id === (int) auth()->id();
                            $body = trim((string) $message->body) !== '' ? $message->body : ($message->hasAttachment() ? '[Adjunto]' : '['.$message->type->value.']');
                        @endphp
                        <article class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm {{ $isMine ? 'ml-auto bg-blue-600 text-white' : 'bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                            <div class="mb-1 text-xs font-semibold {{ $isMine ? 'text-blue-100' : 'text-gray-500 dark:text-gray-300' }}">{{ $senderName }}</div>
                            <p class="text-sm">{{ $body }}</p>
                            <div class="mt-2 text-right text-[11px] {{ $isMine ? 'text-blue-100' : 'text-gray-400 dark:text-gray-300' }}">
                                {{ $message->created_at?->format('Y-m-d H:i') }}
                            </div>
                        </article>
                    @empty
                        <div class="m-auto text-sm font-medium text-gray-500 dark:text-gray-300">Sin mensajes para mostrar.</div>
                    @endforelse
                </section>
            </div>
        @else
            <div class="col-span-12 m-auto flex flex-col items-center justify-center gap-3 text-center">
                <h4 class="rounded-full bg-gray-50 p-2 px-3 font-semibold dark:bg-gray-800 dark:text-white dark:font-normal">
                    Selecciona una conversación para verla aquí
                </h4>
            </div>
        @endif
    </main>
</div>
