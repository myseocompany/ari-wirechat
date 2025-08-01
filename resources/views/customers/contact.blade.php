<div id="divmsg"></div>

<div class="card-block">
  <form action="customers/{{$customer->id}}/edit">
    {{ csrf_field() }}

    {{-- SECCIÓN: INFORMACIÓN DEL EVENTO --}}
    <div class="mb-4 border-bottom pb-2">
      <h5 class="text-primary">Información del Evento</h5>

      @if(!empty($customer->country))
        <p><strong>País:</strong> <span class="text-dark">{{ $customer->country }}</span></p>
      @endif

      @if(!empty($customer->campaign_name))
        <p><strong>Campaña:</strong> <span class="text-dark">{{ $customer->campaign_name }}</span></p>
      @endif

      @if(!empty($customer->adset_name))
        <p><strong>Grupo de anuncios:</strong> <span class="text-dark">{{ $customer->adset_name }}</span></p>
      @endif

      @if(!empty($customer->ad_name))
        <p><strong>Anuncio:</strong> <span class="text-dark">{{ $customer->ad_name }}</span></p>
      @endif

      @if(!empty($customer->user))
        <p><strong>Asignado a:</strong> <span class="text-dark">{{ $customer->user->name }}</span></p>
      @endif

      @if(!empty($customer->source))
        <p><strong>Fuente:</strong> <span class="text-dark">{{ $customer->source->name }}</span></p>
      @endif

      @if(!empty($customer->created_at))
        <p><strong>Fecha de creación:</strong> <span class="text-dark">{{ $customer->created_at }}</span></p>
      @endif

      @if(!empty($customer->updated_at))
        <p><strong>Fecha de actualización:</strong> <span class="text-dark">{{ $customer->updated_at }}</span></p>
      @endif

      @if(!empty($customer->updated_user))
        <p><strong>Usuario actualizador:</strong> <span class="text-dark">{{ $customer->updated_user->name }}</span></p>
      @endif
    </div>

    {{-- SECCIÓN: CONTACTO --}}
    <div class="mb-4 border-bottom pb-2">
      <h5 class="text-primary">Contacto</h5>

      @if(!empty($customer->contact_name))
        <p><strong>Nombre:</strong> <span class="text-dark">{{ $customer->contact_name }}</span></p>
      @endif

      @if(!empty($customer->contact_email))
        <p><strong>Email:</strong> <span class="text-dark">{{ $customer->contact_email }}</span></p>
      @endif

      @if(!empty($customer->contact_phone2))
        <p><strong>Teléfono:</strong> <span class="text-dark">{{ $customer->contact_phone2 }}</span></p>
      @endif

      @if(!empty($customer->contact_position))
        <p><strong>Cargo:</strong> <span class="text-dark">{{ $customer->contact_position }}</span></p>
      @endif

      @if(!empty($customer->document))
        <p><strong>Documento:</strong> <span class="text-dark">{{ $customer->document }}</span></p>
      @endif
    </div>

    {{-- SECCIÓN: EMPRESA --}}
    <div class="mb-4 border-bottom pb-2">
      <h5 class="text-primary">Empresa</h5>

      @if(!empty($customer->business))
        <p><strong>Nombre:</strong> <span class="text-dark">{{ $customer->business }}</span></p>
      @endif

      @if(!empty($customer->position))
        <p><strong>Cargo del contacto:</strong> <span class="text-dark">{{ $customer->position }}</span></p>
      @endif

      @if(!empty($customer->number_venues))
        <p><strong>Número de sedes:</strong> <span class="text-dark">{{ $customer->number_venues }}</span></p>
      @endif
    </div>

    {{-- SECCIÓN: INTERÉS COMERCIAL --}}
    <div class="mb-4 border-bottom pb-2">
      <h5 class="text-primary">Interés Comercial</h5>

      @if(!empty($customer->product))
        <p><strong>Producto consultado:</strong> <span class="text-dark">{{ $customer->product->name }}</span></p>
      @endif

      @if(!empty($customer->bought_products))
        <p><strong>Producto adquirido:</strong> <span class="text-dark">{{ $customer->bought_products }}</span></p>
      @endif

      @if(!empty($customer->date_bought))
        <p><strong>Fecha de compra:</strong> <span class="text-dark">{{ $customer->date_bought }}</span></p>
      @endif

      @if(!empty($customer->empanadas_size))
        <p><strong>Tamaño de empanadas:</strong> <span class="text-dark">{{ $customer->empanadas_size }}</span></p>
      @endif

      @if(!empty($customer->count_empanadas))
        <p><strong>No. empanadas al día:</strong> <span class="text-dark">{{ $customer->count_empanadas }}</span></p>
      @endif
    </div>

    {{-- SECCIÓN: NOTAS Y VISITAS --}}
    <div class="mb-4 border-bottom pb-2">
      <h5 class="text-primary">Notas</h5>

      @if(!empty($customer->notes))
        <p><strong>Observaciones:</strong> <span class="text-dark">{{ $customer->notes }}</span></p>
      @endif

      @if(!empty($customer->technical_visit))
        <p><strong>Visitas Técnicas:</strong> <span class="text-dark">{{ $customer->technical_visit }}</span></p>
      @endif
    </div>

    {{-- BOTONES DE ACCIÓN --}}
    <div class="mb-4">
      <a href="/customers/{{$customer->id}}/edit" class="btn btn-sm btn-primary">Editar</a>

      @if(is_null($customer->user_id) || $customer->user_id == 0)
        <a href="/customers/{{$customer->id}}/assignMe" class="btn btn-sm btn-primary">Asignarme</a>
      @endif

      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
        <a href="/customers/{{ $customer->id }}/destroy" class="btn btn-sm btn-danger" title="Eliminar">Eliminar</a>

        {{-- Datos para RD --}}
        <input type="hidden" name="name" id="name" value="{{ $customer->name }}">
        <input type="hidden" name="phone" id="phone" value="{{ $customer->phone }}">
        <input type="hidden" name="email" id="email" value="{{ $customer->email }}">
        <input type="hidden" name="country" id="country" value="{{ $customer->country }}">

        <a class="btn btn-sm btn-primary mt-2" onclick="sendToRDStation();">Enviar a RD</a>

        <a href="/optimize/customers/consolidateDuplicates/?query={{ $customer->phone ?? $customer->email }}"
           class="btn btn-sm btn-primary mt-2">
           Buscar duplicados
        </a>
      @endif
    </div>
  </form>
</div>

{{-- SCRIPT PARA RD --}}
<script type="text/javascript">
  function sendToRDStation(){
    var name = $("#name").val();
    var phone = $("#phone").val();
    var email = $("#email").val();
    var country = $("#country").val();

    $.ajax({
      method: 'GET',
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
        $("#divmsg").empty();
        $("#divmsg").prepend('<div class="alert alert-primary alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>El Cliente <strong>'+name+'</strong> fue enviado a <strong>RD Station</strong> con éxito!</div>');
        $("#divmsg").show(400).delay(4000).fadeOut();
      },
      error: function(){
        console.log('Error al enviar a RD Station');
      }
    });
  }
</script>
