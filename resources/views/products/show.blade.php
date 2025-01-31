@extends('layout')

@section('content')
    <h1>Ver Producto</h1>
    <form method="POST" action="{{ url('/products', $model->id) }}">
        {{ csrf_field() }}
        {{ method_field('PUT') }}

        <div class="row">
            <div class="col-md-4">
                <!-- Columna 1 -->
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <span readonly type="text" class="readonly" id="name" name="name" value="" required="required">{{ $model->name }}</span>
                </div>
                
                <div class="form-group">
                    <label>Activo:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="active" id="activo_si" value="1" {{ $model->active == 1 ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="activo_si">Si</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="active" id="activo_no" value="0" {{ $model->active == 0 ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="activo_no">No</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="alias">Alias:</label>
                    <span readonly type="text" class="readonly" id="alias" name="alias" value="" required="required">{{ $model->alias }}</span>
                </div>
                
                <div class="form-group">
                    <label for="price">Precio:</label>
                    <span readonly type="number" class="readonly" id="price" name="price" value="" required="required">{{ $model->price }}</span>
                </div>

                

                <a type="submit" class="btn btn-primary" href="/products/{{$model->id}}/edit">Editar</a>
            </div>

            <div class="col-md-4">
                <!-- Columna 2 -->
                <div class="form-group">
                    <label for="shipping">Flete:</label>
                    <span readonly type="number" class="readonly" id="shipping" name="shipping" value="" required="required">{{ $model->shipping }}</span>
                </div>

                <div class="form-group">
                    <label for="total">Precio total:</label>
                    <span readonly type="number" class="readonly" id="total" name="total" value="" required="required">{{ $model->total }}</span>
                </div>

                <div class="form-group">
                    <label for="coin">Moneda:</label>
                    <span readonly type="text" class="readonly" id="coin" name="coin" value="" required="required">{{ $model->coin }}</span>
                </div>

                <div class="form-group">
                    <label for="country">Pais:</label>
                    <span readonly type="text" class="readonly" id="country" name="country" value="" required="required">{{ $model->country }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Columna 3 -->
                <div class="form-group">
                    <label for="description">Descripcion:</label>
                    <div class="readonly" id="description" name="description">{{ $model->description }}</div>
                </div>

                <div class="form-group">
                    <label for="description_to_print">Descripcion para Imprimir:</label>
                    <div class="readonly" id="description_to_print" name="description_to_print">{{ $model->description_to_print }}</div>
                </div>
            </div>
        </div>
    </form>
@endsection
