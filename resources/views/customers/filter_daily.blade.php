<form action="/{{ $model->action }}" method="GET" id="filter_form" class="flex flex-col gap-4">
  <input type="hidden" name="sort" value="{{ $sort ?? $request->sort }}">
  @php $canViewAllCustomers = auth()->user()?->canViewAllCustomers() ?? false; @endphp

  @if ($canViewAllCustomers)
    <div class="flex flex-col gap-2">
      <label for="user_id" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Usuario</label>
      <select name="user_id" id="user_id" onchange="this.form.submit();" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
        <option value="">Todos</option>
        <option value="null" @if($request->user_id === "null") selected @endif>Sin asignar</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
            {{ \Illuminate\Support\Str::limit($user->name, 18) }}
          </option>
        @endforeach
      </select>
    </div>
  @else
    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
    <div class="flex flex-col gap-2">
      <label class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Usuario</label>
      <div class="w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500">
        Solo tus clientes
      </div>
    </div>
  @endif

  <div class="flex flex-col gap-2">
    <label for="name_" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Buscar</label>
    <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm">
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[color:var(--ds-cloud)] text-[color:var(--ds-navy)]">
        <i class="fa fa-search"></i>
      </span>
      <input class="w-full text-sm text-slate-700 focus:outline-none" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" value="{{ $request->search }}" autocomplete="off">
    </div>
  </div>

  <div class="flex flex-wrap gap-2">
    <button class="rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]" type="submit">Aplicar filtros</button>
    <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600" href="{{ url($model->action) }}">Limpiar</a>
  </div>
</form>
