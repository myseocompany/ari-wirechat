<!-- ARCHIVOS -->
 @if($customer)
  <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3" id="headingTwo">
      <h2 class="text-base font-semibold text-slate-900">Archivos</h2>
    </div>
    <div class="px-4 py-3">

<form id="uploadForm" method="POST" action="/customer_files" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="customer_id" value="{{ $customer->id }}">

  <!-- input real (oculto) para compatibilidad con tu backend) -->
  <input id="filesInput" type="file" name="files[]" multiple hidden>

  <!-- Zona Drag & Drop -->
  <div id="dropArea" class="cursor-pointer rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-600">
    <div class="mb-2 font-semibold text-slate-700">
      <strong>Arrastra y suelta tus archivos aquí</strong>
    </div>
    <div class="text-slate-500">o haz clic para seleccionar</div>
  </div>

  <!-- Lista/preview -->
  <div id="fileList" class="mt-3" style="display:none;">
    <h6 class="text-sm font-semibold text-slate-900">Archivos seleccionados</h6>
    <ul class="mt-2 space-y-2 text-sm text-slate-700" id="fileItems"></ul>
  </div>

  <div class="mt-3">
    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-700">Subir</button>
  </div>
</form>

<script>
(function(){
  const dropArea  = document.getElementById('dropArea');
  const input     = document.getElementById('filesInput');
  const fileList  = document.getElementById('fileList');
  const fileItems = document.getElementById('fileItems');
  const form      = document.getElementById('uploadForm');
  const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
  const tableBody = document.getElementById('customer-files-body');

  // helpers
  function preventDefaults(e){ e.preventDefault(); e.stopPropagation(); }
  ['dragenter','dragover','dragleave','drop'].forEach(ev => {
    dropArea.addEventListener(ev, preventDefaults, false);
  });

  // estilos al arrastrar
  dropArea.addEventListener('dragover', () => dropArea.classList.add('bg-slate-100'));
  dropArea.addEventListener('dragleave', () => dropArea.classList.remove('bg-slate-100'));
  dropArea.addEventListener('drop', (e) => {
    dropArea.classList.remove('bg-slate-100');
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
      li.className = 'flex items-center justify-between';
      li.innerHTML = `
        <span>${f.name} <small class="text-slate-500">(${(f.size/1024).toFixed(1)} KB)</small></span>
        <button type="button" class="text-xs font-medium text-blue-600 transition hover:text-blue-700" data-idx="${idx}">Quitar</button>
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

  function appendRow(file) {
    if (!tableBody || !file) {
      return;
    }
    const displayName = file.name || file.url || '';
    const missingBadge = file.status && file.status !== 'OK'
      ? `<span class="ml-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
           <i class="fa fa-exclamation-triangle"></i> Missing
         </span>`
      : '';
    const row = document.createElement('tr');
    row.id = `file-row-${file.id}`;
    row.innerHTML = `
      <td class="px-3 py-2">
        <a href="${file.open_url}" target="_blank" rel="noopener">
          <span class="text-blue-600 hover:underline">${displayName}</span>
          ${missingBadge}
        </a>
      </td>
      <td class="px-3 py-2">${file.created_at || ''}</td>
      <td class="px-3 py-2">${file.creator_name || 'Sin usuario'}</td>
      <td class="px-3 py-2 text-right">
        <button type="button"
                class="js-delete-file inline-flex items-center rounded-md border border-red-600 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                data-id="${file.id}"
                data-url="${file.destroy_url}"
                title="Eliminar">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    `;
    tableBody.prepend(row);
  }

  if (form && input) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (input.files.length === 0) {
        alert('Selecciona al menos un archivo.');
        return;
      }
      const originalLabel = submitBtn ? submitBtn.textContent : null;
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Subiendo...';
      }
      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        });
        const data = await response.json().catch(() => null);
        if (!response.ok || !data || !data.ok) {
          const message = data && data.message ? data.message : `Error ${response.status}`;
          throw new Error(message);
        }
        if (Array.isArray(data.files)) {
          data.files.forEach(appendRow);
        }
        input.value = '';
        fileItems.innerHTML = '';
        fileList.style.display = 'none';
      } catch (err) {
        alert(err && err.message ? err.message : 'No se pudo subir el archivo. Intenta nuevamente.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        }
      }
    });
  }
})();
</script>


      <div class="mt-4">
        <div>
          <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Url</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Fecha de Creación</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Usuario</th>
                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white" id="customer-files-body">
  @foreach($customer->customer_files as $file)
    <tr id="file-row-{{ $file->id }}">
      <td class="px-3 py-2">


<a href="{{ route('customer_files.open', $file->id) }}" target="_blank" rel="noopener">



          <span class="text-blue-600 hover:underline">{{ $file->name ?? $file->url }}</span>
          @if($file->status !== 'OK')
            <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
              <i class="fa fa-exclamation-triangle"></i> Missing
            </span>
          @endif
        </a>
      </td>
      <td class="px-3 py-2">{{ $file->created_at }}</td>
      <td class="px-3 py-2">{{ $file->creator?->name ?? 'Sin usuario' }}</td>

      <td class="px-3 py-2 text-right">
        <button type="button"
                class="js-delete-file inline-flex items-center rounded-md border border-red-600 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50"
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
