      
<ul class="navbar-nav mr-auto">
    <li class="nav-item active">
        <a class="nav-link" href="/">Inicio<span class="sr-only">(current)</span></a>
    </li>
   
    @foreach($menu as $item)
        <li class="nav-item">
            <a class="nav-link" href="{{$item->url}}"> 

                    {{$item->name}}
            </a>
        </li>     
    @endforeach
    
          
          <!-- Authentication Links -->
                        @if (Auth::guest())
                            
                            
          <!--
          <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
          -->
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar Sesion</a></li>
          @else
          @if (Auth::user()->role_id == 1)
            {{-- expr --}}

            <li class="dropdown">
            <a class="nav-link" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                Prospectos <span class="caret"></span>
            </a>

            <ul class="dropdown-menu" role="menu">
                <li>
                    <a class="nav-link" href="{{ route('leads') }}">Prospectos</a>  
                </li>
                <li>
                    <a class="nav-link" href="/optimize/customers/findDuplicates/">Buscar duplicados</a>  
                </li>
            </ul>
        </li>

         
          <li class="nav-item">
            <a class="nav-link" href="{{ route('actions') }}">Acciones</a>
          </li>
          <li class="dropdown">
            <a class="nav-link" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                Reportes <span class="caret"></span>
            </a>

            <ul class="dropdown-menu" role="menu">
                <li>
                    <a href="/reports/users">
                        Usuarios
                    </a>
                </li>
                <li>    
                    <a href="/reports/customers_time">
                        Clientes en el tiempo
                    </a>
                </li>      
                <li>
                      <a href="/reports/users/customer/status">
                        Estados por usuario
                    </a>
                </li>    
                      <a href="/reports/users/customer/actions">
                        Acciones por usuario
                    </a>
                </li>
                </li>    
                      <a href="/reports/views/customers_followup">
                        Seguimientos
                    </a>
                </li>
            </ul>
        </li>
          <li class="nav-item"><a class="nav-link" href="/config">Configuracion</a></li>

          @endif
          @if (Auth::user()->role_id == 2 || Auth::user()->role_id == 10)
            <li class="nav-item">
            <a class="nav-link" href="{{ route('leads') }}">Prospectos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('customers') }}">Clientes</a>
          </li>
            
            <li class="nav-item">
            <a class="nav-link" href="{{ route('actions') }}">Acciones</a>
          </li>
          
          @endif
          
                            <li class="dropdown">
                                <a class="nav-link" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
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
                        @endif
  </ul>
