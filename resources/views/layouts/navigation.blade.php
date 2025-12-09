<nav class="navbar navbar-expand-md navbar-light fixed-top bg-white container">
      
  <a class="navbar-brand" href="/customers"><img src="/img/Logo_MQE_normal-40px.png" alt="" ></a>
    <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
      <ul class="navbar-nav mr-auto">
        
        @if (Auth::guest())
                          
        <li class="nav-item"><a class="nav-link text-dark" href="{{ route('login') }}">Iniciar sesión</a></li>
        @else

          @foreach(App\Models\Menu::getUserMenu(Auth::user()) as $item)
              <li class="@if($item->hasChildren()) dropdown @else nav-item @endif">
                  


                  @if($item->url == "/logout")
                          <a class="nav-link text-dark" href="#"
                              onclick="event.preventDefault();
                                       document.getElementById('logout-form').submit();">
                              Salir
                          </a>
                          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                              {{ csrf_field() }}
                          </form>
                      @else
                      <a class="nav-link text-dark @if($item->hasChildren()) dropdown-toggle @endif" href="{{$item->url}}" @if($item->hasChildren()) data-toggle="dropdown" role="button" aria-expanded="false" @endif>
                      
                          @php
                            $menuName = $item->name;
                            if ($item->url === '/reports/views/daily_customers_followup') {
                                $menuName = 'Seguimientos';
                            }
                          @endphp
                          {{$menuName}} @if($item->hasChildren()) @endif
                          
                      </a>
                      @endif
                  @if($item->hasChildren())
                    <ul class="dropdown-menu" role="menu">
                    @if(Auth::check())
                      <li class="nav-item px-3 py-1 text-muted" style="font-size: 0.9rem;">
                        {{ Auth::user()->name }}
                      </li>
                      <li class="dropdown-divider"></li>
                      @if(Auth::user()->role_id == 2)
                        <li class="nav-item dropdown">
                          <a class="nav-link text-dark dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-expanded="false">
                            Cuenta
                          </a>
                          <ul class="dropdown-menu" role="menu">
                            <li class="nav-item px-3 py-1 text-muted" style="font-size: 0.9rem;">
                              {{ Auth::user()->name }}
                            </li>
                            <li class="dropdown-divider"></li>
                            <li class="nav-item">
                              <a class="nav-link text-dark" href="#"
                                  onclick="event.preventDefault();
                                           document.getElementById('logout-form').submit();">
                                  Salir
                              </a>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                  {{ csrf_field() }}
                              </form>
                            </li>
                          </ul>
                        </li>
                      @else
                        @foreach($item->getChildren() as $subitem)
                        <li class="nav-item">

                          @if($subitem->url == "/logout")
                              <a class="nav-link text-dark" href="#"
                                  onclick="event.preventDefault();
                                           document.getElementById('logout-form').submit();">
                                  Salir
                              </a>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                  {{ csrf_field() }}
                              </form>
                          @else
                          <a class="nav-link text-dark" href="{{$subitem->url}}">
                              
                                  {{$subitem->name}} 
                              
                          </a>
                          @endif
                        </li>

                        @endforeach
                      @endif
                    @endif
                    </ul> 
                  @endif 
              </li>     
          @endforeach     
        @endif      
       
      </ul>

      
        

         @if (!Auth::guest())
          <form class="form-inline mt-2 mt-md-0" action="/customers" method="GET">

          <input class="form-control mr-sm-2" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" @if (isset($request->search)) value="{{$request->search}}" @endif>
          <button class="btn btn-primary my-2 my-sm-0" type="submit">Ir</button>
        </form>  
      @endif

      </div>
  </nav>
  <br>
