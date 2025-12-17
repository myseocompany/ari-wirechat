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

              @if(isset($allTags) && $allTags->count())
                @include('customers.partials.tags_selector', [
                  'selectedTags' => $customer->tags,
                  'formId' => 'customer-tags-form-index',
                  'formAction' => route('customers.tags.update', $customer),
                  'feedbackSelector' => '#tags-feedback-index',
                ])
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
