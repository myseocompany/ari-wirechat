<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>

@if($customer != null)
  @include('customers.index_partials.customer_header')
  
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


@else
  <div class="col-md-12">
    El prospecto no existe
  </div>
  <div>
    <a href="/customers/create">Crear</a>
  </div>
@endif
