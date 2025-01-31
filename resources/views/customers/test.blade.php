@extends('layout')

@section('content')

<div class="panel-group">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse1">Collapsible list group</a>
      </h4>
    </div>
    <div id="collapse1" class="panel-collapse collapse">
      <ul class="list-group">
        <li class="list-group-item">One</li>
        <li class="list-group-item">Two</li>
        <li class="list-group-item">Three</li>
      </ul>
      <div class="panel-footer">Footer</div>
    </div>
  </div>
</div>

      
@endsection