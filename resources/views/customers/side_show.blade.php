<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>

@if($customer != null)
<div class="row">
  <div class="col-md-12">
    <div id="customer_title">
      <div style="overflow: hidden;">

        <?php
        ?>
        @if($customer->isBanned())
        <h2 class="mb-2 pb-0" style="border-bottom: 1px solid #fff !important; color:red; "> <i class="fa fa-exclamation-circle" style="color:gray; "></i> {{$customer->name}} </h2>

        @else

        <h1 class="mb-2 pb-0" style="border-bottom: 1px solid #fff !important;">@if(isset($customer->maker)&& ($customer->maker==1)) 🥟 @endif
          @if(isset($customer->maker)&& ($customer->maker==0)) 💡 @endif
          @if(isset($customer->maker)&& ($customer->maker==2))🍗🥩⚙️ @endif

          {{$customer->name}}

        </h1>
        <script>
          function searchInGoogle(search) {
            var url = "https://www.google.com/search?q=" + encodeURIComponent(search);
            window.open(url, '_blank');
          }
        </script>

        <div>
          
          @if(isset($customer->maker)&& ($customer->maker==1)) Hace empanadas @endif
          @if(isset($customer->maker)&& ($customer->maker==0)) Proyecto @endif
          @if(isset($customer->maker)&& ($customer->maker==2)) Desmechadora @endif
        </div>
        @endif

        @include('customers.action_poorly_rated')
        @include('customers.action_opportunity')
        @include('customers.action_sale_form')
        @include('customers.action_spare')
        @include('customers.action_PQR')
        @include('customers.action_order')

        


        @if($customer->user_id)<p style="margin-top: 10px !important;font-size: 20px;color: gray;">{{$customer->user->name}}</p>@endif

      </div>
      <div>@if(isset($customer->scoring_interest) && ($customer->scoring_interest>0))
        <span style="background-color: #ccc; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left; font-size: 12px; padding: 2px;">{{$customer->scoring_interest}}</span>
        @endif
      </div>



      <div class="row">
        @if(isset($customer->linkedin_url))
        <div class="col-md-6 col-sm-6"><a href="{{$customer->linkedin_url}}"><img src="{{$customer->image_url}}" width="200" style="border-radius: 49.9%; width: 20%;"></a></div>
        @endif
        <div class="col-md-12 scoring">
          <div class="stars-outer">
            <div class="stars-inner"></div>
            <script type="text/javascript">
              starTotal = 4;
              starPercentage = ({{$customer -> getScoringToNumber()}} / starTotal) * 100;
                  starPercentageRounded = (Math.round(starPercentage / 10) * 10) + '%'; console.log(starPercentageRounded); $('.stars-inner').width(starPercentageRounded);
            </script>
          </div>
        </div>
      </div>
      @if(isset($customer->status))
      <div><span class="customer_status" style="background-color: {{$customer->status->color}}">{{$customer->status->name}}</span></div>
      @endif
      @if(isset($customer->total_sold))
      <div>Valor de la cotización:{{$customer->total_sold}}</div>
      @endif
      <div>

        @if($customer->country){{$customer->country}},@endif
        @if($customer->department){{$customer->department}},@endif
        @if($customer->city){{$customer->city}},@endif
        @if($customer->address){{$customer->address}},@endif

      </div>



      <div>
        <a @if(isset($customer->phone)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone}}</a>/
        <a @if(isset($customer->phone2)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone2}}</a>
        / {{$customer->email}}



      </div>





    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div id="customer_show">
      <div><a href="#" onclick="searchInGoogle('{{$customer->name}}')">
          Buscar en Google</a>
      </div>
      <div>
        @if(isset($customer->rd_public_url))
        <a href="{{$customer->rd_public_url}}" target="_blank">Buscar en RD Station</a>
        @endif
      </div>
      @include('customers.alerts')
      @include('customers.contact')
      <br>
      @if($actual)

      @endif
    </div>
  </div>



  <!-- segunda columna -->
  <div class="col-md-8">
    <div id="customer_fallowup">
      @include('customers.actions')
      @include('customers.actions_form')

      @include('customers.accordion')











      @if(isset($customer->references))
      <h2>Referencias Wompi--</h2>
      <a href="/references/{{$customer->id}}/create">
        <span class="btn btn-primary btn-sm" aria-hidden="true">Crear</span>
      </a>
      <table class="table table-striped" style="table-layout: fixed;">
        <tr>
          <th class="responsive">Fecha</th>
          <th class="responsive">Concepto</th>
          <th class="responsive">Valor</th>
          <th class="responsive">Estado</th>
          <th class="responsive">Acciones</th>
          <th class="responsive">Link de Pago</th>
        </tr>

        @foreach($references as $item)
        <tr>
          <td>{{$item->created_at}}</td><!-- fecha-->
          <td>{{$item->note}}</td><!-- concepto-->

          <td>{{number_format(substr($item->value,0, -2),2)}}</td><!-- valor-->



          @if (isset($item->status_id) && ($item->status_id == 'DECLINED'))
          <td><span class="badge" style="color: #f64d79; background-color: #ffdce5;">{{$item->status_id}}</span></td>
          @else @if (isset($item->status_id) && ($item->status_id == 'APPROVED'))
          <td><span class="badge" style="color: #2cc775; background-color: #e8f8ee;">{{$item->status_id}}</span></td>
          @else @if (isset($item->status_id) && ($item->status_id == 'ERROR'))
          <td><span class="badge" style="background-color: #eee; color: #888;">{{$item->status_id}}</span></td>
          @else @if (is_null($item->status_id))
          <td>{{$item->status_id}}</td>
          @endif @endif @endif @endif




          @if (is_null($item->status_id))
          @if (Auth::user()->role_id == 1)
          <td>
            <a href="/references/{{$item->id}}/edit">
              <span class="btn btn-success btn-sm" aria-hidden="true">Editar</span>
            </a>
            <a href="/references/{{$item->id}}/destroy">
              <span class="btn btn-danger btn-sm" aria-hidden="true">Eliminar</span>
            </a>
          </td>
          @else
          @if (Auth::user()->role_id == 2)
          <td>
            <a href="/references/{{$item->id}}/edit">
              <span class="btn btn-success btn-sm" aria-hidden="true">Editar</span>
            </a>
          </td>
          @endif
          @endif
          @else
          <td></td>
          @endif


          <td>
            <a href="/references/{{$item->id}}/wompi_link">
              <span class="btn btn-primary btn-sm" aria-hidden="true">Ver</span>
            </a>

            <!--
    <a href="/wompi_link/{{$item->id}}">
        <span class="btn btn-primary btn-sm" aria-hidden="true">Crear con Wompi</span>
    </a>
  -->
          </td>
        </tr>
        @endforeach

      </table>
      @endif



      @include('customers.historial')
    </div>

  </div>
</div>
@else
<div class="col-md-12">
  El prospecto no existe
</div>
<div>
  <a href="/customers/create">Crear</a>
</div>

@endif
<!-- fin de segunda columna -->

</div>

