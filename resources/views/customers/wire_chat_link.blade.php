<button class="btn btn-outline-secondary btn-sm" title="Iniciar chat con WhatsApp" onclick="startChat({{ $model->id }})">
    <i class="fas fa-comment-dots"></i>
  </button>
  

  <script>
  function startChat(customerId) {
    fetch('/customers/start-chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        customer_id: customerId,
        mensaje: '¡Hola, te hablo de parte de maquiempandas! ¿En qué puedo ayudarte?'
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        //window.open(data.chat_url, '_blank'); // nueva pestaña con WireChat
        alert('Mensaje enviado. Revisa con la sesión del usuario chat');
      } else {
        alert('No se pudo iniciar el chat');
      }
    });
  }
  </script>