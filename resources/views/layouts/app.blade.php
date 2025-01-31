<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="google-site-verification" content="LxHKqj-7LHr4nr1F8SSnd7J2_vI1H0lgTg2s1hb-t7A" />
  <link rel="icon" type="image/png" href="/img/icono-sitio-mqe.png">
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
  @livewireStyles
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
</head>
<body>
  <div class="container">
    @include('layouts.navigation')
    <div style="background-color:#FFF;">

    </div>
    @yield('content')

    <!-- Site footer -->
    @include('layouts.footer')

  </div> <!-- /container -->

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
      <script src="/js/addInput.js?id=<?php echo rand(1,10000) ?>"></script> 
    @livewireScripts
    </body>
    </html>