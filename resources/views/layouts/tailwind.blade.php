<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="google-site-verification" content="LxHKqj-7LHr4nr1F8SSnd7J2_vI1H0lgTg2s1hb-t7A">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="/img/icono-maquiempanadas-2025.png">
  <title>CRM MQE</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/dashboard.css?id=<?php echo rand(1,1000);?>">
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900">
  @include('layouts.navigation_tailwind')

  <main class="mx-auto max-w-7xl px-4 pb-10 pt-20 sm:px-6 lg:px-8">
    @yield('content')
  </main>

  @include('layouts.footer')

  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="/js/scripts.js?id=<?php echo rand(1,10000) ?>"></script>

  <script>
    (function () {
      var navToggle = document.querySelector('[data-nav-toggle]');
      var navPanel = document.getElementById('mobile_nav');
      if (navToggle && navPanel) {
        navToggle.addEventListener('click', function () {
          var isOpen = navPanel.hasAttribute('hidden') === false;
          if (isOpen) {
            navPanel.setAttribute('hidden', '');
            navToggle.setAttribute('aria-expanded', 'false');
          } else {
            navPanel.removeAttribute('hidden');
            navToggle.setAttribute('aria-expanded', 'true');
          }
        });
      }

      document.addEventListener('click', function (event) {
        if (event.target.closest('[data-dropdown-trigger]')) {
          var dropdown = event.target.closest('[data-dropdown]');
          if (!dropdown) {
            return;
          }
          var menu = dropdown.querySelector('[data-dropdown-menu]');
          if (menu) {
            menu.classList.toggle('hidden');
          }
          return;
        }

        if (!event.target.closest('[data-dropdown]')) {
          document.querySelectorAll('[data-dropdown-menu]').forEach(function (menu) {
            menu.classList.add('hidden');
          });
        }
      });

      document.querySelectorAll('[data-logout]').forEach(function (button) {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          var form = document.getElementById('logout-form');
          if (form) {
            form.submit();
          }
        });
      });

      document.addEventListener('click', function (event) {
        if (event.target.closest('[data-filter-open]')) {
          var overlay = document.getElementById('filter_overlay');
          if (!overlay) {
            return;
          }
          event.preventDefault();
          overlay.setAttribute('aria-hidden', 'false');
          document.body.classList.add('filter-overlay-open');
          return;
        }

        if (event.target.closest('[data-filter-close]')) {
          var overlayToClose = document.getElementById('filter_overlay');
          if (!overlayToClose) {
            return;
          }
          event.preventDefault();
          overlayToClose.setAttribute('aria-hidden', 'true');
          document.body.classList.remove('filter-overlay-open');
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          var overlay = document.getElementById('filter_overlay');
          if (!overlay) {
            return;
          }
          overlay.setAttribute('aria-hidden', 'true');
          document.body.classList.remove('filter-overlay-open');
        }
      });
    })();
  </script>

  @yield('footer_scripts')
  @stack('scripts')
</body>
</html>
