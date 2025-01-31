@extends('layout')

@section('content')
    <h1>Crear Producto</h1>
    <form method="POST" action="{{ url('/products/store') }}">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-4">
                <!-- Columna 1 -->
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" class="form-control" id="name" name="name" required="required">
                </div>

                 <div class="form-group">
                <label>Activo:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="active" id="activo_si" value="1">
                    <label class="form-check-label" for="activo_si">Si</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="active" id="activo_no" value="0">
                    <label class="form-check-label" for="activo_no">No</label>
                </div>
            </div>              
              
                <div class="form-group">
                    <label for="alias">Alias:</label>
                    <input type="text" class="form-control" id="alias" name="alias" required="required">
                </div>

                <div class="form-group">
                    <label for="price">Precio:</label>
                    <input type="number" class="form-control" id="price" name="price">
                </div>
                
                <div class="form-group">
                    <label for="shipping">Flete:</label>
                    <input type="number" class="form-control" id="shipping" name="shipping">
                </div>
                
            </div>

            <div class="col-md-4">
                <!-- Columna 2 -->
                <div class="form-group">
                    <label for="total">Precio total:</label>
                    <input type="number" class="form-control" id="total" name="total">
                </div>
                

                <div class="form-group">
                    <label for="coin">Moneda:</label>
                    <input type="text" class="form-control" id="coin" name="coin">
                </div>

                <div class="form-group">
                    <label for="country">Pais:</label>
                    <input type="text" class="form-control" id="country" name="country" required="required"> 
                </div>
            </div>

            <div class="col-md-4">
                <!-- Columna 3 -->
                <div class="form-group">
                    <label for="description">Descripcion:</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="description_to_print">Descripcion para Imprimir:</label>
                    <textarea class="form-control" id="description_to_print" name="description_to_print"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
@endsection
