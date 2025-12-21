<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="/img/icono-maquiempanadas-2025.png">
  <title>CRM MQE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
  <main class="min-h-screen">
    @yield('content')
  </main>
</body>
</html>
