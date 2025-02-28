<div id="divmsg"></div>
<div class="card-block">
  <form action="customers/{{$customer->id}}/edit">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-md-12">
      <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Pais:</strong></span></div> <div class="col-md-12">{{$customer->country}}</div></div>
      <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Campaña:</strong></span></div> <div class="col-md-12">{{$customer->campaign_name}}</div></div>
      <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Grupo de anuncios:</strong></span></div> <div class="col-md-12">{{$customer->adset_name}}</div></div>
      <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Anuncio:</strong></span></div> <div class="col-md-12">{{$customer->ad_name}}</div></div>
      
        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Fecha actualización:</strong></span></div> <div class="col-md-12">{{$customer->updated_at}}</div></div>
        

        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Fecha creación:</strong></span></div> <div class="col-md-12">{{$customer->created_at}}</div></div>
        
        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Documento:</strong></span></div> <div class="col-md-12">{{$customer->document}}</div></div>
        
        
        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Persona de Contacto:</strong></span></div> <div class="col-md-12">{{$customer->contact_name}}</div></div>

        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Email del Contacto:</strong></span></div> <div class="col-md-12">{{$customer->contact_email}}</div></div> 

        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Telefono del Contacto:</strong></span></div> <div class="col-md-12">{{$customer->contact_phone2}}</div></div>

        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Cargo del Contacto:</strong></span></div> <div class="col-md-12">{{$customer->contact_position}}</div></div>

        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Usuario actualizador:</strong></span></div> <div class="col-md-12">@if(isset($customer->updated_user)){{$customer->updated_user->name}}@endif</div></div>   
      </div>
      
      <div class="col-md-12">

        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Empresa:</strong></span></div> <div class="col-md-12">{{$customer->business}}</div></div>
        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Cargo:</strong></span></div> <div class="col-md-12">{{$customer->position}}</div></div>

      
        
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Producto Adquirido:</strong></span></div> <div class="col-md-12">{{$customer->bought_products}}</div></div>
        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Fecha de Compra:</strong></span></div> <div class="col-md-12">{{$customer->date_bought}}</div></div>


         <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Tamaño de las empanadas:</strong></span></div> <div class="col-md-12">{{$customer->empanadas_size}}</div></div>
          
          <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Número sedes:</strong></span></div> <div class="col-md-12">{{$customer->number_venues}}</div></div>

        <div class="row">
          <div class="col-md-12 customer-label-container">
            <span class="customer-label">
              <strong>Producto consultado:</strong>
            </span>
          </div> 

          <div class="col-md-12">
         @if(isset($customer->product)){{$customer->product->name}} @endif</div></div>

         <div class="row">
          <div class="col-md-12 customer-label-container">
            <span class="customer-label">
              <strong>Asignado a:</strong>
            </span>
          </div>
          <div class="col-md-12"> 
          @if(isset($customer->user)&& !is_null($customer->user)&&$customer->user!=''){{$customer->user->name}} @else Sin asignar @endif
        </div></div>

        <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label">
          <strong>Fuente:</strong>
        </span>
        </div> <div class="col-md-12">
          @if(isset($customer->source)&& !is_null($customer->source)&&$customer->source!=''){{$customer->source->name}}
          @endif
        </div></div>

        
                <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>No empanadas:</strong></span></div> <div class="col-md-12">{{$customer->count_empanadas}}</div></div>

        <div class="row">
      
    </div>
    

        <!-- <div class="row"><div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Notas:</strong></span></div> <div class="col-md-12"></div></div> -->
      </div>

    </div>
    <div class="row">
      <div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Notas:</strong></span></div> <div class="col-md-12">{{$customer->notes}}</div>
    </div>

    
    <div class="row">
      <div class="col-md-12 customer-label-container"><span class="customer-label"><strong>Visitas Técnicas:</strong></span></div> <div class="col-md-12">{{$customer->technical_visit}}</div>
    </div>

    

    <br>

    @if($actual)
    <a href="/customers/{{$customer->id}}/edit">
      <span class="btn btn-primary btn-sm" aria-hidden="true">Editar</span>
    </a>
      @if(is_null($customer->user_id) || $customer->user_id==0)
      <a href="/customers/{{$customer->id}}/assignMe">
        <span class="btn btn-primary btn-sm" aria-hidden="true">Asignarme</span>
      </a>
      @endif  

      @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)

        <a href="/customers/{{ $customer->id }}/destroy">
        <span class="btn btn-sm btn-danger" aria-hidden="true" title="Eliminar">Eliminar</span></a>


        <input type="hidden" name="name" id="name" value="{{$customer->name}}">
        <input type="hidden" name="phone" id="phone" value="{{$customer->phone}}">
        <input type="hidden" name="email" id="email" value="{{$customer->email}}">
        <input type="hidden" name="country" id="country" value="{{$customer->country}}">
        <a class="btn btn-sm btn-primary" type="submit" title="Enviar Cliente a RD Station" onclick="sendToRDStation();" style="color: white; margin-top: 5px;">Enviar a RD</a>

        <a href="/optimize/customers/consolidateDuplicates/?query={{ $customer->phone ?? $customer->email }}" 
   class="btn btn-sm btn-primary" 
   type="submit" 
   title="Buscar duplicados"  
   style="color: white; margin-top: 5px;">
    Buscar duplicados
</a>


      @endif


    @endif
    </form>
    
  
  </div>

  <script type="text/javascript">
    function sendToRDStation(){
        var name = $("#name").val();
        var phone = $("#phone").val();
        var email = $("#email").val();
        var country = $("#country").val();
        $.ajax({
            method: 'GET',
            type: 'GET',
            crossDomain: true,
            url: "https://mqe.quirky.com.co/api/customers/rd_station",
            dataType: 'json',
            data: {
                name : name,
                phone : phone,
                email : email,
                country : country
            },
            success: function(result){
                console.log(result);
                $("#divmsg").empty();
                $("#divmsg").prepend('<div class="alert alert-primary alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>El Cliente <strong>'+name+'</strong> fué enviado a <strong>RD Station</strong> con éxito!</div>');
                $("#divmsg").show(400);
                $("#divmsg").hide(4000);


                
                
                



            },
            error: function(i,o,s){
                console.log('Error al enviar');
            }
        });
    }
  </script>