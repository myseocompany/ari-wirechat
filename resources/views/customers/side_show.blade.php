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
        <h2 class="mb-2 pb-0" style="border-bottom: 1px solid #fff !important; color:red; "> <i class="fa fa-exclamation-circle" style="color:gray; "></i> 
          
          

          {{$customer->name}} </h2>

        @else

        <h1 class="mb-2 pb-0" style="border-bottom: 1px solid #fff !important;">
          @if(isset($customer->maker)&& ($customer->maker==1)) 🥟 @endif
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



      <div>@if(isset($customer->phone))
        <a  href="/customers/{{$customer->id}}/show">{{$customer->phone}}</a>/@endif
        @if(isset($customer->phone2)) 
        <a href="/customers/{{$customer->id}}/show" >{{$customer->phone2}}</a>@endif
        / {{$customer->email}}



      </div>
      <div class="customer_description">creado el: 
        {{$customer->created_at}} / 
      </div>
      <div>
        <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm mb-2" onclick="startChat({{ $customer->id }})">
          <i class="fas fa-comments"></i> Iniciar Chat
        </a>
        
        @include('customers.wire_chat_link')

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

