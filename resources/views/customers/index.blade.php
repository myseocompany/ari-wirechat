@extends('layouts.agile')



@if(isset($phase))
  @section('title', $phase->name)
@else
  @section('title', 'Clientes') 
@endif



@section('list')
<div class="col-12">
  Registro <strong>{{ $model->firstItem() }}</strong> a 
  <strong>{{ $model->lastItem() }}</strong> de 
  <strong>{{ $model->total() }}</strong>
</div>
 

 <script type="text/javascript">
   var ratings = [];
 </script>
 <?php $cont=0; ?>

  @foreach($model as $item)
  <div class="col-12">
      <div class="customers customer_row row">
          <div class="initials col-sm-1 col-2">
              <div class="customer-circle" style_id="{{$item->status_id}}" style="background-color: <?= $item->getStatusColor(); ?>">
                  {{$item->getInitials()}}
              </div>
          </div>
          <div class="customer_name col-sm-8 col-10" onmouseover="showEditIcon({{$item->id}});" onmouseout="hideEditIcon({{$item->id}});">
              <div>
                  <?php 
                  $request->customer_id = $item->id;
                  $url = $request->fullUrl();
                  if(str_contains($request->fullUrl(), "?")){
                      if($request->has("customer_id")){
                          $request->customer_id = $item->id; 
                      }
                      $url = $request->fullUrl()."&customer_id=".$item->id; 
                  }else{
                      $url = $request->fullUrl()."?customer_id=".$item->id; 
                  }
                  ?>
                  <a href="{{$url}}">
                      <?php if($item->id==173929){ } ?>
                      @if(isset($item->name)&& ($item->name!=""))
                          {{ substr($item->name, 0, 21) }}@if (strlen($item->name)>21) ...@endif
                      @else
                          Sin nombre
                      @endif
                  </a>
              </div>
              <div class="scoring_customer">
                  @if(isset($item->country) && strlen($item->country)==2)
                      <img src="/img/flags/{{ strtolower($item->country) }}.svg" height="10">
                  @else
                      {{ $item->country }}
                  @endif 
                  @if(isset($item->scoring_interest) && ($item->scoring_interest>0))
                      <span style="background-color: #ccc; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left; font-size: 12px; padding: 2px;">{{$item->scoring_interest}}</span>
                  @endif
                  <div class="stars-outer">
                      <div class="stars-inner" id="star{{$cont++}}"></div>
                      <script type="text/javascript">
                          ratings.push({{$item->getScoringToNumber()}});
                      </script>
                  </div>
              </div>
              <a href="/customers/{{$item->id}}/edit">
                  <img src="/img/editar.png" id="edit_icon_{{$item->id}}" style="display: none; width: 17px;">
              </a> 
              @if(isset($item->phone) || isset($item->phone2))
                  <a>
                      <img onmouseover="showEditIcon({{$item->id}});" onmouseout="hideEditIcon({{$item->id}});" src="/img/message.png" data-toggle="modal" data-target="#modalCampañas_{{$item->id}}" id="edit_icon_campaings_{{$item->id}}" style="display: none; width: 20px;">
                  </a> 
              @endif
              <div class="customer_description">
                  @if(isset($item->maker) && ($item->maker==1) )  🥟 Hace empanadas @endif
                  @if(isset($item->maker) && ($item->maker==0) )  💡 Proyecto @endif
                  @if(isset($item->maker) && ($item->maker==2) )  🍗🥩⚙️ Desmechadora @endif
                  @if(isset($item->country))
                      {{ substr($item->country,0,15)}}@if (strlen($item->country)>15) ...@endif
                      ,
                  @endif
                  <div>
                      @if(isset($item->phone)|| isset($item->phone2))
                          <span>{{ isset($item->phone) ? $item->phone : $item->phone2 }}</span>
                      @endif
                  </div>
                  <div>
                      @if(isset($item->created_at))
                          {{\Carbon\Carbon::parse($item->created_at)->format('d-m-Y')}}
                      @endif
                  </div>
              </div>
          </div>
          <div class="customer_created_at col-sm-3 d-none d-sm-block">
              <a href="/customers/{{$item->id}}/show">
                  @if (isset($item->user) && ($item->user != '') && ($item->user_id != 0))
                      {{substr($item->user->name, 0, 20)}}
                  @else
                      sin asesor
                  @endif
              </a>
          </div>
      </div>
  </div>


