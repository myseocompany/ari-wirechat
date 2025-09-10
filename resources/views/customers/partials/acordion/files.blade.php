<!-- ARCHIVOS -->
 @if($customer)
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        Archivos
        </button>
      </h2>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" >

<form id="uploadForm" method="POST" action="/customer_files" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="customer_id" value="{{ $customer->id }}">

  <!-- input real (oculto) para compatibilidad con tu backend) -->
  <input id="filesInput" type="file" name="files[]" multiple hidden>

  <!-- Zona Drag & Drop -->
  <div id="dropArea" class="border rounded p-4 text-center"
       style="border-style:dashed; cursor:pointer">
    <div class="mb-2">
      <strong>Arrastra y suelta tus archivos aquí</strong>
    </div>
    <div class="text-muted">o haz clic para seleccionar</div>
  </div>

  <!-- Lista/preview -->
  <div id="fileList" class="mt-3" style="display:none;">
    <h6>Archivos seleccionados</h6>
    <ul class="list-unstyled mb-0" id="fileItems"></ul>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-sm btn-primary">Subir</button>
  </div>
</form>

<script>
(function(){
  const dropArea  = document.getElementById('dropArea');
  const input     = document.getElementById('filesInput');
  const fileList  = document.getElementById('fileList');
  const fileItems = document.getElementById('fileItems');

  // helpers
  function preventDefaults(e){ e.preventDefault(); e.stopPropagation(); }
  ['dragenter','dragover','dragleave','drop'].forEach(ev => {
    dropArea.addEventListener(ev, preventDefaults, false);
  });

  // estilos al arrastrar
  dropArea.addEventListener('dragover', () => dropArea.classList.add('bg-light'));
  dropArea.addEventListener('dragleave', () => dropArea.classList.remove('bg-light'));
  dropArea.addEventListener('drop', (e) => {
    dropArea.classList.remove('bg-light');
    const dt = new DataTransfer();              // construir FileList
    // añade lo que ya haya en el input (por si combinan click + drop)
    for (const f of input.files) dt.items.add(f);
    // añade lo que viene del drop
    for (const f of e.dataTransfer.files) dt.items.add(f);
    input.files = dt.files;
    renderList();
  });

  // click para abrir selector
  dropArea.addEventListener('click', () => input.click());
  input.addEventListener('change', renderList);

  function renderList(){
    fileItems.innerHTML = '';
    if (input.files.length === 0){ fileList.style.display='none'; return; }
    fileList.style.display = 'block';
    Array.from(input.files).forEach((f, idx) => {
      const li = document.createElement('li');
      li.className = 'd-flex align-items-center justify-content-between py-1';
      li.innerHTML = `
        <span>${f.name} <small class="text-muted">(${(f.size/1024).toFixed(1)} KB)</small></span>
        <button type="button" class="btn btn-link btn-sm p-0" data-idx="${idx}">Quitar</button>
      `;
      fileItems.appendChild(li);
    });

    // quitar archivo individual
    fileItems.querySelectorAll('button[data-idx]').forEach(btn=>{
      btn.addEventListener('click', () => {
        const i = parseInt(btn.getAttribute('data-idx'), 10);
        const dt = new DataTransfer();
        Array.from(input.files).forEach((f, idx) => { if (idx !== i) dt.items.add(f); });
        input.files = dt.files;
        renderList();
      });
    });
  }
})();
</script>


      <div>
        <div class="table">
          <table class="table table-striped">
            <thead>
              <tr>

                <th>Url</th>
                <th>Fecha de Creación</th>
                <th>Usuario</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
  @foreach($customer->customer_files as $file)
    <tr id="file-row-{{ $file->id }}">
      <td>
        <a href="/public/files/{{ $file->customer_id }}/{{ $file->url }}" target="_blank">
          {{ $file->url }}
          @if($file->status !== 'OK')
            <span class="badge badge-warning ml-2">
              <i class="fa fa-exclamation-triangle"></i> Missing
            </span>
          @endif
        </a>
      </td>
      <td>{{ $file->created_at }}</td>
      <td>{{ $file->creator?->name ?? 'Sin usuario' }}</td>

      <td>
        <button type="button"
                class="btn btn-sm btn-outline-danger js-delete-file"
                data-id="{{ $file->id }}"
                data-url="{{ route('customer_files.destroy', $file) }}"
                title="Eliminar">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    </tr>
  @endforeach
</tbody>

          </table>        

        </div>
      </div>
    </div>

  </div>

  <script>
(function () {
  
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
console.log("validando "+ token);
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.js-delete-file');
  if (!btn) return;

  e.preventDefault();
  const id  = btn.dataset.id;
  const url = btn.dataset.url;

  if (!confirm('¿Mover a la papelera este archivo?')) return;

  try {
    btn.disabled = true;
    const res = await fetch(url, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.message || 'Error al eliminar');
    document.getElementById('file-row-'+id)?.remove();
  } catch (err) {
    alert('No se pudo eliminar. Intenta nuevamente.');
    btn.disabled = false;
  }
});

})();
</script>


@endif