<div>
  <h3>Últimas cotizaciones</h3>
@php $total = 0; @endphp
  <ol>
    @foreach($customersQuotes20 as $item)
    <li>
      <small>{{$item->created_at->format('Y/m/d')}}</small> - 
      <a href="/customers/{{$item->id}}/show">{{$item->name}}</a> - 
      @if(isset($item->project) ) <span style="background-color: {{$item->project->color}}; " class="badge badge-secondary">{{$item->project->name}}</span> @endif
      
      @if(isset($item->total_sold) && !is_null($item->total_sold))
                        @if(is_numeric($item->total_sold)) $ {{number_format($item->total_sold/1000000 ,0,",",".")}}M @endif 
                      @endif</li>
</li>
    @php if(isset($item->total_sold)&& !is_null($item->total_sold) && is_numeric($item->total_sold)){
      $total += $item->total_sold;
    } @endphp
    @endforeach
    <li>
     <strong>TOTAL COTIZADO</strong>
     @if(isset($total) && !is_null($total))
                        @if(is_numeric($total)) <strong>$ {{number_format($total/1000000,0,",",".")}}M</strong> @endif 
                      @endif
  </lid>
  </ul>
</div>