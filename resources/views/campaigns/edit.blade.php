@extends('layout')
@section('content')
<h1>Editar Campaña : {{$campaign->name}}</h1>
<div id="divmsg" style="display:none;" class="alert alert-primary" role="alert"></div>
<meta name="csrf-token" content="{{ csrf_token() }}">
<form>
{{ csrf_field() }}
    <table class="table table-bordered" id="dynamic_field">
        <fieldset class="scheduler-border">
        <legend class="scheduler-border">Campaña:</legend>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" id="editCampaignName" name="editCampaignName" value="{{$campaign->name}}">
                    <input type="hidden" class="form-control" id="editCampaignId" name="editCampaignId" value="{{$campaign->id}}">
                    <span class="input-group-btn">
                         <button type="submit" id="submitEdit" class="btn btn-success">Editar</button>
                    </span>
                </div>
            </div>          
        </fieldset>
        
        <fieldset class="scheduler-border">
        <legend class="scheduler-border">Mensajes Actuales:</legend>
        
            @foreach($messages as $item)
            <div class="form-group" id="container{{$item->id}}">
                <div class="input-group">
                  <!--<input type="text" name="message{{$item->id}}" id="message{{$item->id}}" class="form-control name_list" value="{{$item->text}}"/>-->
                  <textarea rows="5" class="form-control" id="message{{$item->id}}" name="message{{$item->id}}">{{$item->text}}</textarea>

                  <span class="input-group-btn">
                    <button type="submit" id="update" class="btn btn-success" onclick="updateMessage('{{$campaign->id}}','{{$item->id}}','{{$item->text}}')"  >Editar</button>
                  </span>
                  <span class="input-group-btn">
                    <button type="submit" id="delete" class="btn btn-danger" onclick="deleteMessage('{{$item->id}}')"  >Eliminar</button>
                  </span>
                </div>
            </div>
            @endforeach
        
        </fieldset>

        <fieldset class="scheduler-border">
        <legend class="scheduler-border">Añadir Nuevo Mensaje:</legend>
            <div class="form-group">
                <label for="name">Nota:</label>
                <!--<input type="text" class="form-control" id="addMessageName" name="addMessageName" value="">-->
                <textarea rows="5" class="form-control" id="addMessageName" name="addMessageName"></textarea>
                <input type="hidden" class="form-control" id="addCampaignId" name="addCampaignId" value="{{$campaign->id}}">
            </div>
            <div class="form-group">
                <button type="submit" id="submitAdd" class="btn btn-primary">Guardar</button>

            </div>              
        </fieldset>
    </table>
</form>

@endsection