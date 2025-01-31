<!--
<div class="">
  @if($customersGroup->count()!=0)
  <div class="dashboard">
    
    @foreach($customersGroup as $item)
    <div class="dashboard_item">
      <div class="dashboard_body">
        <div class="dashboard_01">
      
        </div>
        <div class="groupBarGroup dashboard_03" style="background-color: {{$item->color}};">
          <h3>{{$item->count}}</h3>
         
          <div><a href="#" onclick="changeStatus({{$item->id}})">{{$item->name}}</a></div>
        </div> 
        <div class="dashboard_03">
          
        </div>
      </div>
    </div>         
    @endforeach
  </div>
  @else
    Sin Estados
  @endif
</div>
-->

<div>
  <ul class="groupbar bb_hbox">
    
    @foreach($customersGroup as $item)
    @if($item->count !=0)
    <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php 
        if($customersGroup->count()!=0){
          echo 100/$customersGroup->count();
        }
     ?>%">
      <h3>{{$item->count}}</h3>
     
      <div><a href="#" onclick="changeStatus({{$item->id}})">{{$item->name}}</a></div>
    </li>  
    @else
    Sin Estados
  @endif        
    @endforeach
  </ul>

</div>