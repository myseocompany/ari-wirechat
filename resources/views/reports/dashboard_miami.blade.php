@extends('layoutGuess')
@section('content')
<meta http-equiv="refresh" content="1800">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <div  id="dashboard_public">
    <div class="row">
      <div class="col-md-4">
        @include("reports.dashboard_miami_list")
      </div>
      <div class="col-md-8">
        @include("reports.dashboard_miami_group")
      </div>
    </div>
  </div>
@endsection