<div class="row mb-2">
    <div class="col-md-12">
  @if($customersGroup->count()!=0)
  <ul class="groupbar bb_hbox">
  @php
    $count=0;  
    $sum_g = 0;  
  @endphp 
    @foreach($customersGroup as $item)
 <?php
  
  if($item->count>0)
      $count++;

  ?>
  
  @endforeach
    @foreach($customersGroup as $item)
    @if($item->count!=0)
    <li class="groupBarGroup card border-0" style="background-color: @if( isset($item->status_color) ) {{$item->status_color}}; @else #000000; @endif  width: <?php 
       

        if($customersGroup->count()!=0){
          echo 100/$count;
        }
     ?>%">
      <h3>{{$item->count}}</h3>
     
      <div><a href="#" onclick="changeStatus({{$item->status_id}})"> @if( isset($item->status_name) ) {{$item->status_name}} @else sin estado @endif </a></div>
    </li>
    @php
      $sum_g += $item->count;
    @endphp
    @endif          
    @endforeach
  </ul>
  @else
    Sin Estados
  @endif
  </div>
</div>