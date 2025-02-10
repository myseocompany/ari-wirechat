<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Datos</title>
</head>
<body>
    <h1>Enviar Leads</h1>

    <form id="leadForm">
        <textarea id="json_data" rows="10" cols="50">
{
    "leads": [
        {
            "id": "3690528888",
            "email": "nidia132007@hotmail.com",
            "name": "Nidia Contreras",
            "company": "Casa",
            "job_title": null,
            "public_url": "http://app.rdstation.com.br/leads/public/4ed77c95-3159-49dc-87eb-8c96e1922f88",
            "created_at": "2024-05-31T20:58:02.917-03:00",
            "opportunity": "false",
            "custom_fields": { "Pais": "CO" }
        }
    ]
}
        </textarea>
        <br>
        <button type="submit">Enviar</button>
    </form>

    <script>
        document.getElementById("leadForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Evita la recarga del formulario

            let jsonData = document.getElementById("json_data").value.trim();

            fetch("{{ route('updateRD') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: jsonData
            })
            .then(response => response.json())
            .then(data => console.log("Respuesta del servidor:", data))
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
