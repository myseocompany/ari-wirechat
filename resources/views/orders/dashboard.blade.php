<div>
  @if($group->count()!=0)
  <ul class="groupbar bb_hbox">
  
    @foreach($group as $item)
    <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php 
        if($group->count()!=0){
          echo 100/$group->count();
        }
     ?>%">
      <h3>{{$item->count}}</h3>
     
      <div><a href="#" onclick="changeStatus({{$item->status_id}})">{{$item->name}}</a></div>
    </li>  
     
    @endforeach
  </ul>
  @else
    Sin Estados
  @endif
</div>