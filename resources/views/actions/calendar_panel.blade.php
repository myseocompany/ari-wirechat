<div class="card w-100">
  <div class="card-body">
    <div class="form-row align-items-end">
      <div class="form-group col-6">
        <label class="small text-muted mb-1">Usuario</label>
        <select id="fc-user" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-6">
        <label class="small text-muted mb-1">Tipo</label>
        <select id="fc-type" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach(($types ?? []) as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-7">
        <div class="form-check mt-2">
          <input id="fc-pending" class="form-check-input" type="checkbox" checked>
          <label class="form-check-label" for="fc-pending">Solo pendientes</label>
        </div>
      </div>
      <div class="form-group col-5 text-right">
        <button id="fc-apply" class="btn btn-primary btn-sm">Aplicar</button>
      </div>
    </div>
    <div id="calendar"></div>
  </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>#calendar{min-height:600px}</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/es.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const el = document.getElementById('calendar'); if(!el) return;

  const calendar = new FullCalendar.Calendar(el, {
    height:'auto',
    locale:'es',
    initialView:'dayGridMonth', // mes por defecto
    headerToolbar:{
      left:'prev,next today',
      center:'title',
      right:'dayGridMonth,timeGridWeek,timeGridDay,listWeek' // mes/semana/día/lista
    },
    navLinks:true,
    nowIndicator:true,
    editable:true,
    eventSources:[{
      url:'{{ route('actions.calendar.feed') }}',
      method:'GET',
      extraParams:()=>({
        user_id: document.getElementById('fc-user').value || '',
        type_id: document.getElementById('fc-type').value || '',
        pending: document.getElementById('fc-pending').checked ? 'true' : 'false',
        search: '{{ $request->action_search ?? '' }}',
      })
    }],
    eventClick(info){
      if(info.event.url){ window.location=info.event.url; info.jsEvent.preventDefault(); }
    },
    eventDrop:saveMove,
    eventResize:saveMove,
  });

  function saveMove(info){
    fetch(`{{ route('actions.reschedule',['action'=>'__ID__']) }}`.replace('__ID__', info.event.id),{
      method:'PUT',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({ due_date: info.event.start.toISOString() })
    }).then(r=>{ if(!r.ok) throw new Error('No se pudo reprogramar'); })
      .catch(e=>{ alert(e.message); info.revert(); });
  }

  calendar.render();
  document.getElementById('fc-apply').addEventListener('click', ()=> calendar.refetchEvents());
});
</script>
@endpush
