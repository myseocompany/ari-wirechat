@extends('layout')

@section('content')
<form method="POST" action="/orders/transactions/{{$model->id}}/update" id="transaction_insert">
            <div class="form-group row">
            {{ csrf_field() }}
            <input type="hidden" id="order_id" name="order_id" value="{{$model->id}}">
                <input type="date" id="date" name="date" placeholder="Fecha" class="form-control col-2" @if(isset($model->date)) value="{{Carbon\Carbon::createFromFormat('Y-m-d', $model->date)->format('Y-m-d')}}" @endif>
                <input type="text" id="description" name="description" placeholder="Concepto" class="form-control col-2" value="{{$model->description}}">
                <input type="text" id="internal_id" name="internal_id" placeholder="No" class="form-control col-1" value="{{$model->internal_id}}">
                
                <input type="number" name="value" id="value" placeholder="Valor" class="form-control col-2" @if(($model->credit!=0)) value="{{$model->credit}}" @else value="{{$model->debit}}" @endif>
                <div class="col-4">
                    <div class="form-check  form-check-inline">
                        
                        <label class="form-check-label"><input class="form-check-input" type="radio" name="is_debit" id="is_debit" value="1" @if(($model->debit!=0)) checked="checked"  @endif>Debito</label>
                        
                    </div>
                    <div class="form-check  form-check-inline">
                        
                        <label class="form-check-label"><input class="form-check-input" type="radio" name="is_debit" id="is_debit" value="0" @if(($model->credit!=0)) checked="checked"  @endif>Credito</label>
                        
                    </div>
                </div>
                <div class="col-1">
                    <button type="submit" class="btn btn-primary btn-sm" >Enviar</button>
                </div>
            </div>
        </form>
@endsection