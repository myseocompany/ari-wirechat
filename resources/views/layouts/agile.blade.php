<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="google-site-verification" content="LxHKqj-7LHr4nr1F8SSnd7J2_vI1H0lgTg2s1hb-t7A" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="/img/icono-maquiempanadas-2025.png">
  <title>@yield('title') - AriCRM</title>
  
  <!-- Placed at the end of the document so the pages load faster -->
  <!-- Popper.js (versión compatible con Bootstrap 4) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <!-- fonts online -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">    

  <link rel="stylesheet" href="/css/dashboard.css?id=<?php echo rand(1,1000);?>">
 


</head>
<body id="sidebody">
  
<div class="row">
  <nav class="col-sm-12 col-md-4 " >
    <div id="sidenav" class="d-flex flex-column h-100">
      <div id="brand">
        <a class="navbar-brand" href="/customers/"><img src="/img/Logo_MQE_normal-40px.png" alt="" ></a>

        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
              <span class="fas fa-bars"></span>
      
      </button>
      <div class="navbar-collapse collapse" id="navbarsExampleDefault" style="">
        @include('customers.partials.side_nav')
      </div>

        
      </div>
      <div>
        <h1>@yield('title')</h1>
        @hasSection('filter')
          <div class="px-2 mb-2">
            <button id="filter_overlay_toggle" class="btn btn-outline-primary btn-sm" type="button">
              Filtros
            </button>
          </div>
        @endif
      </div>
      <div id="sidecontent" class="flex-fill">
        @yield('list')
      </div>
      <div id="sidecontent-footer" class="flex-shrink-0">
        @yield('list_footer')
      </div>
    </div>
  @include('layouts.left_navigation')
  </nav>
  
  <section id="side_content" class="col-sm-12  col-md-8">
    <div class="container">
      @yield('content')
    </div> 
  </section>
</div>

@hasSection('filter')
  <div id="filter_overlay" class="filter-overlay" aria-hidden="true">
    <div class="filter-overlay__backdrop" data-filter-close></div>
    <div class="filter-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="filter_overlay_title">
      <div class="filter-overlay__header">
        <h2 id="filter_overlay_title">Filtros</h2>
        <button class="filter-overlay__close" type="button" data-filter-close aria-label="Cerrar filtros">&times;</button>
      </div>
      <div class="filter-overlay__body">
        @yield('filter')
      </div>
    </div>
  </div>
@endif


<!-- jQuery moderno (debe ir primero) -->
<script src="//cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<!-- jQuery UI -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script> 
<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet">   

<!-- Moment.js + Daterangepicker -->
<script src="//cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- Bootstrap (después de jQuery y Popper.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" crossorigin="anonymous"></script>

<!-- Scripts locales -->
<script src="/js/scripts.js?id={{ rand(1,10000) }}"></script>



      <script src="/js/scripts.js?id=<?php echo rand(1,10000) ?>"></script> 
@yield('footer_scripts')
@stack('scripts')
@hasSection('filter')
  <script>
    (function () {
      const overlay = document.getElementById('filter_overlay');
      const openButton = document.getElementById('filter_overlay_toggle');
      if (!overlay || !openButton) {
        return;
      }
      const closeButtons = overlay.querySelectorAll('[data-filter-close]');
      const openOverlay = function () {
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('filter-overlay-open');
      };
      const closeOverlay = function () {
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('filter-overlay-open');
      };
      openButton.addEventListener('click', openOverlay);
      closeButtons.forEach(function (button) {
        button.addEventListener('click', closeOverlay);
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeOverlay();
        }
      });
    })();
  </script>
@endif
    </body>
    </html>
