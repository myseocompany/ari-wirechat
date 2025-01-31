<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="google-site-verification" content="LxHKqj-7LHr4nr1F8SSnd7J2_vI1H0lgTg2s1hb-t7A" />
  <link rel="icon" type="image/png" href="/img/icono-sitio-mqe.png">
  <title>@yield('title') - Create</title>
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <!-- fonts online -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">    
  <link rel="stylesheet" href="/css/dashboard.css?id=<?php echo rand(1,1000);?>">
 
  {{-- drag and drop --}}
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet">   

</head>
<body id="sidebody">
  
<div class="row">
  <nav class="col-sm-12 col-md-4">
    <div id="sidenav">
      <div id="brand">
        <a class="navbar-brand" href="/customers/phase/1"><img src="/img/Logo_MQE_normal-40px.png" alt="" ></a>

        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
              <span class="fas fa-bars"></span>
      
      </button>
      <div class="navbar-collapse collapse" id="navbarsExampleDefault" style="">
        @include('customers.index.side_nav')
      </div>

        
      </div>
      <div>
        <h1>@yield('title')</h1>
      </div>
      <div id="sidefilter">
        @yield('filter')
      </div>
      <div id="sidecontent">
        @yield('list')
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

  {{--<script src="/js/jquery-3.2.1.slim.min.js"></script>--}}
  <script
  src="https://code.jquery.com/jquery-3.4.1.js"
  integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
  crossorigin="anonymous"></script>



<!-- Placed at the end of the document so the pages load faster -->
<script src="/js/popper.min.js"></script>
      

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <!-- Bootstrap core JavaScript
  
      ================================================== -->
      

      
      {{--   <script scr="/js/fontawesome.js"></script> --}}
      @yield('footer_scripts')
      <script src="/js/scripts.js?id=<?php echo rand(1,10000) ?>"></script> 
    </body>
    </html>