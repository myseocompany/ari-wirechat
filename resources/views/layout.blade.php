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
  <title>CRM MQE</title>

  <!-- Bootstrap core CSS -->
  
  <!-- CSS de Bootstrap -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- fonts online -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">    
  <link rel="stylesheet" href="/css/dashboard.css?id=<?php echo rand(1,1000);?>">
 
  {{-- drag and drop --}}
  
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    


  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script> 
  <script src="//cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
    box-shadow:  0px 0px 0px 0px #000;
  }

  legend.scheduler-border {
    font-size: 1.2em !important;
    font-weight: bold !important;
    text-align: left !important;
  }
</style>

@stack('styles')
</head>
<body>
  @include('layouts.navigation_tailwind')
  <div class="container pt-20">
    <div style="background-color:#FFF;">

    </div>
    @yield('content')

    <!-- Site footer -->
    @include('layouts.footer')

  </div> <!-- /container -->

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

<script type="text/javascript">
    $(document).ready(function(){
      $('.listPhone').hide()
      $('select[name="selectBy"]').on('change', function(){
        if($(this).val()== 1){
          $('.listEmail').show()
          $('.listPhone').hide()
          $('#title').html("Email")
        }else{
          $('.listEmail').hide()
          $('.listPhone').show()
          $('#title').html("Phone")
        }
      })
    })
  </script>

  <script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $(".listEmail tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $(".listPhone tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>

  


<!-- JS, Popper.js y jQuery de Bootstrap (asegúrate de ponerlos antes del cierre de la etiqueta </body>) -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  @yield('footer_scripts')
      <script src="/js/scripts.js?id=<?php echo rand(1,10000) ?>"></script> 
      <!-- script src="/js/addInput.js?id=<?php echo rand(1,10000) ?>"--> 
      <script>
        $(document).ready(function(){
          setTimeout(function(){
            $(".alert").fadeOut("slow");
          }, 4000); // desaparece después de 4 segundos
        });
      </script>
      @hasSection('filter')
        <script>
          (function () {
            const overlay = document.getElementById('filter_overlay');
            const openButtons = Array.from(document.querySelectorAll('[data-filter-open]'));
            if (!overlay || openButtons.length === 0) {
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
            openButtons.forEach(function (button) {
              button.addEventListener('click', openOverlay);
            });
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
        })();
      </script>
      @stack('scripts')
    </body>
    </html>
