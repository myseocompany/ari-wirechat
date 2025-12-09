<?php
function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
}

// Normaliza el customer en caso de que llegue un paginator o nulo
if ($customer instanceof \Illuminate\Pagination\LengthAwarePaginator) {
    $customer = $customer->first();
}
$authUser = auth()->user();
$limited = $customer instanceof \App\Models\Customer ? ! $customer->hasFullAccess($authUser) : false;
?>

@if($customer instanceof \App\Models\Customer)
  @include('customers.index_partials.customer_header')
  
    @if($limited)
      <div class="alert alert-info mt-3">Acceso restringido: solo datos básicos visibles.</div>
    @else
      <div class="row">
        <div class="col-md-4">
          <div id="customer_show">
            <div><a href="#" onclick="searchInGoogle('{{$customer->name}}')">Buscar en Google</a></div>
            <div>
              @if(isset($customer->rd_public_url))
                <a href="{{$customer->rd_public_url}}" target="_blank">Buscar en RD Station</a>
              @endif
            </div>
            @include('customers.index_partials.contact')
            <br>

            {{-- Etiquetas del cliente --}}
            <div class="mt-3">
              <h3 class="text-sm font-semibold">Etiquetas</h3>
              <div class="mb-2 tags-badges" id="customer-tags-badges">
                @if($customer->tags && $customer->tags->count())
                  @foreach($customer->tags as $tag)
                    <span class="px-2 py-1 rounded-full text-xs font-semibold mr-2 mb-1 d-inline-block" style="background-color: {{ $tag->color ?? '#e2e8f0' }};">
                      {{ $tag->name }}
                    </span>
                  @endforeach
                @else
                  <span class="text-muted">Sin etiquetas</span>
                @endif
              </div>

              @if(isset($allTags) && $allTags->count())
                <form
                  method="POST"
                  action="{{ route('customers.tags.update', $customer) }}"
                  id="customer-tags-form-index"
                  class="customer-tags-form"
                  data-tags-badges="#customer-tags-badges"
                  data-tags-feedback="#tags-feedback-index">
                  @csrf
                  <div class="grid grid-cols-2 gap-2">
                    @foreach($allTags as $tagOption)
                      @php
                        $checked = $customer->tags->contains($tagOption->id);
                        $color = $tagOption->color ?: '#edf2f7';
                      @endphp
                      <label class="flex items-center gap-2 px-3 py-2 rounded border cursor-pointer text-sm" style="border-color: {{ $checked ? $color : '#e2e8f0' }}; background-color: {{ $checked ? $color : '#fff' }};">
                        <input
                          type="checkbox"
                          name="tags[]"
                          value="{{ $tagOption->id }}"
                          class="form-checkbox tag-checkbox"
                          data-name="{{ $tagOption->name }}"
                          data-color="{{ $tagOption->color ?: '#e2e8f0' }}"
                          @checked($checked)>
                        <span>{{ $tagOption->name }}</span>
                      </label>
                    @endforeach
                  </div>
                </form>
                <div id="tags-feedback-index" class="small text-muted mt-2 tags-feedback"></div>
                @include('customers.partials.tags_script')
              @endif
            </div>
          </div>
        </div>

        <!-- segunda columna -->
        <div class="col-md-8">
          <div id="customer_fallowup">
            @include('customers.partials.actions_form')
            @include('customers.index_partials.time_line')
            @include('customers.index_partials.accordion')
          </div>
        </div>

      </div> <!-- row -->
    @endif


@else
  <div class="col-md-12">
    El prospecto no existe
  </div>
  <div>
    <a href="/customers/create">Crear</a>
  </div>
@endif
