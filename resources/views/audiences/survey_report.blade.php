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






  {{-- tabla resumen --}}
 <br>

@include('audiences.dashboard')

 <br>
{{-- <div>{{$model->total()}} Registro(s)</div> --}}
<br>

<!--Tabla de contenido -->


<div class="table">
    <table class="table table-striped">
        <thead>
          <tr>
            <th>Preguntas</th>
            <th>Total de respuestas</th>
            <th>Ponderado</th>
            <th></th>
          </tr>          
        </thead>
        <tbody>

                @foreach($metas as $item)
                @if($item->parent_id == null)
                    <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>
                    @endif
                @if($item->type_id !=4)
                 
                    <tr>
                    <td>{{$item->name}}</td>
                    <td>{{$item->countask}}</td>
                    <td>{{$item->average}}</td>
                    </tr>
            
                @endif
                @endforeach

           
        </tbody>


    </table>    
</div>





@endsection