@php
  $selectedTags = collect((array)($request->tag_id ?? []))->filter()->map(fn($id) => (int)$id)->all();
@endphp

<form action="/{{ $model->action }}" method="GET" id="filter_form" class="form-inline flex-wrap align-items-end">
  @if (Auth::user()->role_id !== 2)
    <select name="user_id" id="user_id" onchange="this.form.submit();" class="form-control mr-2 mb-2">
      <option value="">Usuario...</option>
      <option value="null" @if($request->user_id === "null") selected @endif>Sin asignar</option>
      @foreach($users as $user)
        <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
          {{ \Illuminate\Support\Str::limit($user->name, 18) }}
        </option>
      @endforeach
    </select>
  @endif
  <div class="input-group mb-2" style="min-width: 260px; max-width: 420px;">
    <div class="input-group-prepend">
      <span class="input-group-text bg-white text-muted">
        <i class="fa fa-search"></i>
      </span>
    </div>
    <input class="form-control" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" value="{{ $request->search }}">
  </div>
  <input type="hidden" name="sort" value="{{ $sort ?? $request->sort }}">
  <button class="btn btn-primary mb-2 ml-2" type="submit">Ir</button>
</form>
