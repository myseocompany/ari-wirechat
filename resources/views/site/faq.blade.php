@extends('layout')

@section('content')
<div class="container">
    <div class="row">
        <!--<div class="col-md-8 col-md-offset-2">-->
            <div class="col-md-12">
            <div class="panel panel-default">

            	@if(isset(Auth::user()->id))
                <div class="panel-heading">
                    <h1>Preguntas frecuentes</h1>
                </div>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
			    <div class="accordion" id="accordionExample">
			        <div class="card-header" id="headingFilter">
			            <h2 class="mb-0">
			                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="true" aria-controls="collapseFilter">
			                ¿Dónde puedo ver los videos de introducción?
			                </button>
			            </h2>
			        </div>
			    </div>
				<div id="collapseFilter" class="collapse" aria-labelledby="headingFilter" data-parent="#accordionExample">
					<div class="card-body" id="headingFilter">
					  <h2 class="mb-0">
					    <a href="https://drive.google.com/drive/folders/1xxukESXBZC9jYkbNoUV3pxsR4ojb6nFi?usp=sharing" class="btn btn-link btn-block text-left" target="_blanck">
					      Solo los usuarios con acceso al Sharepoint pueden acceder a los videos dando click aquí.
					    </a>
					  </h2>
					</div>
				</div>
				@endif
            </div>
        </div>
    </div>
</div>
@endsection
