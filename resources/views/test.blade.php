@extends('layout')

@section('content')
    <form id="myForm" > action="{{ url('/api/watoolbox') }}" method="POST">
        @csrf
        <br><input type="text" name="id" value="123">
        <br><input type="text" name="type" value="text">
        <br><input type="text" name="user" value="JuanDavid">
        <br><input type="text" name="phone" value="573125407247">
        <br><input type="text" name="content" value="Hello, world!">
        <br><input type="text" name="name" value="John Doe">
        <br><input type="text" name="name2" value="JohnD">
       
        <br><input type="text" name="image" value="http://example.com/image.jpg">
        <br><input type="text" name="APIKEY" value="pHPC9TbqDGWVAPRGpzX0VxxNGPJeuXj03uWqt0QQ9b1e9bdf">
        <br>
        <button type="submit">Send Data</button>
    </form>
    <script>
        document.getElementById('myForm').addEventListener('submit', function(e) {
            e.preventDefault();  // Evita el envío normal del formulario

            // Crea un objeto FormData y usa fetch para enviar los datos como JSON
            var formData = new FormData(this);
            var object = {};
            formData.forEach(function(value, key){
                object[key] = value;
            });

            fetch('http://127.0.0.1:8000/api/watoolbox', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(object)
            })
            .then(response => response.json())
            .then(data => console.log(data))
            .catch(error => console.error('Error:', error));
        });
    </script>
@endsection