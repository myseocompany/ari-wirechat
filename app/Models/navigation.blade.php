     <nav class="navbar navbar-expand-md navbar-white fixed-top bg-white container">
      
    <a class="navbar-brand" href="/customers/phase/1"><img src="/img/Logo_MQE_normal-40px.png" alt="" ></a>
      <button class="navbar-toggler d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="navbar-collapse collapse" id="navbarsExampleDefault" style="">
        <ul class="navbar-nav mr-auto">
          
          @if (Auth::guest())
                            
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a></li>
          @else
          

            @foreach(App\Models\Menu::getUserMenu(Auth::user()) as $item)
                <li class="@if($item->hasChildren()) dropdown @else nav-item @endif">
                    


                    @if($item->url =="/logout")
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                                Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        @else
                        <a class="nav-link" href="{{$item->url}}" @if($item->hasChildren()) class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" @endif> 
                        
                            {{$item->name}} @if($item->hasChildren()) - @endif
                            
                        </a>
                        @endif
                    @if($item->hasChildren())
                      <ul class="dropdown-menu" role="menu">
                      @foreach($item->getChildren() as $subitem)
                      <li class="nav-item">

                        @if($subitem->url =="/logout")
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                                Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        @else
                        <a class="nav-link" href="{{$subitem->url}}"> 
                            
                                {{$subitem->name}} 
                            
                        </a>
                        @endif
                      </li>

                      @endforeach
                      </ul> 
                    @endif 
                </li>     
            @endforeach     
          @endif      
         
        </ul>

        
          

         @if (!Auth::guest())
          <form class="form-inline mt-2 mt-md-0" action="/customers/phase/1" method="GET">

            <input class="form-control mr-sm-2" type="text" placeholder="Busca o escribe" aria-label="Cliente" id="name_" name="search" @if (isset($request->search)) value="{{$request->search}}" @endif>
            <button class="btn btn-primary my-2 my-sm-0" type="submit">Ir</button>
          </form>  
        @endif

        </div>
    </nav>
    <br>