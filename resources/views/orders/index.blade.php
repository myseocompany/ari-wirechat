@extends('layout')

@section('content')
<h1>Ordenes</h1>

@include('orders.dashboard')
@include('orders.filter')

@php
    $total_payments = Array();
    if(isset($payments))
      foreach($payments as $item){
        $total_payments[$item->id] = 0;
      }
    
@endphp

<table class="table">
<tr>

	<th>Cliente</th>
  <th>Fecha entrega</th>
  <th>Estado</th>
  <th>Valor</th>

</tr>
<?php $i=1;?> 

@foreach($model as $item)
<tr class="order_row">
  
  <td>
    <div><strong>OrderID:</strong> {{$item->id}} @if(isset($item->user))<strong>Atendido por</strong> {{$item->user->name}}@endif</div>
    <div>{{$item->delivery_address}}</div>
    <div><a href="/products/{{$item->product_id}}/show">{{$item->name}}</a></div>
    <div>@if(isset($item->customer))
      <a href="/orders/{{$item->id}}/show">{{$item->customer->name}}</a>
    @endif</div>
    <div>
      @if(isset($item->payment))
      {{$item->payment->name}}
      @endif
    </div>
    <div>
      <i>{{$item->created_at}}</i>
    </div>

    @if($item->notes != null or $item->notes != "")
    <div class="order_notes">
      {{$item->notes}}
    </div>

    @endif
  </td>
  
  

  <td>{{$item->delivery_date}}</td>
  @if(isset($item->status))
  <td>{{$item->status->name}}</td>
  @else
  <td></td>
  @endif
  <td class="text-right">$ {{number_format($item->getTotal(), 0)}}</td>

</tr>


@php
  if(isset($item->payment_id) && ($item->payment_id != ""))
  $total_payments[$item->payment_id] += $item->getTotal();
@endphp
@endforeach
@if(isset($payments))
  @foreach($payments as $item)
    
    <tr>
      <td colspan="3" class="text-right"><strong>{{$item->name}}:</strong></td>  
      <td class="text-right">$ {{number_format($total_payments[$item->id])}}</td>
    </tr>
  @endforeach
@endif



</table>
{ $model->appends(request()->input())->links() }}


@endsection