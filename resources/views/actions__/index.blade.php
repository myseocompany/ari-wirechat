@extends('layout')

@section('content')

<h1>Acciones</h1>
@if($model instanceof \Illuminate\Pagination\LengthAwarePaginator )
Registro <strong>{{ $model->currentPage() * $model->perPage() - ( $model->perPage() - 1 ) }}</strong> a <strong>{{ min($model->currentPage() * $model->perPage(), $model->total()) }}</strong> de <strong>{{$model->total()}}</strong>
@endif
<form action="/actions/" method="GET" id="filter_form">
  <div class="row">
    <div class="col"><select name="filter" class="custom-select" id="filter" onchange="update()">
        <option value="">Seleccione tiempo</option>
        <option value="0" @if ($request->filter == "0") selected="selected" @endif>hoy</option>
        <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>ayer</option>
        <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>esta semana</option>
        
        <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>semana pasada</option>
        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>mes pasado</option>
        <option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>este mes</option>
        <option value="-7" @if ($request->filter == "-7") selected="selected" @endif>ultimos 7 dias</option>
        <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>ultimos 30 dias</option>
        
      </select></div>
    <div class="col"><input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
    <br>  
    <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
</div>

<div class="col">
 
{{-- Combo de estados --}}
<select name="type_id" class="slectpicker custom-select" id="type_id" onchange="submit();">
        <option value="">Tipo acción...</option>
        @foreach($action_options as $item)
          <option value="{{$item->id}}" @if ($request->type_id == $item->id) selected="selected" @endif>
             {{ $item->name }}
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <!--  
*
*    Combo de usuarios
*
-->
      <select name="creator_user_id" class="custom-select" id="creator_user_id" onchange="submit();">
        <option value="">Usuario creador</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->creator_user_id == $user->id) selected="selected" @endif>
             <?php echo substr($user->name, 0, 10); ?>
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <!--  
*
*    Combo de usuarios
*
-->
<select name="user_id" class="custom-select" id="user_id" onchange="submit();">
        <option value="">Dueño del cliente</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
             <?php echo substr($user->name, 0, 10); ?>
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
</div>

  </div>
       
     
    </form>
<div class="d-flex flex-column">
  @foreach ($model as $item)
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap align-items-center mb-2">
          @if(isset($item->customer->status))
            <span class="badge mr-2" style="background-color: {{$item->customer->status->color}} ">
              {{$item->customer->status->name}}
            </span>
          @endif
          <span class="text-muted small">{{$item->created_at}} - {{$item->getTypeName()}}</span>
        </div>

        <a href="/customers/{{$item->customer_id}}/show"><h4 class="mb-2"> {{$item->getCustomerName()}}</h4></a>

        <div class="action_note mb-2">{{$item->note}}</div>

        @if(!empty($item->url))
          @php
            $lowerUrl = Str::lower($item->url);
            $parsedPath = parse_url($lowerUrl, PHP_URL_PATH);
            $normalizedPath = $parsedPath ?? $lowerUrl;
            $isAudio = Str::endsWith($normalizedPath, ['.mp3', '.wav', '.ogg', '.oga', '.m4a', '.m4b', '.webm']);
            $mime = Str::endsWith($normalizedPath, '.mp3') ? 'audio/mpeg' :
              (Str::endsWith($normalizedPath, '.wav') ? 'audio/wav' :
              (Str::endsWith($normalizedPath, ['.ogg', '.oga']) ? 'audio/ogg' :
              (Str::endsWith($normalizedPath, ['.m4a', '.m4b']) ? 'audio/mp4' :
              (Str::endsWith($normalizedPath, '.webm') ? 'audio/webm' : 'audio/mpeg'))));
          @endphp
          @if($isAudio)
            <audio controls preload="metadata" class="w-100 mt-2" src="{{ $item->url }}">
              <source src="{{ $item->url }}" type="{{ $mime }}">
              Tu navegador no soporta el audio.
            </audio>
          @endif
        @endif

        <div class="action_created"></div>

        @if(isset($item->customer))
          <div class="d-flex flex-wrap text-muted small">
            <span class="mr-3 mb-1">Asesor: {{$item->customer->user->name ?? 'N/A'}}</span>
            @if(isset($item->creator))
              <span class="mr-3 mb-1">Creador: {{$item->creator->name}}</span>
            @endif
            <span class="mr-3 mb-1">{{$item->customer->phone}}</span>
            <span class="mr-3 mb-1">{{$item->customer->email}}</span>
            @if(isset($item->customer->project))
              <span class="mr-3 mb-1">{{$item->customer->project->name}}</span>
            @endif
            @if(isset($item->customer->source))
              <span class="mr-3 mb-1">{{$item->customer->source->name}}</span>
            @endif
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>
@if($model instanceof \Illuminate\Pagination\LengthAwarePaginator )

{{ $model->appends(request()->input())->links() }}
@endif
@endsection
