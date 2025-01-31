@extends('layout')

@section('content')

<h1>Crear Orden</h1>

<form class="form-group" action="{{ route('orders.store') }}" method="POST">
  
{{ csrf_field() }}
<input type="hidden" name="status_id" id="status_id" value="2">
    <div class="row">
    	<div class="col-md-6 col-sm-12 group-container">
        	<h3 class="title">Inmueble</h3>
        	<div class="row">
          

            <div class="col-md-12 col-sm-12">
              <div class="row">
                <label class="col-6">Proyecto:</label>
                <select id="category_id" name="category_id" class="col-6 form-control">
                  <script type="text/javascript">
                    var categories = [];
                  </script>
                  <option>Seleccione un proyecto</option>
                  <script type="text/javascript">
                  @foreach($categories as $item)
                      categories[{{$item->id}}] = {{$item->name}};
                  @endforeach
                  </script>
                  @foreach($categories as $item)
                    <option value="{{$item->id}}">{{$item->name}}</option>
                  @endforeach
                </select>
              </div>                          
            </div>

          	<div class="col-md-12 col-sm-12">
            	<div class="row">
	              <label class="col-6">Inmueble:</label>
	              <select id="product_id" name="product_id" class="col-6 form-control" onchange="showPrice();">
                  <script type="text/javascript">
                    var products = [];
                  </script>
	                <option>Seleccione un inmueble</option>
                  <script type="text/javascript">
                  @foreach($products as $item)
                      products[{{$item->id}}] = {{$item->price}};
                      console.log(products);
                  @endforeach
                  </script>
	                @foreach($products as $item)
	                	<option value="{{$item->id}}">{{$item->name}} - {{$item->category_id}}</option>
                  @endforeach
	              </select>
            	</div>                        	
          	</div>
          	<script type="text/javascript">
      				function showPrice(){
        				price = new Intl.NumberFormat("es-ES").format(products[$( "#product_id" ).val()]);
                $('#price').val( "$ "+price );                
              }
		        </script>

		    <div class="col-md-12 col-sm-12">
		    	<div class="row">
		    		<label class="col-6">Parqueadero:</label>
              		<select id="parking_id" name="parking_id" class="col-6 form-control" onchange="showParkingPrice();">
              		<script type="text/javascript">
              			var parkings = [];
              		</script>
	                <option>Seleccione un parqueadero</option>
	                <script type="text/javascript">
	                	@foreach($parkings as $item)
	                		parkings[{{$item->id}}] = {{$item->price}};
	                	@endforeach
	                </script>
	                @foreach($parkings as $item)
	                <option value="{{$item->id}}">{{$item->name}}</option>
	                @endforeach
	              	</select>
		    	</div>
		    </div>

		    <div class="col-md-12 col-sm-12">
		    	<div class="row">
	              <label class="col-6">Deposito:</label>
	              <select id="deposit_id" name="deposit_id" class="col-6 form-control" onchange="showDepositPrice();">
	              	<script type="text/javascript">
	              		var deposits = [];
	              	</script>
	                <option>Seleccione un depósito</option>
	                <script type="text/javascript">
	                	@foreach($deposits as $item)
	                		deposits[{{$item->id}}] = {{$item->price}};
	                	@endforeach
	                </script>
	                @foreach($deposits as $item)
	                <option value="{{$item->id}}">{{$item->name}}</option>
	                @endforeach
	              </select>
	            </div>
		    </div>

          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Cuota inicial:</label>
              <input id="initial_installment" name="initial_installment" class="col-6" 
              value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Acabados:</label>
              <input id="finishes_value" name="finishes_value" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Descuentos/Interéses:</label>
              <input id="discount_interest" name="discount_interest" class="col-6" value="">
            </div>
          </div>
        </div>
      </div> 
      <div class="col-md-6 col-sm-12 group-container">
        <h3 class="title">Precios</h3>
        <div class="row">


          <div class="col-md-12 col-sm-12">
            <div class="row">            	
	            <label class="col-6">-></label>
                <input type="text" name="price" id="price" class="col-6 form-control" readonly>	                          
            </div>
            <div class="row">
            	<label class="col-6">-></label>
            	<input type="text" name="price" id="parkingprice" class="col-6 form-control" readonly>
            </div>
          </div>
          <script>
          	function showParkingPrice() {
          		parkingPrice = new Intl.NumberFormat("es-ES").format(parkings[$( "#parking_id" ).val()]);
          		$('#parkingprice').val( "$ " +parkingPrice);
          	}
          </script>

          <div class="col-md-12 col-sm-12">            
            <div class="row">
            	<label class="col-6">-></label>
            	<input type="text" name="price" id="depositprice" class="col-6 form-control" readonly>
            </div>
          </div>
          <script>
          	function showDepositPrice() {
          		depositPrice = new Intl.NumberFormat("es-ES").format(deposits[$( "#deposit_id" ).val()]);
          		$('#depositprice').val( "$ " +depositPrice);
          	}
          </script>

          <div class="col-md-12 col-sm-12">            
            <div class="row">
              <label class="col-6">Subtotal:</label>
              <input type="text" name="subtotal" id="subtotal" class="col-6 form-control" readonly>
            </div>
          </div>
          <script>
            function showSubtotal() {
              var apto = document.getElementById("price").value;
              var park = document.getElementById("parkingprice").value;
              var depo = document.getElementById("depositprice").value;
              var subtotal = apto + park + depo;
              console.log(subtotal);
              $('#subtotal').val( "$ " +subtotal);
            }
          </script>
          
          
        </div>
      </div>

      <div class="col-md-6 col-sm-12 group-container">
        <h3 class="title">Subsidio</h3>
        <div class="row">
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Subsidio:</label>
              <input id="subsidy" name="subsidy" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Subsidio al credito:</label>
              <input id="subsidy_balance" name="subsidy_balance" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Estado:</label>
              <input id="subsidy_status" name="subsidy_status" class="col-6" value="">
            </div>
          </div>
          
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Fecha:</label>
              <input id="subsidy_date" name="subsidy_date" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Valor:</label>
              <input id="subsidy_value" name="subsidy_value" class="col-6" value="">
            </div>
          </div>
        </div>
      </div>  

      <div class="col-md-6 col-sm-12 group-container">
        <h3 class="title">Crédito</h3>
        <div class="row">
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Crédito:</label>
              <input id="credit" name="credit" class="col-6" value="">
            </div>
          </div>

          
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Estado:</label>
              <input id="credit_status" name="credit_status" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Valor:</label>
              <input id="credit_value" name="credit_value" class="col-6" value="">
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-sm-12 group-container">
        <h3 class="title">Otros</h3>
        <div class="row">

          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Escritura:</label>
              <input id="real_state_note" name="real_state_note" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Entregas:</label>
              <input id="releases" name="releases" class="col-6" value="">
            </div>
          </div>
          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Encargo:</label>
              <input id="fiduciary_commission" name="fiduciary_commission" class="col-6" value="">
            </div>
          </div>

        </div>
      </div>

      <div class="col-md-6 col-sm-12 group-container">
        <h3 class="title">Cliente</h3>
        <div class="row">
          


          <div class="col-md-12 col-sm-12">
            <div class="row">
              <label class="col-6">Nombre:</label>
              <input id="customers" name="customers" class="col-6 form-control">
              
              <input type="hidden" name="customer_id" id="customer_id" value="">
              
            </div>
          </div>
          
          
        </div>
      </div>            
      
    </div>




      <div><input type="submit" name="" class="btn btn-primary" style="display: block; margin: 0 auto;"></div>
    
  
  </form>

@include('orders.autocomplete')

<script type="text/javascript">
  
  var customers = [@foreach ($customers as $item) "{{ucwords(strtolower($item->name))}}", @endforeach];
  var customers_id = [
  @foreach ($customers as $item) "{{ucwords(strtolower($item->id))}}", @endforeach];

  autocomplete(document.getElementById("customers"), customers);
</script>
  

@endsection