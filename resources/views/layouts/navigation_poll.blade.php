     <nav class="navbar navbar-expand-md navbar-white fixed-top bg-white container">
      
    <a class="navbar-brand" href="#"><img src="/img/Logo_MQE_normal-40px.png" alt="" ></a>
      <button class="navbar-toggler d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="navbar-collapse collapse" id="navbarsExampleDefault" style="">
        <ul class="navbar-nav mr-auto">
          
          <!-- Authentication Links -->
                        @if (Auth::guest())
                            
                            
          <!--
          <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
          -->
          
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
            <a class="nav-link" href="{{ route('orders') }}">Pedidos</a>
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
          

         @if (!Auth::guest())
          <form class="form-inline mt-2 mt-md-0" action="/leads" method="GET">
            <input class="form-control mr-sm-2" type="text" placeholder="Busca o escribe" aria-label="Cliente" id="name" name="search" @if (isset($request->search)) value="{{$request->search}}" @endif>
            <button class="btn btn-primary my-2 my-sm-0" type="submit">Ir</button>
          </form>  
        @endif

        </div>
    </nav>
    <style type="text/css">
      .fixed-top {
    position: static;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1030;
}
    </style>
    <br>