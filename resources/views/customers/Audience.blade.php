@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>


@section('content')

@if($model != null)

  @if($model->isBanned())
    <h1 style="color:red;"> <i class="fa fa-exclamation-circle" style="color:gray; "></i> {{$model->name}} <br> </h1>  
    @else  
      <h1> {{$model->name}} <br> </h1>  
  @endif

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
<div class="card-block">
  <form action="customers/{{$model->id}}/edit">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-md-6">

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Nombre:</strong></span></div> <div class="col-md-6">{{$model->name}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Documento:</strong></span></div> <div class="col-md-6">{{$model->document}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Teléfono:</strong></span></div> <div class="col-md-6">{{$model->phone}} <a href="https://wa.me/{{ clearWP($model->phone) }}" target="_empty">WhatsApp </a></div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Celular:</strong></span></div> <div class="col-md-6">{{$model->phone2}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Correo Electrónico:</strong></span></div> <div class="col-md-6">{{$model->email}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Dirección:</strong></span></div> <div class="col-md-6">{{$model->address}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Departamento:</strong></span></div> <div class="col-md-6">{{$model->department}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Ciudad:</strong></span></div> <div class="col-md-6">{{$model->city}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Persona de Contacto:</strong></span></div> <div class="col-md-6">{{$model->contact_name}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Email del Contacto:</strong></span></div> <div class="col-md-6">{{$model->contact_email}}</div></div>	

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Usuario actualizador:</strong></span></div> <div class="col-md-6">@if(isset($model->updated_user)){{$model->updated_user->name}}@endif</div></div>   
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Tamaño de empandas:</strong></span></div> <div class="col-md-6">{{$model->empanadas_size}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Número de sedes:</strong></span></div> <div class="col-md-6">{{$model->number_venues}}</div></div>  

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Fecha de Creación:</strong></span></div> <div class="col-md-6">{{$model->created_at}}</div></div>  

        <div class="row"><div class="col-md-6"><span class="lavel"><strong> Última Fecha de actualización:</strong></span></div> <div class="col-md-6">{{$model->updated_at}}</div></div>




      </div>

    
       



      <div class="col-md-6">

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>País:</strong></span></div> <div class="col-md-6">{{$model->country}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Empresa:</strong></span></div> <div class="col-md-6">{{$model->business}}</div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Cargo:</strong></span></div> <div class="col-md-6">{{$model->position}}</div></div>

        <div class="row"><div class="col-md-6"><strong>Estado:</strong></div> <div class="col-md-6">@if(isset($model->status)&& !is_null($model->status)&&$model->status!=''){{$model->status->name}}@endif
        </div></div>
        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Producto Adquirido:</strong></span></div> <div class="col-md-6">{{$model->bought_products}}</div></div>
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Fecha de Compra:</strong></span></div> <div class="col-md-6">{{$model->purchase_date}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Producto consultado:</strong></span></div> <div class="col-md-6">
         @if(isset($model->product)){{$model->product->name}} @endif</div></div>

         <div class="row"><div class="col-md-6"><strong>Asignado a:</strong></div> <div class="col-md-6">
          @if(isset($model->user)&& !is_null($model->user)&&$model->user!=''){{$model->user->name}} @else Sin asignar @endif
        </div></div>

        <div class="row"><div class="col-md-6"><strong>Fuente:</strong></div> <div class="col-md-6">
          @if(isset($model->source)&& !is_null($model->source)&&$model->source!=''){{$model->source->name}}
          @endif
        </div></div>

        
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Telefono del Contacto:</strong></span></div> <div class="col-md-6">{{$model->contact_phone2}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Cargo del Contacto:</strong></span></div> <div class="col-md-6">{{$model->contact_position}}</div></div>
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>No empanadas:</strong></span></div> <div class="col-md-6">{{$model->count_empanadas}}</div></div>

        <div class="row">
      <div class="col-md-12"><span class="lavel"><strong>URL RD Station:</strong></span></div> <div class="col-md-6"><a href="{{$model->rd_public_url}}" target="_blank">{{$model->rd_public_url}}</a>
      </div>
    </div>
    <div class="row"><div class="col-md-6"><span class="lavel"><strong>Interes:</strong></span></div> <div style="background-color: #66C366; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left;">{{$model->scoring_interest}}</div></div>
    <div class="row">
      <div class="col-md-6 lavel"><span class="lavel"><strong>Perfil:</strong></span></div> 
      <div class="col-md-6 scoring">
        <div class="stars-outer">
          <div class="stars-inner"></div>
          <script type="text/javascript">
            ratings = {
              scoring : {{$model->scoring_profile}}
            };
             starTotal = 3;
             for(rating in ratings) {  
                starPercentage = (ratings[rating] / starTotal) * 100;
                starPercentageRounded = (Math.round(starPercentage / 10) * 10)+'%';
                console.log(starPercentageRounded);
                $('.stars-inner').width(starPercentageRounded); 
              }
          </script>
        </div>   
      </div>
    </div>

        <!-- <div class="row"><div class="col-md-6"><span class="lavel"><strong>Notas:</strong></span></div> <div class="col-md-6"></div></div> -->
      </div>

    </div>
    <div class="row">
      <div class="col-md-12"><span class="lavel"><strong>Notas:</strong></span></div> <div class="col-md-12">{{$model->notes}}</div>
    </div>

    
    <div class="row">
      <div class="col-md-12"><span class="lavel"><strong>Visitas Técnicas:</strong></span></div> <div class="col-md-6">{{$model->technical_visit}}</div>
    </div>

    

       <h3>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
         Encuesta
       </button>
     </h3>
   
      <form action="/metadata/{{$model->id}}/store"  method="POST" style="margin-left:10px; margin-right:10px;">
      {{ csrf_field() }}	
        @foreach($meta_data as $item)
       <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">  
          <label> {{$item->value}}</label>
          <input type="number" id="meta_{{$item->id}}" name="meta_{{$item->id}}" placeholder="{{$item->value}}" class="form-control">
        
       
        @endforeach
            
        <input type="submit" value="Enviar" class="btn btn-primary " size="7" >
      </form>
    
           
 

</div>

  @endif

  @else
  El prospecto no existe

  @endif


  @endsection
