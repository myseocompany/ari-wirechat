<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ $details['title'] ?? 'Correo' }}</title>
</head>
<body>
  <h1>{{ $details['title'] ?? 'Mail desde Laravel' }}</h1>
  <p>{{ $details['body'] ?? 'Contenido de prueba.' }}</p>
</body>
</html>
