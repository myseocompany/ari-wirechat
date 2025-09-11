{{-- resources/views/reports/missing_customer_files/index.blade.php --}}
@extends('layout')

@section('content')
@php
  use Carbon\Carbon;
  $defaultYear     = $selectedYear ?? 2025;
  $selMonth        = $selectedMonth ?? null;
  $selUserId       = $selectedUserId ?? null;
  $monthsAvailable = $months ?? [];    // e.g. [1,2,3,...]
@endphp

<div class="container">
  <h1>Customer Files</h1>

  {{-- Filtros --}}
  <form method="get" action="{{ route('reports.missing_customer_files') }}" class="mb-3">
    <div class="form-row">
      <div class="col-md-3">
        <label>Año</label>
        <select name="year" class="form-control">
          @foreach($availableYears as $y)
            <option value="{{ $y }}" @selected($defaultYear == $y)>{{ $y }}</option>
          @endforeach
          @if(!$availableYears->contains(2025))
            <option value="2025" @selected($defaultYear == 2025)>2025</option>
          @endif
        </select>
      </div>

      <div class="col-md-3">
        <label>Mes (opcional)</label>
        <select name="month" class="form-control">
          <option value="">— Resumen del año —</option>
          @for($m=1; $m<=12; $m++)
<option value="{{ $m }}" @selected($selMonth == $m)>
  {{ \Carbon\Carbon::create()->month((int)$m)->translatedFormat('F') }}
