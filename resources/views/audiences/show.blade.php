@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>

@section('content')

<h1>{{$audience->name}}</h1>

<?php $id=1; ?>



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
    @include('audiences.filter_show')
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

@include('audiences.dashboard')

 <br>
{{-- <div>{{$model->total()}} Registro(s)</div> --}}
<br>
<!-- Prueba boton metodo zero actions -->

<!-- <a href="/emails/zeroActions">cero actions</a> -->

<!-- Fin Prueba boton metodo zero actions -->

  <div>

            @if (count($model) > 0)
            <table class="table table-striped">
              
              <?php $lastStatus=-1 ?>
              <tbody>
               <?php $count=1; ?>
                @foreach($model as $item)
                
                <tr>
                  <td>
                    <a href="/customers/{{ $item->id }}/show">{{ $count + $model->perPage() * (($model->currentPage() -1)) }}</a>
                  </td>
                  <td><div><a href="/customers/{{ $item->id }}/show">{{ substr($item->name,0,20) }}@if (strlen($item->name)>20) ...@endif</a><br>
                    {{$item->phone}}, {{$item->phone2}}<br>
                  {{ substr($item->country,0,15)}}@if (strlen($item->country)>15) ...@endif, {{ substr($item->city,0,15)}}@if (strlen($item->city)>15) ...@endif<br>
                  
                  @if (isset($item->product) && ($item->product != null))
                  ,  {{$item->product->name}}
                  @else
                  , sin producto
                  @endif
                  @if (isset($item->user) && ($item->user != '') && ($item->user_id != 0))
                  {{$item->user->name}}
                  @else
                  , sin asesor
                  @endif</div>
                  <div class="notes">{{$item->notes}}</div>
                  <div class="customer_source">@if(isset($item->souce)){{$item->souce->name}}@endif</div>
                  </td>
                  <?php //dd($item); ?>
                  <td><a href="/customers/{{ $item->id }}/show" href="https://wa.me/{{ clearWP($item->phone) }}&text=Hola%20quiero%20redimir%20mi%20bono%20de%20black%20friday%20para%20comprar%20la%20m%C3%A1quina" target="_blank">

                    
                  </a></td>

                  <td></td>
                  <td></td>
                
                  
                <td>
                  @if(!is_null($item->count_empanadas) && $item->count_empanadas>0)
                  {{$item->count_empanadas}} Emp. <br>
                  @endif
                </td>
                <td><div class="customer_dates">
                  {{$item->created_at}}<br>{{$item->updated_at}}<br>
                    
                </div>
                </td> 

                 <td>@if(isset($item->source)){{$item->source->name}}@endif</td> 
                 <td>
                    
                        
                         @if(isset($item->status_id)&&($item->status_id!="")&&(!is_null($item->status)))
                               <span class="customer_status" style="background-color: {{$item->status->color}}">{{$item->status->name}}</span>
                         @endif   
                         
                 </td>
               <!--  
                 @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
                   <td>
                   {{-- Delete --}}
                    <a href="/customers/{{ $item->id }}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
                 </td>
                 @endif
                 
                  <td>
                  <a href="/customers/{{ $item->id }}/show"><span class="btn btn-sm btn-success fa fa-eye fa-3" aria-hidden="true" title="Consultar"></span></a>
                    <a href="/customers/{{ $item->id }}/edit"><span class="btn btn-sm btn-warning fa fa-pencil-square-o" aria-hidden="true" title="Editar"></span></a>
                    {{-- Delete --}}
                    <a href="/customers/{{ $item->id }}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
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
                  {{-- {{$model->links()}} --}}
                  {{ $model->appends(request()->input())->links() }}
                  <div>
             {{--  Registro {{ $model->currentPage()*$model->perPage() - ( $model->perPage() - 1 ) }}  a {{ $model->currentPage()*$model->perPage()}} de {{ $model->total()}} --}}
             
            </div>
          
          </div>


@endsection
