@php
  $pendingActions = null;
  if (Auth::check()) {
    $pendingQuery = \App\Models\Action::whereNotNull('due_date')->whereNull('delivery_date');
    if (Auth::user()->role_id == 2) {
      $pendingQuery->join('customers', 'customers.id', '=', 'actions.customer_id')
                   ->where('customers.user_id', Auth::id());
    }
    $pendingActions = $pendingQuery->count();
  }
@endphp

<nav class="navbar navbar-expand-md navbar-light fixed-top bg-white container-fluid">
      
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

                            if (Auth::check() && strtolower(trim($menuName)) === 'cuenta') {
                              $menuName = Auth::user()->name;
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
                            {{ Auth::user()->name }}
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
          <div class="d-flex align-items-center">
            @php
              $actionsLink = '/actions/?range_type=&filter=&from_date=&from_time=&to_date=&to_time=&pending=true&type_id=&user_id=';
              if (Auth::user()->role_id == 2) {
                $actionsLink .= Auth::id();
              }
              $actionsLink .= '&action_search=';
            @endphp
            <a href="{{ $actionsLink }}" target="_self" class="position-relative d-flex align-items-center justify-content-center text-secondary mr-3" title="Acciones pendientes">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              @if(!is_null($pendingActions))
                <span class="position-absolute badge badge-pill badge-danger" style="top: -4px; right: -6px; padding: 2px 6px; font-size: 11px;">
                  {{ $pendingActions }}
                </span>
              @endif
            </a>
            <form class="form-inline mt-2 mt-md-0" action="/customers" method="GET">
              <input class="form-control mr-sm-2" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" @if (isset($request->search)) value="{{$request->search}}" @endif>
              <button class="btn btn-primary my-2 my-sm-0" type="submit">Ir</button>
            </form>
          </div>
        @endif

      </div>
  </nav>
  <br>