</option>

          @endfor
        </select>
      </div>

      <div class="col-md-4">
        <label>Usuario</label>
        <select name="user_id" class="form-control">
          <option value="">Todos</option>
          <option value="unassigned" @selected($selUserId === 'unassigned')>Sin asignar</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected($selUserId == $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 align-self-end">
        <button class="btn btn-primary btn-block">Generar reporte</button>
      </div>
    </div>
  </form>

  {{-- ============================================================
       1) SOLO AÑO → RESUMEN POR MESES (cálculo por AJAX)
     ============================================================ --}}
  @if($defaultYear && !$selMonth)
    <h2>Customer Files – Year {{ $defaultYear }}</h2>

    @if(empty($monthsAvailable))
      <div class="alert alert-info">No hay clientes ganados en este año.</div>
    @else
      <div class="list-group" id="year-summary">
        @foreach($monthsAvailable as $m)
          @php
            $mn       = Carbon::create()->month((int)$m)->translatedFormat('F');
            $link     = route('reports.missing_customer_files', ['year'=>$defaultYear, 'month'=>$m, 'user_id'=>$selUserId]);
            $verify   = route('reports.missing_customer_files.verify_month', ['year'=>$defaultYear,'month'=>$m,'user_id'=>$selUserId]);
          @endphp
          <a href="{{ $link }}"
             class="list-group-item list-group-item-action d-flex justify-content-between align-items-center js-month-item"
             data-verify-url="{{ $verify }}">
            <span>{{ ucfirst($mn) }}</span>
            <span class="badge badge-secondary badge-pill js-month-count">calculando…</span>
          </a>
        @endforeach
      </div>
    @endif
  @endif

  {{-- ============================================================
       2) AÑO + MES → RESUMEN DEL MES + 3) DETALLE POR CLIENTE
     ============================================================ --}}
  @if($defaultYear && $selMonth)
    @php
      $monthName = \Carbon\Carbon::create()->month((int)$selMonth)->translatedFormat('F');
      $verifyMonthUrl = route('reports.missing_customer_files.verify_month', [
        'year'   => $defaultYear,
        'month'  => $selMonth,
        'user_id'=> $selUserId,
      ]);
    @endphp

    {{-- Resumen del mes (pill, se llena por AJAX) --}}
    <div class="mb-3">
      <a href="#"
         class="btn btn-outline-primary"
         id="month-pill"
         data-verify-url="{{ $verifyMonthUrl }}">
        {{ ucfirst($monthName) }} — <span class="js-month-count">calculando…</span>
      </a>
    </div>

    {{-- Tabla detalle por cliente (clic en "Ver detalle" = archivos del cliente) --}}
    @isset($customers)
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Última actualización</th>
            <th>Total archivos</th>
            <th>Faltantes</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($customers as $customer)
            @php $rowId = 'customer-'.$customer->id; @endphp
            <tr id="{{ $rowId }}" class="js-customer-row"
    data-verify-url="{{ route('reports.missing_customer_files.verify', $customer) }}">
              <td>
                {{-- Drill al cliente (vista completa del cliente) --}}
                <a href="{{ url('customers/'.$customer->id.'/show') }}" target="_blank">{{ $customer->name }}</a>
              </td>
              <td>{{ optional($customer->updated_at)->format('Y-m-d') }}</td>
              <td class="js-total">{{ $customer->files_count ?? $customer->total_files ?? 0 }}</td>
              <td><span class="badge badge-secondary js-missing">—</span></td>
              <td>
                {{-- Drill dentro del cliente: archivos --}}
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-toggle="collapse" data-target="#detail-{{ $rowId }}">
                  Ver detalle
                </button>
              </td>
            </tr>

            {{-- Archivos del cliente (se marcan OK/MISSING con el resultado AJAX por cliente) --}}
{{-- Dentro del <tr class="collapse" id="detail-{{ $rowId }}"> --}}
<tr class="collapse" id="detail-{{ $rowId }}">    
    <td colspan="5">
    {{-- Subida masiva para ESTE cliente --}}
    <form method="POST" action="{{ route('customer_files.store') }}"
            enctype="multipart/form-data" class="mb-3">
        @csrf
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <div class="form-row align-items-center">
        <div class="col-auto">
            <input type="file" name="files[]" multiple required class="form-control-file">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Subir archivos</button>
        </div>
        </div>
    </form>

    @if($customer->files->isEmpty())
        <p class="text-muted mb-0">No files for this customer.</p>
    @else
        <style>
        .file-name { overflow-wrap:anywhere; word-break:break-word; }
        .file-path { font-family: monospace; }
        </style>

        <table class="table table-sm table-bordered mt-2">
        <thead>
            <tr>
            <th>Archivo</th>
            <th class="d-none d-md-table-cell">Path</th>
            <th>Status</th>
            <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customer->files as $f)
            <tr data-file-id="{{ $f->id }}">
                <td class="file-name">{{ $f->url }}</td>
                <td class="file-path d-none d-md-table-cell">/files/{{ $customer->id }}/{{ $f->url }}</td>

                {{-- el verificador AJAX actualizará este badge a OK/MISSING --}}
                <td class="js-status"><span class="badge badge-light">?</span></td>

                <td class="js-actions">
                {{-- A) Abrir (link seguro) --}}
                <a href="{{ route('customer_files.open', $f->id) }}"
                    target="_blank"
                    class="btn btn-sm btn-outline-secondary d-none js-open-btn">
                    Abrir
                </a>

                {{-- B) Reponer (mismo nombre) --}}
                <form action="{{ route('customer_files.reupload', $f->id) }}"
                        method="POST" enctype="multipart/form-data"
                        class="d-none js-reupload-form mt-1">
                    @csrf
                    <div class="d-flex align-items-center">
                    <label class="btn btn-sm btn-outline-secondary mb-0 mr-2">
                        Seleccionar archivo
                        <input type="file" name="file" required class="d-none js-reupload-input">
                    </label>
                    <button type="submit" class="btn btn-sm btn-warning">Reponer</button>
                    </div>
                    <small class="text-muted js-selected-name"></small>
                </form>

                {{-- C) (opcional) Eliminar a papelera --}}
                <button type="button"
                        class="btn btn-sm btn-outline-danger ml-1 js-delete-file"
                        data-id="{{ $f->id }}"
                        data-url="{{ route('customer_files.destroy', $f->id) }}">
                    <i class="fa fa-trash"></i>
                </button>
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>
    @endif
    </td>
    </tr>

          @empty
            <tr><td colspan="5" class="text-muted">No hay clientes para este mes.</td></tr>
          @endforelse
        </tbody>
      </table>

      {{-- Paginación si viene del controlador --}}
      @if(method_exists($customers,'links'))
        <div class="mt-3">
          {{ $customers->appends(request()->query())->links() }}
        </div>
      @endif
    @else
      <div class="alert alert-info">No hay datos de clientes para mostrar.</div>
    @endisset
  @endif

</div> {{-- /.container --}}

{{-- ============================================================
     SCRIPTS: Resumen por meses (año) y detalle por persona (mes)
   ============================================================ --}}
