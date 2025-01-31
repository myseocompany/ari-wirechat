      
<ul class="navbar-nav mr-auto">
    <li class="nav-item active">
        <a class="nav-link" href="/">Inicio<span class="sr-only">(current)</span></a>
    </li>
    
    @foreach(App\Models\Menu::getUserMenu(Auth::user()) as $item)
            
        <li class="nav-item">
            <a class="nav-link" href="{{$item->url}}"> 
                
                    {{$item->name}}
                
            </a>
        </li>     
    @endforeach
    
          
  </ul>
