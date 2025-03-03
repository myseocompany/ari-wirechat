
@extends('layout')
@section('content')



<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<div style="overflow-x:auto;">


<form class="form" action="/optimize/customers/mergeDuplicates/" method="GET">
    {{ csrf_field() }}

    <script>
      function updateControl(value, target) {
          var element = document.getElementById(target);
          if (element) {
              if (element.tagName === "SELECT") {
                  element.value = value;
                  // Disparar el evento 'change' manualmente
                  var event = new Event("change", { bubbles: true });
                  element.dispatchEvent(event);
              } else {
                  element.value = value;
              }
          }
      }
  
      // Asignamos la función al evento change de los selects dinámicamente
      document.addEventListener("DOMContentLoaded", function() {
          document.querySelectorAll("select").forEach(select => {
              select.addEventListener("change", function() {
                  updateControl(this.value, this.id);
              });
          });
      });
  </script>
  

    <table class="table table-striped">   

       <tr>
            <td>Registros</td>
            <td>Editar</td>
            <?php $cont2 = 0; ?>
            @foreach($model as $item)
            <input type="hidden" name="customer_id_all[]" value="{{$item->id}}"  class="d-none2">
                
            <td>
                <input type="radio" name="customer_id" value="{{$item->id}}" @if($cont2==0) checked="checked" @endif >
                <a href="/customers/{{$item->id}}/show">{{$item->id}}</a> 
            </td>
            <?php $cont2++; ?>
            @endforeach
              
        </tr>

  
        @foreach($model[0]->getAttributes() as $key => $value)

            @if( $key!="id" && ($key != "request"))
            <tr class="row_ms">      
                <td><strong>{{$key}}:</strong></td>
               
                <td>  
                      @if($key=='status_id')
                     <select name="status_id" id="status_id" style="width:200px">
                        <option value="">Seleccione un estado</option>
                        @foreach($statuses_options as $status_option)
                        <option value="{{$status_option->id}}" onclick="updateControl('{{$status_option->id}}','{{$key}}')"   >{{$status_option->name}}
                        </option>
                        @endforeach
                    </select>
                     @endif

                     @if($key=='user_id')
                      <select name="user_id" id="user_id" style="width:200px">
                        <option value="" >Seleccione un usuario:</option>
                        @foreach($user as $subitem)
                          <option value="{{$subitem->id}}">
                            {{$subitem->name}}-{{$subitem->id}}
                          </option>
                        @endforeach
                      </select>
                      <script>
                        funcion changeUserId(this){
                          var selected = $(this).val();
                          $("#user_id").val(selected);
                        }
                      </script>
                     @endif

                       @if($key=='source_id')
                     <select name="source_id" id="source_id" style="width:200px">
                      <option value="" >Seleccione una fuente</option>
                      @foreach($customers_source as $customer_source)
                      <option value="{{$customer_source->id}}" onclick="updateControl('{{$customer_source->id}}','{{$key}}')"   >
                        {{$customer_source->name}}
                      </option>
                      @endforeach
                    </select>
                     @endif
                   

                     

                     



                    <input style="width:200px"; 
                    @if($key=='date_bought') type="text" readonly="" @endif @if($key=='created_at') 
                    type="text" readonly="" @endif @if($key=='updated_at') -ype="text" readonly @endif @if($key=='status_id') type="hidden" readonly="" @endif 
                    @if($key=='user_id') type="hidden" readonly=""  @endif 
                    @if($key=='source_id') type="hidden" readonly="" @endif
                    @if($key=='scoring_interest') type="number" readonly="" @endif
                    @if($key=='vas') type="number" readonly="" @endif
                    @if($key=='product_id') type="hidden" readonly="" @endif
                    @if($key=='updated_user_id') type="hidden" readonly="" @endif
                    @if($key=='gender') readonly=""  @endif
                    @if($key=='scoring_profile') readonly=""  @endif
                    

                    value="{{$value}}" type="text" name="{{$key}}" id="{{$key}}"></input>

                </td>
               

                <?php $cont = 0; 


                ?>
            @foreach($model as $item)
                <?php $data = $item->toArray();?>
                @if($cont==0)
                <script type="text/javascript">
                
                    $("#{{$key}}").val("{{$data[$key]}}");
                      
                </script>
              @endif
                <td class="ms_row">

                  <div class="form-check form-check-inline">

                   
                    <input class="form-check-input" type="radio"  id="{{$key}}_{{$item->id}}" 
                      value="{{$data[$key]}}" @if($cont==0) checked="checked" @endif  
                        onclick="updateControl('{{$data[$key]}}','{{$key}}')" name="{{$key}}_1">
                   
                    <label class="form-check-label" for="{{$key}}">
                        {{$controller->getModelText($key, $item)}} {{$data[$key]}}</label>

                    


                  </div>
                </td>

                <?php $cont++; ?>
            @endforeach
            
             </tr>
            @endif
        @endforeach
        <tr>
            <td><strong>Acciones</strong></td>

               <td></td>

             @foreach($model as $item)

            <td>
              

                 
                <ul>
                    @foreach($item->actions as $item_action)

                         <li> 
                             <input type="checkbox" name="action_all[]" checked="checked" value="{{ $item_action->id }}">
                              {{ $item_action->note }}

                           <div>@if(isset($item_action->type)&& !is_null($item_action->type)&& $item_action->type!=''){{$item_action->type->name}}@endif
                           </div>
                          <div>@if(isset($item_action->creator)&& !is_null($item_action->creator)&& $item_action->creator!=''){{$item_action->creator->name}}@else Automático @endif
                          </div>
                          <div>@if($item_action->type_id==2 || $item_action->type_id==4) {{$item_action->getEmailSubject()}}
                            <br> {{$item_action->note}} @else {{$item_action->note}}@endif</div>
                          <div>
                         </li>
                    @endforeach
                       
                </ul>
                   <?php
                    if(isset($_POST['submit'])){
                     if(!empty($_POST['action_all'])){
                 
                        foreach($_POST['action_all'] as $selected){
                        
                         }
                     }
                    }

                  

                    ?>

            </td>
            @endforeach
        </tr>
    
        <tr>
            <td><strong>Archivos</strong></td>
            <td></td>
            @foreach($model as $item)    
               <td>
                
                <ul>
                    @foreach($item->files as $item_file)

                         <li> 
                             <input type="checkbox" name="file_all[]" checked="checked" value="{{ $item_file->id }}">
                              {{ $item_file->url }}

                           
                         </li>
                    @endforeach
                       
                </ul>
                
               </td>
               @endforeach
             
        </tr>





    </table>
    <div style="text-align: center;">
        <input type="submit" name="" value="enviar" class="btn btn-primary my-2 my-sm-0">
    </div>
</form>
</div>


@endsection