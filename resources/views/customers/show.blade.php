 @extends('layout')
<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>


@section('content')
<hr>
@if($model != null)

@if($model->isBanned())
<h1 style="color:red;"> <i class="fa fa-exclamation-circle" style="color:gray; "></i> {{$model->name}} <br> </h1>
@else

<h1 class="customer_name"> {{$model->name}}


  <br>
</h1>
<div class="customer_created_at  gray-dark"><small>ID: {{$model->id}}</small></div>
<div class="customer_created_at  gray-dark"><small>{{$model->created_at}}</small></div>
@endif

{{-- Alertas --}}
@if (session('status'))
<div class="alert-primary alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
  {!! html_entity_decode(session('status')) !!}
</div>
@endif
@if (session('statusone'))
<div class="alert-warning alert-dismissible" role="alert">
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
  
    <div class="row">
      <!-- Primera columna -->
      <div class="col-md-4">

      

        <div class="card">
          <h5 class="card-title card-header">Detalles</h5>
          <div class="card-body">


            <p class="card-text">
              <div>
<!--
                <form method="POST" action="/customers/start-chat" id="wire_chat">
                  @csrf
                  <input type="hidden" name="customer_id" value="{{ $model->id }}">
                  <input type="hidden" name="mensaje" value="¡Hola, te hablo de parte de maquiempandas! ¿En qué puedo ayudarte?">
                  
                  <button type="submit" class="btn btn-outline-primary btn-sm mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke-width="1.5" 
                    stroke="currentColor" 
                    width="16" height="16" 
                    class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" 
                    d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                  </svg>
                      Iniciar Chat
                  </button>
              </form>

            -->
              
              </div>
            
              <div>
                
                <span class="lavel"><strong>Imagen:</strong></span>
                <a href="{{$model->image_url}}">Link</a>
                
              
              </div>
            <div><span class="lavel"><strong>Nombre:</strong></span> {{$model->name}}</div>
             @if(!empty($model->business))
            <div><span class="lavel"><strong>Empresa:</strong></span> {{$model->business}}</div>
            @endif
            
            <!-- teléfono -->
            @php
                $phone1 = $model->phone ? '+' . ltrim($model->getInternationalPhone($model->phone), '+') : null;
                $phone2 = $model->phone2 ? '+' . ltrim($model->getInternationalPhone($model->phone2), '+') : null;
            @endphp

            @if($phone1)
              <div>
                <span class="label"><strong>Teléfono:</strong></span>
                <span>{{ $phone1 }}</span>
              </div>
            @endif

            @if($phone2 && $phone2 !== $phone1)
              <div>
                <span class="label"><strong>Celular:</strong></span>
                <span>{{ $phone2 }}</span>
              </div>
            @endif
            <!-- fin teléfono -->



            <div><span class="lavel"><strong>Email:</strong></span> {{$model->email}}</div>
            <div><span class="lavel"><strong>País:</strong></span> {{$model->country}}</div>
            <div><span class="lavel"><strong>Estado:</strong></span> @if(isset($model->status)&& !is_null($model->status)&&$model->status!='')
              <span class="badge" style="background-color: @if(isset($model->status) && ($model->status != '')) {{$model->status->color}};@else gray @endif">{{$model->status->name}}</span> @endif
            </div>
            <div><strong>Asignado a:</strong>
              @if(isset($model->user)&& !is_null($model->user)&&$model->user!=''){{$model->user->name}} @else Sin asignar @endif
            </div>
            <div><span class="lavel"><strong>Interes:</strong></span>
              <span style="background-color: #66C366; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left;">{{$model->scoring_interest}}</span>
            </div>
            <div class="row">
              <div class="col-md-6 lavel"><span class="lavel"><strong>Perfil:</strong></span></div>
              <div class="col-md-6 scoring">
                <div class="scoring-stars">
                  @php
                    $stars = $model->getScoringToNumber();
                  @endphp
                
                  @for ($i = 1; $i <= 4; $i++)
                    @if ($i <= $stars)
                      <span style="color: gold; font-size: 18px;">★</span>
                    @else
                      <span style="color: lightgray; font-size: 18px;">☆</span>
                    @endif
                  @endfor
                </div>
              </div>
            </div>


            <div>
              <span class="lavel"><strong>No empanadas:</strong></span>{{$model->count_empanadas}}
            </div>

            <div>
              <span class="lavel"><strong>Rd Station: </strong><a href="{{$model->rd_public_url}}" target="_blank">Link</a></span>

            </div>

            <div>
              <span class="lavel"><strong>Notas:</strong></span> {{$model->notes}}
            </div>

            <div>
              <span class="lavel"><strong>Campaña:</strong></span> {{$model->campaign_name}}
            </div>
           
            </p>

            <?php $customer = $model; ?>






            @if($actual)
            <div>
              <a href="/customers/{{$model->id}}/edit">
                <span class="btn btn-primary btn-sm" aria-hidden="true">Editar</span>
              </a>
              @if(is_null($model->user_id) || $model->user_id==0)
              <a href="/customers/{{$model->id}}/assignMe">
                <span class="btn btn-primary btn-sm" aria-hidden="true">Asignarme</span>
              </a>
              @endif

              @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)

              <a href="/customers/{{ $model->id }}/destroy">
                <span class="btn btn-sm btn-danger" aria-hidden="true" title="Eliminar">Eliminar</span></a>
              @endif
              @endif
              <button class="btn btn-primary btn-sm" id="btnCopiar" size="5"> POA</button>
              <a href="/orders/create/{{ $model->id }}">
                <span class="btn btn-primary btn-sm" aria-hidden="true" title="Eliminar">Cotizar</span>
              </a>
            </div>





          </div>
        </div>
        <br>
        @include('customers.show_partials.orders')
        <div class="card">
          <h5 class="card-title card-header">Dirección</h5>
          <div class="card-body">
            <p class="card text">
            <div><span class="lavel"><strong>Documento:</strong></span> {{$model->document}}</div>
            <div><span class="lavel"><strong>Dirección:</strong></span> {{$model->address}}</div>
            <div><span class="lavel"><strong>Departamento:</strong></span> {{$model->department}}</div>
            <div><span class="lavel"><strong>Ciudad:</strong></span> {{$model->city}}</div>
            <div><span class="lavel"><strong>Usuario actualizador:</strong></span>@if(isset($model->updated_user)){{$model->updated_user->name}}@endif
            </div>
            <div><span class="lavel"><strong>Tamaño de empandas:</strong></span> {{$model->empanadas_size}}</div>
            <div><span class="lavel"><strong>Número de sedes:</strong></span> {{$model->number_venues}}</div>
            <div><span class="lavel"><strong> Última Fecha de actualización:</strong></span> {{$model->updated_at}}</div>
            </p>
          </div>
        </div>
        <div class="card">
          <h5 class="card-title card-header">Detalle Contacto</h5>
          <div class="card-body">
            <p class="card text">
            <div><span class="lavel"><strong>Nombre:</strong></span> {{$model->contact_name}}</div>
            <div><span class="lavel"><strong>email:</strong></span> {{$model->contact_email}}</div>
            <div><span class="lavel"><strong>Telefono:</strong></span> {{$model->contact_phone2}}</div>
            <div><span class="lavel"><strong>Telefono:</strong></span> {{$model->contact_position}}</div>

            </p>
          </div>
        </div>

        <div class="card">
          <h5 class="card-title card-header">Empresa</h5>
          <div class="card-body">
            <p class="card-text">

            <div><span class="lavel"><strong>Empresa:</strong></span>{{$model->business}}</div>

            <div><span class="lavel"><strong>Cargo:</strong></span>{{$model->position}}</div>


            <div><span class="lavel"><strong>Producto Adquirido:</strong></span>{{$model->bought_products}}</div>
            <div><span class="lavel"><strong>Valor Cotizado:</strong></span>{{$model->total_sold}}</div>

            <div><span class="lavel"><strong>Fecha de Compra:</strong></span>{{$model->purchase_date}}</div>

            <div><span class="lavel"><strong>Producto consultado:</strong></span>
              @if(isset($model->product)){{$model->product->name}} @endif</div>



            <div><strong>Fuente:</strong>
              @if(isset($model->source)&& !is_null($model->source)&&$model->source!=''){{$model->source->name}}
              @endif

            </div>
            </p>
          </div>
        </div>

      </div>
      <!-- fin columna 1 -->
      <!-- Segunda columna -->

      <div class="col-md-8">
        @include('customers.partials.actions_form')
        @include('customers.show_partials.actions_widget_wp')


        @include('customers.partials.action_opportunity')
        @include('customers.partials.action_sale_form')
        @include('customers.partials.action_PQR')
        @include('customers.partials.action_spare')


        <!-- inicio acordeon -->


      <div class="card">
        <div class="card-body">


          <p class="card-text">
            <!-- BODYMS -->


            @if($actual)
          <div class="accordion">
            <div class="card">
              <div class="card-header" id="headingOne">
                <h3>
                  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Envío de correos
                  </button>
                </h3>
              </div>
              <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                <form action="/customers/{{$model->id}}/action/mail" method="POST">
                  {{ csrf_field() }}
                  <div>
                    <select name="email_id" id="email_id">
                      <option value="">Seleccione una opción</option>
                      @foreach($email_options as $email_option)
                      <option value="{{$email_option->id}}">{{$email_option->subject}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
                    <input class="btn btn-primary btn-sm" type="submit" value="Enviar correo">
                  </div>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header" id="headingThree">
                <h3>
                  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseThree" id="tabSurvey" aria-expanded="true" aria-controls="collapseThree">
                    Encuesta
                  </button>
                </h3>
              </div>
              <div id="collapseThree" class="collapse" arial-labelledby="collapseThree">

                <ul class="tabs">
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Productos')">Productos</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Servicios')">Servicios</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Cproductos')">Crear de productos</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Cservicios')">Crear de servicios</a></li>
                </ul>

                <div id="Productos" class="tab-content">

                  <div class="table">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Preguntas</th>
                          <th>1</th>
                          <th>2</th>
                          <th>3</th>
                          <th>4</th>
                          <th>5</th>
                          <th>6</th>
                          <th>7</th>
                          <th>8</th>
                          <th>9</th>
                          <th>10</th>
                        </tr>
                      </thead>
                      <tbody>

                        @php
                        $last_date = "";

                        @endphp


                        @foreach($metas as $item)
                        @if($item->parent_id == 1)
                        @if($item->created_at != $last_date)
                        <tr>
                          <td colspan="11">
                            <h3>{{$item->created_at}}</h3>
                          </td>
                        </tr>
                        @endif
                        @php
                        $last_date = $item->created_at;
                        @endphp

                        <tr>

                          @if($item->type_id == 1)
                          <th>{{$item->name}}</th>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 1) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 2) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 3) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 4) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 5) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 6) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 7) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 8) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 9) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 10) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                        </tr>
                        @elseif($item->type_id == 4)
                        <tr>
                          <th>{{$item->name}}</th>

                          <td colspan="4">
                            {{$item->value}}
                          </td>
                        </tr>

                        @endif


                        @endif
                        @endforeach


                      </tbody>


                    </table>

                  </div>

                </div>

                <div id="Servicios" class="tab-content">

                  <div class="table">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Preguntas</th>
                          <th>1</th>
                          <th>2</th>
                          <th>3</th>
                          <th>4</th>
                          <th>5</th>
                          <th>6</th>
                          <th>7</th>
                          <th>8</th>
                          <th>9</th>
                          <th>10</th>
                        </tr>
                      </thead>
                      <tbody>

                        @php
                        $last_date = "";

                        @endphp


                        @foreach($metas as $item)
                        @if($item->parent_id == 8)
                        @if($item->created_at != $last_date)
                        <tr>
                          <td colspan="11">
                            <h3>{{$item->created_at}}</h3>
                          </td>
                        </tr>
                        @endif
                        @php
                        $last_date = $item->created_at;
                        @endphp

                        <tr>

                          @if($item->type_id == 1)
                          <th>{{$item->name}}</th>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 1) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 2) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 3) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 4) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 5) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 6) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 7) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 8) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 9) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 10) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                        </tr>
                        @elseif($item->type_id == 4)
                        <tr>
                          <th>{{$item->name}}</th>

                          <td colspan="4">
                            {{$item->value}}
                          </td>
                        </tr>

                        @endif


                        @endif
                        @endforeach


                      </tbody>


                    </table>

                  </div>


                </div>

                <div id="Cproductos" class="tab-content">

                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10">

                      <form action="/metadata/{{$model->id}}/store" method="POST" style="margin-left:10px; margin-right:10px;">
                        {{ csrf_field() }}


                        <div class="table">
                          <table class="table table-striped">
                            <thead>

                              <tr>
                                <th></th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                              </tr>
                            </thead>
                            <tbody>

                              @foreach($meta_data as $item)
                              @if($item->parent_id ==1)

                              @if($item->type_id == 1)
                              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">

                              <tr>

                                <th>{{$item->value}}</th>
                                <td><input type="radio" name="meta_{{$item->id}}" value="1"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="2"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="3"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="4"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="5"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="6"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="7"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="8"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="9"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="10"></td>
                              </tr>

                              @elseif($item->type_id == 4)
                              <tr> <br>
                                <th> {{$item->value}}</th>
                                <td colspan="10">
                                  <textarea name="meta_{{$item->id}}" id="meta_{{$item->id}}" rows="5" style="width:100%" placeholder="{{$item->value}}"></textarea>
                                </td>
                              </tr>
                              @endif



                              @endif
                              @endforeach
                              <tr>
                                <th>Audiencia</th>


                                <td colspan="10">
                                  <select name="audience_id" id="audience_id" class="form-control">
                                    @foreach($audiences as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <td colspan="10" class="td_submit"><input type="submit" value="Enviar" class="btn btn-primary" size="7"> </td>
                              </tr>

                            </tbody>

                          </table>
                        </div>
                      </form>
                    </div>
                  </div>

                </div>

                <div id="Cservicios" class="tab-content">
                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10">

                      <form action="/metadata/{{$model->id}}/store" method="POST" style="margin-left:10px; margin-right:10px;">
                        {{ csrf_field() }}


                        <div class="table">
                          <table class="table table-striped">
                            <thead>

                              <tr>
                                <th></th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                              </tr>
                            </thead>
                            <tbody>

                              @foreach($meta_data as $item)
                              @if($item->parent_id ==8)

                              @if($item->type_id == 1)
                              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
                              <tr>

                                <th>{{$item->value}}</th>
                                <td><input type="radio" name="meta_{{$item->id}}" value="1"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="2"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="3"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="4"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="5"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="6"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="7"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="8"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="9"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="10"></td>
                              </tr>

                              @elseif($item->type_id == 4)
                              <tr> <br>
                                <th> {{$item->value}}</th>
                                <td colspan="10">
                                  <textarea name="meta_{{$item->id}}" id="meta_{{$item->id}}" rows="5" style="width:100%" placeholder="{{$item->value}}"></textarea>
                                </td>
                              </tr>
                              @endif

                              @endif
                              @endforeach

                              <tr>
                                <th>Audiencia</th>


                                <td colspan="10">
                                  <select name="audience_id" id="audience_id" class="form-control">
                                    <option value=" ">Seleccionar...</option>
                                    @foreach($audiences as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <td colspan="10"></td>
                                <td> <input type="submit" value="Enviar" class="btn btn-primary" size="7"> </td>
                              </tr>

                            </tbody>

                          </table>
                        </div>
                      </form>
                    </div>
                  </div>

                </div>
              </div>
              <!-- files -->
              <div id="filesParent">
                <div class="card">
                  <div class="card-header" id="headingTwo">
                    <h2>
                      <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                        Archivos
                      </button>
                    </h2>
                  </div>
                  <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#filesParent">
  
                    <form method="POST" action="/customer_files" enctype="multipart/form-data">
                      {{ csrf_field() }}
                      <div class="form-group">
                        <div class="container">
                          <div class="row">
                            <div class="col">Seleccione el archivo:</div>
                            <div class="col"><input type="file" class="form-control" id="file" name="file" placeholder="email"></div>
                            <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
                            <div class="col"><input type="submit" class="btn btn-sm btn-primary glyphicon glyphicon-pencil" aria-hidden="true"></div>
                          </div>
                        </div>
  
                      </div>
                    </form>
  
  
  
                    <div>
                      <div class="table">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>#</th>
  
                              <th>Url</th>
                              <th>Fecha de Creación</th>
                              <th>Usuario</th>
                              <th>Estado</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($model->customer_files as $file)
                            <tr>
                              <th>{{$file->id}}</th>
  
                              <th><a href="/public/files/{{$file->customer_id}}/{{$file->url}}">{{$file->url}}</a></th>
                              <th>{{$file->created_at}}</th>
                              <th>{{ $file->creator?->name ?? 'Sin usuario' }}</th>
                              <th>
                                @if($file->status === 'OK')
                                    <span class="badge badge-success">OK</span>
                                @else
                                    <span class="badge badge-danger">MISSING</span>
                                @endif
                              </th>
                              <th>
                                <a class="btn btn-danger btn-sm" href="/customer_files/{{$file->id}}/delete" title="Eliminar">Eliminar</a>
                              </th>
                              
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
  
                      </div>
                    </div>
                  </div>
  
                </div>
              </div>
              <!-- end files -->

              <div class="card">
                <div class="card-header" id="headingfour">
                  <h3>
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsefour" aria-expanded="true" aria-controls="collapsefour">
                      POA
                    </button>
                  </h3>
                </div>

                <div id="collapsefour" class="collapsed" aria-labelledby="headingfour" data-parent="#accordifourxample">

                  @php
                  $last_date = "";

                  @endphp
                  <div class="table">
                    <table class="table table-striped">
                      @foreach($metas as $item)
                      @if($item->parent_id != 1 && $item->parent_id != 8 )
                      @if($item->created_at != $last_date)
                      <thead>
                        <th>Preguntas</th>
                        <th>Respuestas</th>
                      </thead>
                      <tbody>
                        <tr>
                          <td colspan="11">
                            <h3>{{$item->created_at}}</h3>
                          </td>
                        </tr>
                        @endif
                        @php
                        $last_date = $item->created_at;
                        @endphp


                        <tr>
                          <th>{{$item->name}}</th>
                          <td>
                            {{$item->value}}
                          </td>
                        </tr>

                      </tbody>

                      @endif
                      @endforeach
                    </table>
                  </div>
                </div>
              </div>


              <br>



              <br>




              <br>

              @include('customers.show_partials.history')

              @endif

              @else
              El prospecto no existe

              @endif

              <!-- end BODYMS -->
              </p>



            </div>
          </div>
        </div>

      </div>
       <!-- fin acordeon -->
      </div>


  </form>
  
  <script>
    document.getElementById("btnCopiar").addEventListener("click", function() {
      var textoCopiar = "https://arichat.co/metadata/{{$model->id}}/create/poe/40";

      var elementoInput = document.createElement("input");
      elementoInput.value = textoCopiar;
      document.body.appendChild(elementoInput);

      elementoInput.select();
      document.execCommand("copy");

      document.body.removeChild(elementoInput);
      alert("¡URL copiada al portapapeles!");
    });
  </script>
</div>
@endsection