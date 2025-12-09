@php
  $selectedTags = collect((array)($request->tag_id ?? []))->filter()->map(fn($id) => (int)$id)->all();
@endphp

<form action="/{{ $model->action }}" method="GET" id="filter_form" class="mb-3 bg-white rounded shadow-sm p-4">
  <div class="flex flex-col lg:flex-row lg:items-end lg:space-x-4 space-y-3 lg:space-y-0">
    @if (Auth::user()->role_id !== 2)
      <div class="w-full lg:w-1/3">
        <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-1">Asesor</label>
        <select name="user_id" id="user_id" onchange="this.form.submit();" class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Usuario...</option>
          <option value="null" @if($request->user_id === "null") selected @endif>Sin asignar</option>
          @foreach($users as $user)
            <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
              {{ \Illuminate\Support\Str::limit($user->name, 18) }}
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <div class="w-full lg:flex-1">
      <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Buscar</label>
      <div class="flex space-x-2">
        <input type="text" name="search" id="search" class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $request->search }}" placeholder="Nombre, correo, teléfono...">
        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">Filtrar</button>
        <a href="/{{ $model->action }}" class="px-3 py-2 text-sm font-semibold text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">Limpiar</a>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div class="mb-2 font-semibold text-sm text-gray-700">Etiquetas</div>
    <div class="flex flex-wrap gap-2">
      @foreach($allTags as $tagOption)
        @php
          $checked = in_array($tagOption->id, $selectedTags);
          $color = $tagOption->color ?: '#e2e8f0';
        @endphp
        <label class="inline-flex items-center space-x-2 px-3 py-2 rounded-md border text-sm cursor-pointer" style="border-color: {{ $checked ? $color : '#e2e8f0' }}; background-color: {{ $checked ? $color : '#fff' }};">
          <input
            type="checkbox"
            name="tag_id[]"
            value="{{ $tagOption->id }}"
            class="form-checkbox text-indigo-600"
            @checked($checked)
            onchange="this.form.submit();"
          >
          <span>{{ $tagOption->name }}</span>
        </label>
      @endforeach
    </div>
  </div>
</form>
