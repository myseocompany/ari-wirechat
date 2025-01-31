@extends('layout')

@section('content')
<div class="container">
    <div class="row">
        <!--<div class="col-md-8 col-md-offset-2">-->
            <div class="col-md-12">
            <div class="panel panel-default">
            	<div class="panel-heading">
                    <p>Panel de control</p>
                </div>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
