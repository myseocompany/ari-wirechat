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
  $navUserAvatar = null;
  $navUserInitial = null;
  $menuItems = [];
  $accountMenu = null;
  if (Auth::check()) {
    $navUserAvatar = Auth::user()->image_url;
    if ($navUserAvatar && !preg_match('#^https?://#i', $navUserAvatar)) {
      $navUserAvatar = asset(ltrim($navUserAvatar, '/'));
    }
    $navUserInitial = strtoupper(mb_substr(Auth::user()->name ?? '', 0, 1, 'UTF-8'));

    foreach(App\Models\Menu::getUserMenu(Auth::user()) as $menuItem) {
      if (strtolower(trim($menuItem->name)) === 'cuenta') {
        $accountMenu = $menuItem;
        continue;
      }
      $menuItems[] = $menuItem;
    }
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

          @foreach($menuItems as $item)
              @php
                $liClasses = $item->hasChildren() ? 'dropdown' : 'nav-item';
                $resolvedItemUrl = strtolower(trim($item->name)) === 'seguimientos' ? route('customer-chats') : $item->url;
                $resolvedItemName = strtolower(trim($item->name)) === 'seguimientos' ? 'Chats' : $item->name;
              @endphp
              <li class="{{ $liClasses }}">
                  


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
                      <a class="nav-link text-dark @if($item->hasChildren()) dropdown-toggle @endif" href="{{$resolvedItemUrl}}" @if($item->hasChildren()) data-toggle="dropdown" role="button" aria-expanded="false" @endif>
                          {{$resolvedItemName}}
                      </a>
                      @endif
                  @if($item->hasChildren())
                    <ul class="dropdown-menu" role="menu">
                      @foreach($item->getChildren() as $subitem)
                        @php
                          $resolvedSubitemUrl = strtolower(trim($subitem->name)) === 'seguimientos' ? route('customer-chats') : $subitem->url;
                          $resolvedSubitemName = strtolower(trim($subitem->name)) === 'seguimientos' ? 'Chats' : $subitem->name;
                        @endphp
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
                          <a class="nav-link text-dark" href="{{$resolvedSubitemUrl}}">
                              
                                  {{$resolvedSubitemName}} 
                              
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
          <div class="d-flex align-items-center">
            <form class="form-inline mt-2 mt-md-0 mr-3" action="/customers" method="GET">
              <input class="form-control mr-sm-2" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" @if (isset($request->search)) value="{{$request->search}}" @endif>
              <button class="btn btn-primary my-2 my-sm-0" type="submit">Ir</button>
            </form>
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
            @if($accountMenu)
              <div class="dropdown d-flex align-items-center mr-3 nav-account-dropdown">
                <a class="nav-link text-dark dropdown-toggle p-0" href="{{ $accountMenu->url }}" data-toggle="dropdown" role="button" aria-expanded="false">
                  <span class="nav-user-display">
                    @if($navUserAvatar)
                      <span class="nav-user-avatar" style="background-image: url('{{ $navUserAvatar }}');"></span>
                    @else
                      <span class="nav-user-avatar nav-user-avatar--placeholder">{{ $navUserInitial }}</span>
                    @endif
                    <span>{{ Auth::user()->name }}</span>
                  </span>
                </a>
                @if($accountMenu->hasChildren())
                  <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    @if(Auth::user()->role_id == 2)
                      <li class="nav-item dropdown">
                        <a class="nav-link text-dark dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-expanded="false">
                          {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu" role="menu">
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
                      @foreach($accountMenu->getChildren() as $subitem)
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
                  </ul>
                @endif
              </div>
            @endif
          </div>
        @endif

      </div>
  </nav>
  <br>

<style>
  .navbar-nav .nav-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .nav-user-display {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .nav-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-color: #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    text-transform: uppercase;
    color: #475569;
  }
  .nav-user-avatar--placeholder {
    background-color: #c7d2fe;
    color: #312e81;
  }
  .nav-account-dropdown .dropdown-toggle::after {
    vertical-align: middle;
    position: relative;
    top: -12px;
  }
</style>
