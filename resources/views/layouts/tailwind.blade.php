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
  <style>
    .page-loading-overlay {
      position: fixed;
      inset: 0;
      z-index: 2000;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .page-loading-overlay__backdrop {
      position: absolute;
      inset: 0;
      background: rgba(15, 23, 42, 0.35);
      backdrop-filter: blur(1px);
    }

    .page-loading-overlay__content {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.85rem 1rem;
      border-radius: 9999px;
      background: #111322;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      box-shadow: 0 16px 30px rgba(17, 19, 34, 0.35);
    }

    .page-loading-overlay__spinner {
      width: 1.05rem;
      height: 1.05rem;
      border-radius: 9999px;
      border: 2px solid rgba(255, 255, 255, 0.25);
      border-top-color: #fff;
      animation: page-loading-spin 0.9s linear infinite;
    }

    .page-loading-overlay__text {
      margin: 0;
    }

    @keyframes page-loading-spin {
      to {
        transform: rotate(360deg);
      }
    }

    body.page-loading-open {
      cursor: progress;
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-900">
  @include('layouts.navigation_tailwind')

  <main class="mx-auto max-w-7xl px-4 pb-10 pt-20 sm:px-6 lg:px-8">
    @yield('content')
  </main>

  <div id="page_loading_overlay" class="page-loading-overlay" aria-hidden="true" hidden>
    <div class="page-loading-overlay__backdrop"></div>
    <div class="page-loading-overlay__content" role="status" aria-live="polite">
      <span class="page-loading-overlay__spinner" aria-hidden="true"></span>
      <p class="page-loading-overlay__text" data-page-loading-label>Cargando...</p>
    </div>
  </div>

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

      var pageLoadingOverlay = document.getElementById('page_loading_overlay');
      var pageLoadingLabel = pageLoadingOverlay ? pageLoadingOverlay.querySelector('[data-page-loading-label]') : null;

      var showPageLoadingOverlay = function (message) {
        if (!pageLoadingOverlay) {
          return;
        }
        if (pageLoadingLabel) {
          pageLoadingLabel.textContent = message || 'Cargando...';
        }
        pageLoadingOverlay.removeAttribute('hidden');
        pageLoadingOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('page-loading-open');
      };

      var hidePageLoadingOverlay = function () {
        if (!pageLoadingOverlay) {
          return;
        }
        pageLoadingOverlay.setAttribute('aria-hidden', 'true');
        pageLoadingOverlay.setAttribute('hidden', '');
        document.body.classList.remove('page-loading-open');
      };

      window.showPageLoadingOverlay = showPageLoadingOverlay;
      window.hidePageLoadingOverlay = hidePageLoadingOverlay;

      document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
          return;
        }
        if (!form.hasAttribute('data-loading-overlay')) {
          return;
        }
        showPageLoadingOverlay(form.getAttribute('data-loading-message') || 'Cargando...');
      });

      document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-loading-overlay-trigger]');
        if (!trigger) {
          return;
        }
        showPageLoadingOverlay(trigger.getAttribute('data-loading-message') || 'Cargando...');
      });

      window.addEventListener('pageshow', function () {
        hidePageLoadingOverlay();
      });
    })();
  </script>

  @yield('footer_scripts')
  @stack('scripts')
</body>
</html>
