<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Check-in')</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Opcional: personalización Tailwind -->
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#1d4ed8',   // azul
              secondary: '#f59e0b', // amarillo
            }
          }
        }
      }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <main class="flex-grow flex justify-center items-center p-4">
        @yield('content')
    </main>
</body>
</html>