<!--modal campañas -->
  <div id="modalCampañas_{{$item->id}}" class="modal fade" role="dialog">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
               <h4 class="modal-title">Campañas</h4> 
              </div>
              <div class="modal-body">
                      <div class="col-md-12" id="div_campaign_select_{{$item->id}}">
                        
                      <select name="message_id_{{$item->id}}" id="message_id_{{$item->id}}" onchange="nav(this.value,{{$item->id}})" class="form-control">
                          <option value="">Seleccione un mensaje</option>
                        
                            @if(isset($messages))
                              @foreach($messages as $message)
                         
                                <option value="{{$message->text}}">{{substr($message->text,0,40)}}</option>
                              @endforeach
                            @endif
                        </select>
                      </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
    </div>



      

  

              
                  
  @endforeach
  <script type="text/javascript">
    $(document).ready(function(){
      starTotal = 4;
      ratings.forEach(renderStar);

      function renderStar(value, index, array){
        starPercentage = (array[index] / starTotal) * 100;
        starPercentageRounded = (Math.round(starPercentage / 10) * 10)+'%';
        selector = '#star'+index;
        /*console.log(selector);
        console.log($(selector));
        console.log(index+":"+value+":"+starPercentageRounded+selector);
        */
        $(selector).width(starPercentageRounded); 
      }
    });
             
          
  </script>
  <style>
    ul.pagination {
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 15px;
    padding: 10px;
}

  </style>
<div class="row">
  <div class="col-12 d-flex justify-content-center">
      {!! $model->appends(request()->input())->links() !!}
  </div>
</div>


@endsection

@section('filter')
 @include('customers.index.side_filter')
@endsection



<?php 
  function requestToStr($request){
    $str = "?";
    $url = $request->fullUrl();
    $parsedUrl = parse_url($url);
    
    if(isset($parsedUrl['query'] ))
      $str .= $parsedUrl['query']; 

    return $str; 
  }
 ?>




{{-- Alertas --}}
  @if (session('status'))
          <div class="alert alert-primary alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            {!! html_entity_decode(session('status')) !!}
        </div>
  @endif
    @if (session('statusone'))
          <div class="alert alert-warning alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            {!! html_entity_decode(session('statusone')) !!}
        </div>
  @endif
  @if (session('statustwo'))
          <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            {!! html_entity_decode(session('statustwo')) !!}
        </div>
  @endif
@section('content')
  <div>
  @if($customersGroup->count()!=0)
  <ul class="groupbar bb_hbox">
 @php
  $count=0;  
  $sum_g = 0;  
 @endphp 
    @foreach($customersGroup as $item)
 <?php
  
  if($item->count>0)
      $count++;

  ?>
  
  @endforeach
    @foreach($customersGroup as $item)
    @if($item->count!=0)
    <li class="groupBarGroup" style="background-color: @if( isset($item->status_color) ) {{$item->status_color}}; @else #000000; @endif  width: <?php 
       

        if($customersGroup->count()!=0){
          echo 100/$count;
        }
     ?>%">
      <h3>{{$item->count}}</h3>
     
      <div><a href="#" onclick="changeStatus({{$item->status_id}})"> @if( isset($item->status_name) ) {{$item->status_name}} @else sin estado @endif </a></div>
    </li>
    @php
      $sum_g += $item->count;
    @endphp
    @endif          
    @endforeach
  </ul>
  @else
    Sin Estados
  @endif
</div>


<div>@if(isset($sum_g)) TOTAL {{$sum_g}} @endif </div>

<style>
@media (max-width: 576px) {
    .customer_name {
        display: flex;
        flex-direction: column;
    }
    .customer_description {
        display: flex;
        flex-direction: column;
    }
}

</style>

<script type="text/javascript">
  function showEditIcon(id){
    console.log("show_edit_icon_"+id);
    $("#edit_icon_"+id).css("display", "inline");
    $("#edit_icon_campaings_"+id).css("display", "inline");
  }
  function hideEditIcon(id){
    console.log("hide_edit_icon_"+id);
    $("#edit_icon_"+id).css("display", "none");
     $("#edit_icon_campaings_"+id).css("display", "none");
}

  function nav(value,id) {
    var message = encodeURI(value);
    if (value != "") { 
      endpoint = '/campaigns/'+id+'/getPhone/setMessage/'+message;
        $.ajax({
            type: 'GET',
            url: endpoint,
            dataType: 'json',
            success: function (data) {
                var phone = data;
                /*
                  if(phone == '' || phone == null){
                      phone = data;
                  }
                  */
                   url = "https://api.whatsapp.com/send/?phone="+phone+"&text="+encodeURI(value);
                   window.open(url,'_blank');
            },
            error: function(data) { 
            }
        });

       }
  }
</script>
  @include('customers.side_show')
@endsection
