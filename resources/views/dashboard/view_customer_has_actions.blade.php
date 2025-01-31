@extends('layout')

@section('content')

@include('dashboard.filter')
<div class="container mt-4">
	<div class="row">
		<div class="col-md-8">
			<h2>Leads Activos por usuario</h2>
			<table class="table table-striped table-hover table-responsive">
				<thead class="thead-default">
					<tr>
						<th>Usuario</th>
						<th>Última acción realizada</th>
						<th>Acciones Totales</th>
						<th>Clientes asignados</th>
					</tr>
				</thead>
				<tbody>
					<?php $sum_total_active = 0; ?>
					@foreach ($total_active as $item)

						<tr>
							<td>{{$item->user_name}}</td>
							<?php 
								$time = new DateTime(); 
								$last_action = new DateTime($view_customer_has_action->getCreatedAtActionMax($item->user_id));
								$last_action->format('d/m/Y H:i:s');
								$action_interval = $last_action->diff($time);
							?>
							<td>
									@if($action_interval->m>0)
									<div class="time-danger" >{{$action_interval->m . " meses "}}</div>
									@elseif($action_interval->d>0)
									<div class="time-danger" >{{$action_interval->d . " días "}}</div>
									@elseif($action_interval->h>0)
									<div class="time-warning" >{{$action_interval->h . " horas "}}</div>
									@else
									<div class="time-success" >{{$action_interval->i . " minutos"}}</div>
									@endif
							</td>
							<td>{{$item->count}}</td>
							<td>{{$item->lead_active}}</td>
							

								

							<?php $sum_total_active += $item->lead_active; ?>
						</tr>	
					@endforeach
					<tr>
					    <th>TOTAL</th>
					    <td></td>
					    <td></td>
					    <td>{{$sum_total_active}}</td>
					</tr>
					
				</tbody>
			</table>
		</div>
		<div class="col-md-4">
			<h2>Leads Inactivos por usuario</h2>
			<table class="table table-striped table-hover table-responsive">
				<thead class="thead-default">
					<tr>
						<th>Usuario</th>
						<th>Clientes asignados</th>
					</tr>
				</thead>
				<tbody>
					<?php $sum_total_inactive = 0; ?>
					@foreach ($total_inactive as $item)
						<tr>
							<td>{{$item->user_name}}</td>
							<td>{{$item->lead_inactive}}</td>
							<?php $sum_total_inactive += $item->lead_inactive; ?>
						</tr>	
					@endforeach
				<tr>
				    <th>TOTAL</th>
				    <td>{{$sum_total_inactive}}</td>
				</tr>
				</tbody>
			</table>
		</div>

	</div>
	 
	@if($sum_total_active+$sum_total_inactive > 0)
	<div class="graphic-container" style="margin-top: 20px;margin-left: 20px;margin-right: 20px;">
		<div class="row">
			<div class="form-group col-md-12">
				<span style="background-color: lime; display:inline-block; border-top-left-radius: 20px; border-bottom-left-radius: 20px; height: 40px; width: {{ceil(100*(($sum_total_active+$sum_total_inactive)-$sum_total_inactive)/($sum_total_active+$sum_total_inactive))-1}}%; @if(ceil(100*(($sum_total_active+$sum_total_inactive)-$sum_total_inactive)/($sum_total_active+$sum_total_inactive))-1 == 100) border-top-right-radius: 20px; border-bottom-right-radius: 20px; @endif" >
				<a id="graphic">{{($sum_total_active+$sum_total_inactive)-$sum_total_inactive}}</a></span><span style="background-color: #d0021b; display: inline-block; height: 40px; border-top-right-radius: 20px;border-bottom-right-radius: 20px; width: {{ceil(100*$sum_total_inactive/($sum_total_active+$sum_total_inactive))}}%">
				<a id="graphic">{{$sum_total_inactive}}</a>
				</span>
			</div>
		</div>
	</div>
	@endif
	<div class="row">
		<div class="col-md-6">
			<div class="card">
			  <div class="card-header">
			    <h3>
			      Activos
			    </h3>
			  </div>
			  <div class="card-body">
			    <table id="scroll_active" class="table table-hover">
			    <tbody>
			    @foreach ($model_active as $item)
			      <tr>
					<td>
						<div>
							@if(isset($item->status_name))
								<span class="badge" style="background-color: {{$item->color}} ">
									{{$item->status_name}} 
								</span> 
							@endif
						</div>
						<?php 
							$time = new DateTime(); 
							$time_action = new DateTime($item->created_at_action_max);
							$time_action->format('d/m/Y H:i:s');
							$interval = $time_action->diff($time);
						?>
						<div>
							{{$item->user_name}}
							{{$item->created_at_action_max}} - Espera: 
							<div class="interval" style="color:red; float: right;">
								@if($interval->m>0)
								{{$interval->m . " meses "}}{{$interval->d . " días "}}{{$interval->h . " horas"}}
								@elseif($interval->d>0)
								{{$interval->d . " días "}}{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@elseif($interval->h>0)
								{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@else
								{{$interval->i . " minutos"}}
								@endif
							</div>
						</div>
						<a href="/customers/{{$item->id}}/show"><h4> {{$item->customer_name}}</h4></a>
						<div class="action_created"></div>
						<div class="row">
							<div class="col">{{$item->phone}}</div>
							<div class="col">{{$item->email}}</div>
							@if(isset($item->source))
							<div class="col">{{$item->source_name}}</div>
							@endif
						</div>
					</td>
			      </tr>
			      <?php $id_active = $item->id; ?>
			    @endforeach
			    <div class="last_index_active" style="display: none;" @if(isset($id_active)) id="{{$id_active}}" @else id="" @endif></div>
				<div class="before_active"></div>
			    </tbody>
			  </table>
			  </div>
			</div>
		</div>	

		<div class="col-md-6">
			<div class="card">
			  <div class="card-header">
			    <h3>
			      Inactivos
			    </h3>
			  </div>
			  <div class="card-body">
			    <table id="scroll_inactive" class="table table-hover">
			    <tbody>
			    @foreach ($model_inactive as $item)
			      <tr>
					<td>
						<div>
							@if(isset($item->status_name))
								<span class="badge" style="background-color: {{$item->color}} ">
									{{$item->status_name}} 
								</span> 
							@endif
						</div>
						<?php 
							$time = new DateTime(); 
							$time_action = new DateTime($item->created_at);
							$time_action->format('d/m/Y H:i:s');
							$interval = $time_action->diff($time);
						?>
						<div>
							{{$item->created_at}} - Espera: 
							<div class="interval" style="color:red; float: right;">
								@if($interval->m>0)
								{{$interval->m . " meses "}}{{$interval->d . " días "}}{{$interval->h . " horas"}}
								@elseif($interval->d>0)
								{{$interval->d . " días "}}{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@elseif($interval->h>0)
								{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@else
								{{$interval->i . " minutos"}}
								@endif
							</div>
						</div>
						<a href="/customers/{{$item->id}}/show"><h4> {{$item->customer_name}}</h4></a>
						<div class="action_created"></div>
						<div class="row">
							<div class="col">{{$item->phone}}</div>
							<div class="col">{{$item->email}}</div>
							@if(isset($item->source))
							<div class="col">{{$item->source_name}}</div>
							@endif
						</div>
					</td>
			      </tr>
			      <?php $id_inactive = $item->id; ?>
			    @endforeach
			    <div class="last_index_inactive" style="display: none;" @if(isset($id_inactive)) id="{{$id_inactive}}" @else id="" @endif></div>
				<div class="before_inactive"></div>
			    </tbody>
			  </table>
			  </div>
			</div>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-6">
			<div class="card">
			  <div class="card-header">
			    <h3>
			      Inactivos sin usuario
			    </h3>
			  </div>
			  <div class="card-body">
			    <table id="scroll_inactive_without_user" class="table table-hover">
			    <tbody>
			    @foreach ($model_inactive_without_user as $item)
			      <tr>
					<td>
						<div>
							@if(isset($item->status_name))
								<span class="badge" style="background-color: {{$item->color}} ">
									{{$item->status_name}} 
								</span> 
							@endif
						</div>
						<?php 
							$time = new DateTime(); 
							$time_action = new DateTime($item->created_at);
							$time_action->format('d/m/Y H:i:s');
							$interval = $time_action->diff($time);
						?>
						<div>
							{{$item->created_at}} - Espera: 
							<div class="interval" style="color:red; float: right;">
								@if($interval->m>0)
								{{$interval->m . " meses "}}{{$interval->d . " días "}}{{$interval->h . " horas"}}
								@elseif($interval->d>0)
								{{$interval->d . " días "}}{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@elseif($interval->h>0)
								{{$interval->h . " horas "}}{{$interval->i . " minutos"}}
								@else
								{{$interval->i . " minutos"}}
								@endif
							</div>
						</div>
						<a href="/customers/{{$item->id}}/show"><h4> {{$item->customer_name}}</h4></a>
						<div class="action_created"></div>
						<div class="row">
							<div class="col">{{$item->phone}}</div>
							<div class="col">{{$item->email}}</div>
							@if(isset($item->source))
							<div class="col">{{$item->source_name}}</div>
							@endif
						</div>
					</td>
			      </tr>
			      <?php $id_inactive_without_user = $item->id; ?>
			    @endforeach
			    <div class="last_index_inactive_without_user" style="display: none;" @if(isset($id_inactive_without_user)) id="{{$id_inactive_without_user}}" @else id="" @endif></div>
				<div class="before_inactive_without_user"></div>
			    </tbody>
			  </table>
			  </div>
			</div>
		</div>	
	</div>
</div>
<script>
	var read_request;
	var scroll_active = null;//Evitamos que el scroll se dispare multiples veces
	var scroll_inactive = null;//Evitamos que el scroll se dispare multiples veces
	var scroll_inactive_without_user = null;//Evitamos que el scroll se dispare multiples veces

	function readMore(){
		var id = $(".last_index_active").attr("id");
		var get_last_index;
		var html = "";
		var request = new Object();
		request.user_id = $("#user_id").val();
		request.from_date = $("#from_date").val();
		request.to_date = $("#to_date").val();
		request.id = $(".last_index_active").attr("id");
		
		if(id){
			$.ajax({
				type: "GET",
				url: "/dashboard/scroll_active",
				data: request,
				success: function(data){
					$(".before_active").html("");
					if(data.response == true){
						for(index in data.model){
							html+='<tr><td><div><span class="badge" style="background-color: '+data.model[index].color+' ">'+data.model[index].status_name+' </span></div><div></div><a href="/customers/'+data.model[index].id+'/show"><h4> '+data.model[index].customer_name+'</h4></a><div class="action_created"></div><div class="row"><div class="col">'+data.model[index].phone+'</div><div class="col">'+data.model[index].email+'</div><div class="col">'+data.model[index].source_name+'</div></div></td></tr>';
							get_last_index = data.model[index].id;
						}
						$("#scroll_active").append(html);
						read_request = true;
					}else{
						read_request = false;
						$("#scroll_active").append("<div data-alert class='alert alert-info center'>No hay más datos</div>");
					}
					$(".last_index_active").attr("id",get_last_index);
				},
				error: function(e){
					alert(error);
				}
			});
		}
	}


	function readMoreInactive(){
		var id = $(".last_index_inactive").attr("id");
		var get_last_index;
		var html = "";
		var request = new Object();
		request.user_id = $("#user_id").val();
		request.from_date = $("#from_date").val();
		request.to_date = $("#to_date").val();
		request.id = $(".last_index_inactive").attr("id");
		
		if(id){
			console.log(id);
			$.ajax({
				type: "GET",
				url: "/dashboard/scroll_inactive",
				data: request,
				success: function(data){
					$(".before_inactive").html("");
					if(data.response == true){
						for(index in data.model){
							html+='<tr><td><div><span class="badge" style="background-color: '+data.model[index].color+' ">'+data.model[index].status_name+' </span></div><div></div><a href="/customers/'+data.model[index].id+'/show"><h4> '+data.model[index].customer_name+'</h4></a><div class="action_created"></div><div class="row"><div class="col">'+data.model[index].phone+'</div><div class="col">'+data.model[index].email+'</div><div class="col">'+data.model[index].source_name+'</div></div></td></tr>';
							get_last_index = data.model[index].id;
						}
						$("#scroll_inactive").append(html);
						read_request = true;
					}else{
						read_request = false;
						$("#scroll_inactive").append("<div data-alert class='alert alert-info center'>No hay más datos</div>");
					}
					$(".last_index_inactive").attr("id",get_last_index);
				},
				error: function(e){
					alert(error);
				}
			});
		}
	}


	function readMoreInactiveWithOutUser(){
		var id = $(".last_index_inactive_without_user").attr("id");
		var get_last_index;
		var html = "";
		var request = new Object();
		request.user_id = $("#user_id").val();
		request.from_date = $("#from_date").val();
		request.to_date = $("#to_date").val();
		request.id = $(".last_index_inactive_without_user").attr("id");
		
		if(id){
			console.log(id);
			$.ajax({
				type: "GET",
				url: "/dashboard/scroll_inactive_without_user",
				data: request,
				success: function(data){
					$(".before_inactive_without_user").html("");
					if(data.response == true){
						for(index in data.model){
							html+='<tr><td><div><span class="badge" style="background-color: '+data.model[index].color+' ">'+data.model[index].status_name+' </span></div><div></div><a href="/customers/'+data.model[index].id+'/show"><h4> '+data.model[index].customer_name+'</h4></a><div class="action_created"></div><div class="row"><div class="col">'+data.model[index].phone+'</div><div class="col">'+data.model[index].email+'</div><div class="col">'+data.model[index].source_name+'</div></div></td></tr>';
							get_last_index = data.model[index].id;
						}
						$("#scroll_inactive_without_user").append(html);
						read_request = true;
					}else{
						read_request = false;
						$("#scroll_inactive_without_user").append("<div data-alert class='alert alert-info center'>No hay más datos</div>");
					}
					$(".last_index_inactive_without_user").attr("id",get_last_index);
				},
				error: function(e){
					alert(error);
				}
			});
		}
	}


	$(document).ready(function(){
		$('#scroll_active').on('scroll', function() {
			if(read_request !== false){
				$(".before_active").html("<div data-alert class='alert alert-success center'>Cargando</div>");
				if(scroll_active){
					clearTimeout(scroll_active);//Limpiar peticion de scroll anterior
				}
				console.log("scrolltop = " + $(window).scrollTop());
				console.log("document.height = " + $(document).height());
				console.log("window.height = " + $(window).height());
				console.log("result = " + ($(document).height() - $(window).height() - 100));

				/*if($(window).scrollTop() >= $(document).height() - $(window).height() - 100){*/
					scroll_active = setTimeout(function(){
						scroll_active = null; //lanzamos de nuevo el scroll
						readMore();
					},1000);
				/*}*/
			}
		});

		$('#scroll_inactive').on('scroll', function() {
			if(read_request !== false){

				$(".before_inactive").html("<div data-alert class='alert alert-success center'>Cargando</div>");
				if(scroll_inactive){
					clearTimeout(scroll_inactive);//Limpiar peticion de scroll anterior
				}
				if($(window).scrollTop() >= $(document).height() - $(window).height() - 100){
					scroll_inactive = setTimeout(function(){
						scroll_inactive = null; //lanzamos de nuevo el scroll
						readMoreInactive();
					},1000);
				}
			}
		});

		$('#scroll_inactive_without_user').on('scroll', function() {
			if(read_request !== false){

				$(".before_inactive_without_user").html("<div data-alert class='alert alert-success center'>Cargando</div>");
				if(scroll_inactive_without_user){
					clearTimeout(scroll_inactive_without_user);//Limpiar peticion de scroll anterior
				}
				if($(window).scrollTop() >= $(document).height() - $(window).height() - 100){
					scroll_inactive_without_user = setTimeout(function(){
						scroll_inactive_without_user = null; //lanzamos de nuevo el scroll
						readMoreInactiveWithOutUser();
					},1000);
				}
			}
		});
	});
</script>
<style type="text/css">
	#scroll_active{
	    display: block;
	    overflow-y: auto;
	    overflow-x: hidden;
	    /*white-space: nowrap;*/
	    height: 500px;
  	}

  	#scroll_inactive{
	    display: block;
	    overflow-y: auto;
	    overflow-x: hidden;
	    /*white-space: nowrap;*/
	    height: 500px;
  	}

  	#scroll_inactive_without_user{
	    display: block;
	    overflow-y: auto;
	    overflow-x: hidden;
	    /*white-space: nowrap;*/
	    height: 500px;
  	}

  	a#graphic{
	    text-align: center !important;
	    color: white;
	    display: block;
	    padding-top: 5px;  
	  }

	.time-success{
		background-color: #00c853;
		border-bottom-left-radius: 20px;
	    border-bottom-right-radius: 20px;
	    border-top-left-radius: 20px;
	    border-top-right-radius: 20px;
	    width: 50%;
	    color: #fff;
	    text-align: center;
	}
	.time-warning{
		background-color: #f5a623;
		border-bottom-left-radius: 20px;
	    border-bottom-right-radius: 20px;
	    border-top-left-radius: 20px;
	    border-top-right-radius: 20px;
	    width: 50%;
	    color: #fff;
	    text-align: center;
	}
	.time-danger{
		background-color: #fb2e2e;
		border-bottom-left-radius: 20px;
	    border-bottom-right-radius: 20px;
	    border-top-left-radius: 20px;
	    border-top-right-radius: 20px;
	    width: 50%;
	    color: #fff;
	    text-align: center;
	}
</style>
@endsection
