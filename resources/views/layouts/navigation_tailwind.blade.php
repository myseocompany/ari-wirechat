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

    foreach (App\Models\Menu::getUserMenu(Auth::user()) as $menuItem) {
      if (strtolower(trim($menuItem->name)) === 'cuenta') {
        $accountMenu = $menuItem;
        continue;
      }
      $menuItems[] = $menuItem;
    }
  }
@endphp

<nav class="fixed inset-x-0 top-0 z-50 bg-white shadow">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between gap-3">
      <div class="flex min-w-0 flex-1 items-center gap-4">
        <a class="hidden items-center lg:flex" href="/customers">
          <img src="/img/Logo_MQE_normal-40px.png" alt="" class="h-10 w-auto">
        </a>
        <div class="hidden lg:flex lg:items-center lg:gap-4">
          @if (Auth::guest())
            <a class="text-sm font-medium text-slate-700 hover:text-slate-900" href="{{ route('login') }}">Iniciar sesión</a>
          @else
            @foreach ($menuItems as $item)
              @if ($item->hasChildren())
                <div class="relative" data-dropdown>
                  <button type="button" class="inline-flex items-center gap-1 text-sm font-medium text-slate-700 hover:text-slate-900" data-dropdown-trigger>
                    {{ $item->name }}
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.18l3.71-3.95a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 hidden min-w-[220px] rounded-md border border-slate-200 bg-white py-2 shadow-lg" data-dropdown-menu>
                    @foreach ($item->getChildren() as $subitem)
                      @if ($subitem->url === '/logout')
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" data-logout>Salir</button>
                      @else
                        <a class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" href="{{ $subitem->url }}">{{ $subitem->name }}</a>
                      @endif
                    @endforeach
                  </div>
                </div>
              @else
                @if ($item->url === '/logout')
                  <button type="button" class="text-sm font-medium text-slate-700 hover:text-slate-900" data-logout>Salir</button>
                @else
                  <a class="text-sm font-medium text-slate-700 hover:text-slate-900" href="{{ $item->url }}">{{ $item->name }}</a>
                @endif
              @endif
            @endforeach
          @endif
        </div>
        @if (!Auth::guest())
          <form class="flex min-w-0 flex-1 items-center gap-2 lg:hidden" action="/customers" method="GET">
            <input class="h-9 w-full min-w-0 rounded-md border border-slate-300 px-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_mobile" name="search" @if (isset($request->search)) value="{{ $request->search }}" @endif>
            <button class="inline-flex h-9 items-center rounded-md bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700" type="submit">Ir</button>
          </form>
        @endif
      </div>

      <div class="hidden lg:flex lg:items-center lg:gap-3">
        @if (!Auth::guest())
          <form class="flex items-center gap-2" action="/customers" method="GET">
            <input class="h-9 w-56 rounded-md border border-slate-300 px-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" type="text" placeholder="Busca o escribe..." aria-label="Cliente" id="name_" name="search" @if (isset($request->search)) value="{{ $request->search }}" @endif>
            <button class="inline-flex h-9 items-center rounded-md bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700" type="submit">Ir</button>
          </form>
          @php
            $actionsLink = '/actions/?range_type=&filter=&from_date=&from_time=&to_date=&to_time=&pending=true&type_id=&user_id=';
            if (Auth::user()->role_id == 2) {
              $actionsLink .= Auth::id();
            }
            $actionsLink .= '&action_search=';
          @endphp
          <a href="{{ $actionsLink }}" class="relative inline-flex h-9 w-9 items-center justify-center text-slate-500 hover:text-slate-700" title="Acciones pendientes">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            @if (!is_null($pendingActions))
              <span class="absolute -right-1 -top-1 inline-flex items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-semibold text-white">
                {{ $pendingActions }}
              </span>
            @endif
          </a>
          @if ($accountMenu)
            <div class="relative" data-dropdown>
              <button type="button" class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900" data-dropdown-trigger>
                @if ($navUserAvatar)
                  <span class="h-9 w-9 rounded-full bg-cover bg-center" style="background-image: url('{{ $navUserAvatar }}');"></span>
                @else
                  <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-200 text-sm font-semibold uppercase text-indigo-900">{{ $navUserInitial }}</span>
                @endif
                <span>{{ Auth::user()->name }}</span>
                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.18l3.71-3.95a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                </svg>
              </button>
              <div class="absolute right-0 mt-2 hidden min-w-[200px] rounded-md border border-slate-200 bg-white py-2 shadow-lg" data-dropdown-menu>
                @if (Auth::user()->role_id == 2)
                  <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" data-logout>Salir</button>
                @else
                  @foreach ($accountMenu->getChildren() as $subitem)
                    @if ($subitem->url === '/logout')
                      <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" data-logout>Salir</button>
                    @else
                      <a class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" href="{{ $subitem->url }}">{{ $subitem->name }}</a>
                    @endif
                  @endforeach
                @endif
              </div>
            </div>
          @endif
        @endif
      </div>

      <div class="flex items-center gap-2 lg:hidden">
        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-slate-600 hover:text-slate-900" data-nav-toggle aria-controls="mobile_nav" aria-expanded="false">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <div class="border-t border-slate-200 lg:hidden" id="mobile_nav" hidden>
    <div class="space-y-3 px-4 py-4">
      @if (Auth::guest())
        <a class="block text-sm font-medium text-slate-700" href="{{ route('login') }}">Iniciar sesión</a>
      @else
        @foreach ($menuItems as $item)
          @if ($item->hasChildren())
            <div class="space-y-1">
              <span class="block text-xs font-semibold uppercase text-slate-400">{{ $item->name }}</span>
              @foreach ($item->getChildren() as $subitem)
                @if ($subitem->url === '/logout')
                  <button type="button" class="block text-sm font-medium text-slate-700" data-logout>Salir</button>
                @else
                  <a class="block text-sm font-medium text-slate-700" href="{{ $subitem->url }}">{{ $subitem->name }}</a>
                @endif
              @endforeach
            </div>
          @else
            @if ($item->url === '/logout')
              <button type="button" class="block text-sm font-medium text-slate-700" data-logout>Salir</button>
            @else
              <a class="block text-sm font-medium text-slate-700" href="{{ $item->url }}">{{ $item->name }}</a>
            @endif
          @endif
        @endforeach
        <div class="flex items-center gap-3">
          <a href="{{ $actionsLink ?? '/actions/' }}" class="relative inline-flex h-9 w-9 items-center justify-center text-slate-500" title="Acciones pendientes">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            @if (!is_null($pendingActions))
              <span class="absolute -right-1 -top-1 inline-flex items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-semibold text-white">
                {{ $pendingActions }}
              </span>
            @endif
          </a>
          <span class="text-sm text-slate-600">{{ Auth::user()->name }}</span>
        </div>
      @endif
    </div>
  </div>
</nav>

@if (Auth::check())
  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    {{ csrf_field() }}
  </form>
@endif
