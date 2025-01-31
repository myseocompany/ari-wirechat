@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>

@section('content')
@if(isset($audience))
<h1>{{$audience->name}}</h1>

<table class="table table-striped table-sm">
  @foreach($audience->customers as $item)
    <tr><td>{{$item->name}}</td>
      @if(isset($item->user))
      <td>{{$item->user->name}}</td>
      @else
      <td></td>
      @endif
      <td><a class="btn btn-sm btn-danger fa fa-trash-o" href="/audiences/{{$id}}/customers/{{$item->id}}/destroy"></a></td>
    </tr>
  @endforeach
</table>
@endif

<style>
  a:hover {
    color: #4178be;
}
</style>



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

  
{{-- obteber datos del tiempo --}}



  <div>
    @include('audiences.filter')
  </div>



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
  {{-- fin alertas --}}

  {{-- tabla resumen --}}
 <br>

 <div>
  @if(isset($request->all) && ($request->all==true))
  <a href="/audiences/{{$id}}/customers?{{$model->queryString}}">Deseleccionar todos</a>
  @else
  <a href="/audiences/{{$id}}/customers?{{$model->queryString}}&all=true">Seleccionar todos</a>
  @endif
</div>



 <br>
{{-- <div>{{$model->total()}} Registro(s)</div> --}}
<br>
<!-- Prueba boton metodo zero actions -->

<!-- <a href="/emails/zeroActions">cero actions</a> -->

<!-- Fin Prueba boton metodo zero actions -->

  <div class="">
    <form action="/audiences/{{$id}}/customers" method="POST">
          {{ csrf_field() }}
      @if (count($model) > 0)
            <table class="table table-striped table-sm">
              
              </thead>
              <?php $lastStatus=-1 ?>
              <tbody>
               <?php $count=1; ?>
                @foreach($model as $item)
                {{-- colores --}}
		 		 
                {{-- fin colores --}}
                <tr>
                  <td>
                    <input type="checkbox" name="customer_id[]" value="{{$item->id}}" @if(isset($request->all) && ($request->all==true)) checked @endif>
                    <a href="/customers/{{ $item->id }}/show">{{ $count }}</a>
                  </td>

                  <td><a href="/customers/{{ $item->id }}/show">{{ $item->name }}</a><br>
                   <div>

                       <strong>
                          @if(isset($item->user))
                        
                          {{ $item->user->name }}
                        @endif
                       </strong>
                      </div>
                  @if(isset($item->project))
                    {{ $item->project->name }}
                    @endif
                  


                  </td>
                  <!-- Link whatsapp -->
                  <td><a hresf="/customers/{{ $item->id }}/show" href="https://web.whatsapp.com/send?phone=57{{ clearWP($item->phone) }}" target="_blank">

                  	
                    
                  </a></td>
                  
               
                <td>{{ $item->countInActions() }}</td>
                <td>{{ $item->countOutActions() }}</td>
                
                 <td>@if(isset($item->source)){{$item->source->name}}@endif</td> 
                 
                 <td>{{$item->created_at}}<br>
                  {{$item->last_inbound_date}}
                  <br>

                  {{$item->createdDays()}}

                 </td> 

                 <td>
                    {{-- @if (isset($item->status_id)) {{ substr($item->statuses_name,0,10) }} @endif --}}
                       <?php 
                         if(isset($item->status_id)&&($item->status_id!="")&&(!is_null($item->status))){
                               echo $item->status->name;
                            }
                        ?> 
                 </td>

                 @if (Auth::user()->role_id == 1 )
                   <td>
                   {{-- Delete --}}
                   
                 </td>
                 @endif
                 
                  <!--
                  <td>
                  <a href="customers/{{ $item->id }}/show"><span class="btn btn-sm btn-success fa fa-eye fa-3" aria-hidden="true" title="Consultar"></span></a>
                    <a href="customers/{{ $item->id }}/edit"><span class="btn btn-sm btn-warning fa fa-pencil-square-o" aria-hidden="true" title="Editar"></span></a>
                    {{-- Delete --}}
                    <a href="customers/{{ $item->id }}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
                  </td>
                  </td>
                -->
                </tr>
                <?php $count++;
                  $lastStatus = $item->status_id;
                ?>
        @endforeach
                <?php $count--;?>
                
              </tbody>
                 <?php 
          
          if(isset( $item->points )){$total_tools += $item->points; }
            $count++;
          ?>
            </table>
            
                  @endif
                  <div>
             
             
            </div>
            <div><input class="btn btn-primary" type="submit" name="" value="Guardar"></div>
</form>
          </div>


@endsection