<script>
(function(){
  // ====== 1) Resumen por MESES (cuando solo hay año) ======
  const monthItems = document.querySelectorAll('.js-month-item');
  monthItems.forEach(item => {
    const url  = item.getAttribute('data-verify-url');
    const pill = item.querySelector('.js-month-count');
    fetch(url, {headers:{'Accept':'application/json'}})
      .then(r => r.json())
      .then(d => {
        if(!d.ok) throw new Error();
        pill.textContent = `${d.missing} archivos faltantes`;
        pill.classList.toggle('badge-danger', d.missing > 0);
        pill.classList.toggle('badge-success', d.missing === 0);
        pill.classList.remove('badge-secondary');
      })
      .catch(() => {
        pill.textContent = 'error';
        pill.classList.remove('badge-secondary');
        pill.classList.add('badge-warning');
      });
  });

  // ====== 2) Resumen del MES (pill superior) ======
  const monthPill = document.getElementById('month-pill');
  if (monthPill) {
    const url = monthPill.getAttribute('data-verify-url');
    const span = monthPill.querySelector('.js-month-count');
    fetch(url, {headers:{'Accept':'application/json'}})
      .then(r => r.json())
      .then(d => {
        if(!d.ok) throw new Error();
        span.textContent = `${d.missing} archivos faltantes`;
        monthPill.classList.toggle('btn-outline-danger', d.missing > 0);
        monthPill.classList.toggle('btn-outline-success', d.missing === 0);
        monthPill.classList.remove('btn-outline-primary');
      })
      .catch(() => {
        span.textContent = 'error';
        monthPill.classList.remove('btn-outline-primary');
        monthPill.classList.add('btn-outline-warning');
      });
  }

  // ====== 3) Detalle por CLIENTE (cuando hay año+mes) ======
  const rows = Array.from(document.querySelectorAll('.js-customer-row'));
  if (rows.length) {
    const CONCURRENCY = 4;
    runQueue(rows.map(r => () => verifyRow(r)), CONCURRENCY);
  }

  async function runQueue(tasks, parallel) {
    const workers = Array(Math.min(parallel, tasks.length)).fill(0).map(worker);
    async function worker(){
      while (tasks.length) {
        const t = tasks.shift();
        await t();
        await sleep(80);
      }
    }
    await Promise.all(workers);
  }

  async function verifyRow(row){
    const url = row.dataset.verifyUrl;
    const missingBadge = row.querySelector('.js-missing');
    const detailId = row.nextElementSibling?.id ? ('#' + row.nextElementSibling.id) : null;

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error();

      // faltantes por cliente
      missingBadge.className = 'badge ' + (data.missing ? 'badge-danger' : 'badge-success');
      missingBadge.textContent = data.missing;

      // marcar cada archivo en el detalle
      if (detailId) {
        const table = document.querySelector(detailId + ' table');
        if (table && data.results) {
          data.results.forEach(it => {
            const tr = table.querySelector(`tr[data-file-id="${it.id}"]`);
            if (!tr) return;

            // Badge de estado
            tr.querySelector('.js-status').innerHTML = it.exists
              ? '<span class="badge badge-success">OK</span>'
              : '<span class="badge badge-danger">MISSING</span>';

            // Acciones: Abrir / Reponer
            const actions  = tr.querySelector('.js-actions');
            const openBtn  = actions?.querySelector('.js-open-btn');
            const repForm  = actions?.querySelector('.js-reupload-form');

            if (it.exists) {
              if (openBtn) {
                openBtn.classList.remove('d-none');
                if (it.href) openBtn.href = it.href; // si tu verificador devuelve URL firmada
              }
              if (repForm) repForm.classList.add('d-none');
            } else {
              if (openBtn) openBtn.classList.add('d-none');
              if (repForm) repForm.classList.remove('d-none');
            }
          });
        }
      }
    } catch (e) {
      if (missingBadge) {
        missingBadge.className = 'badge badge-warning';
        missingBadge.textContent = 'ERR';
      }
    }
  }

  // Mostrar el nombre del archivo seleccionado en cada form de reupload
  document.addEventListener('change', (e) => {
    const inp = e.target.closest('.js-reupload-input');
    if (!inp) return;
    const form = inp.closest('.js-reupload-form');
    const small = form?.querySelector('.js-selected-name');
    const name = inp.files?.[0]?.name || '';
    if (small) small.textContent = name ? `Seleccionado: ${name}` : '';
  });

  // Eliminar a papelera (DELETE)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-delete-file');
    if (!btn) return;

    e.preventDefault();
    if (!confirm('¿Mover a la papelera este archivo?')) return;

    const url = btn.dataset.url;
    const row = btn.closest('tr');

    try {
      btn.disabled = true;
      const res = await fetch(url, {
        method : 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
          'Accept'          : 'application/json',
        },
      });
      const json = await res.json();
      if (!res.ok || !json.ok) throw new Error(json.message || 'Error al eliminar');
      row?.remove();
    } catch (err) {
      alert('No se pudo eliminar. Intenta nuevamente.');
      btn.disabled = false;
    }
  });

  function sleep(ms){ return new Promise(r => setTimeout(r, ms)); }
})();
</script>

@endsection
