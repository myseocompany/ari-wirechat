@extends('layout')

@section('content')
<div class="config-dashboard my-4">
  <div class="config-hero shadow-sm">
    <div>
      <p class="text-uppercase text-muted mb-1 small font-weight-bold">Panel de configuración</p>
      <h1 class="h3 mb-2">Haz que el CRM trabaje como tu equipo lo necesita</h1>
      <p class="mb-0 text-muted">
        Gestiona usuarios, canales, etapas y automatizaciones desde un solo lugar.
      </p>
    </div>
    <div class="config-hero__actions">
      <a href="{{ route('admin.leads-distribution.index') }}" class="btn btn-primary btn-sm">Distribución de leads</a>
      <a href="{{ route('users') }}" class="btn btn-outline-secondary btn-sm">Ver usuarios</a>
    </div>
  </div>

  <section class="config-section">
    <header>
      <h2>Clientes y flujos de trabajo</h2>
      <p>Define cómo se clasifican los leads y gestiona las etapas operativas.</p>
    </header>
    <div class="config-grid">
      <article class="config-card">
        <h3>Estados de clientes</h3>
        <p>Ordena las etapas del embudo y su color para reportes y tableros.</p>
        <a href="{{ url('/customer_statuses') }}" class="btn btn-light btn-sm">Configurar</a>
      </article>
      <article class="config-card">
        <h3>Distribución de leads</h3>
        <p>Controla qué porcentaje de clientes recibe cada asesor.</p>
        <a href="{{ route('admin.leads-distribution.index') }}" class="btn btn-light btn-sm">Abrir panel</a>
      </article>
      <article class="config-card">
        <h3>Audiencias</h3>
        <p>Sincroniza segmentos con campañas externas y automatizaciones.</p>
        <a href="{{ url('/audiences') }}" class="btn btn-light btn-sm">Gestionar</a>
      </article>
      <article class="config-card">
        <h3>Campañas</h3>
        <p>Define las fuentes activas para atribuir cada lead.</p>
        <a href="{{ url('/campaigns') }}" class="btn btn-light btn-sm">Ver campañas</a>
      </article>
      
    </div>
  </section>

  <section class="config-section">
    <header>
      <h2>Comunicación y plantillas</h2>
      <p>Actualiza los contenidos que se envían a los clientes.</p>
    </header>
    <div class="config-grid">
      <article class="config-card">
        <h3>Emails</h3>
        <p>Crea o ajusta plantillas para los envíos automáticos.</p>
        <a href="{{ url('/e-mails') }}" class="btn btn-light btn-sm">Editar emails</a>
      </article>
      <article class="config-card">
        <h3>Cuentas de WhatsApp</h3>
        <p>Conecta números oficiales y sincroniza plantillas aprobadas.</p>
        <a href="{{ url('/whatsapp-accounts') }}" class="btn btn-light btn-sm">Administrar</a>
      </article>
      <article class="config-card">
        <h3>Líneas WAToolBox</h3>
        <p>Gestiona APIKEY, teléfono, estado y source_id por cada línea.</p>
        <a href="{{ route('message-sources.index') }}" class="btn btn-light btn-sm">Administrar líneas</a>
      </article>
      <article class="config-card">
        <h3>FAQ</h3>
        <p>Mantén al día la base de conocimiento para el equipo.</p>
        <a href="{{ url('/faq') }}" class="btn btn-light btn-sm">Actualizar FAQ</a>
      </article>
    </div>
  </section>

  <section class="config-section">
    <header>
      <h2>Usuarios y permisos</h2>
      <p>Controla quién accede al CRM y qué puede gestionar.</p>
    </header>
    <div class="config-grid">
      <article class="config-card">
        <h3>Usuarios</h3>
        <p>Crea cuentas nuevas o actualiza datos existentes.</p>
        <a href="{{ route('users') }}" class="btn btn-light btn-sm">Ver usuarios</a>
      </article>
      <article class="config-card">
        <h3>Roles</h3>
        <p>Define permisos por perfil y asigna responsabilidades.</p>
        <a href="{{ route('roles') }}" class="btn btn-light btn-sm">Editar roles</a>
      </article>
      <article class="config-card">
        <h3>Etiquetas</h3>
        <p>Clasifica clientes con etiquetas visibles desde el tablero.</p>
        <a href="{{ route('tags.index') }}" class="btn btn-light btn-sm">Gestionar etiquetas</a>
      </article>
    </div>
  </section>

  <section class="config-section">
    <header>
      <h2>Catálogo y sistema</h2>
      <p>Opciones avanzadas para integraciones y componentes del sitio.</p>
    </header>
    <div class="config-grid">
      <article class="config-card">
        <h3>Productos</h3>
        <p>Actualiza el catálogo y su información comercial.</p>
        <a href="{{ url('/products') }}" class="btn btn-light btn-sm">Ver productos</a>
      </article>
      <article class="config-card">
        <h3>Menús</h3>
        <p>Controla la navegación principal del CRM.</p>
        <a href="{{ route('menus.index') }}" class="btn btn-light btn-sm">Editar menús</a>
      </article>
      <article class="config-card">
        <h3>Logs de solicitudes API</h3>
        <p>Revisa el historial de integraciones para detectar errores.</p>
        <a href="{{ url('/request-logs') }}" class="btn btn-light btn-sm">Abrir logs</a>
      </article>
      <article class="config-card">
        <h3>Actividad de usuarios</h3>
        <p>Monitorea acciones del CRM como ediciones, archivos y pedidos.</p>
        <a href="{{ url('/activity-logs') }}" class="btn btn-light btn-sm">Ver actividad</a>
      </article>
      <article class="config-card">
        <h3>Recuperación Channels</h3>
        <p>Busca llamadas faltantes de Channels y encola la recuperación de audios.</p>
        <a href="{{ route('reports.channels_calls_recovery') }}" class="btn btn-light btn-sm">Abrir recuperación</a>
      </article>
    </div>
  </section>
</div>
@endsection

@push('styles')
<style>
  .config-dashboard {
    max-width: 1100px;
    margin: 0 auto;
  }
  .config-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fbff;
    border-radius: 18px;
    padding: 28px 32px;
    border: 1px solid #e6edf6;
  }
  .config-hero__actions a + a {
    margin-left: 8px;
  }
  .config-section {
    margin-top: 32px;
  }
  .config-section header {
    margin-bottom: 16px;
  }
  .config-section h2 {
    font-size: 1.15rem;
    margin-bottom: 4px;
  }
  .config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
  }
  .config-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e6edf6;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .config-card h3 {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #0f172a;
  }
  .config-card p {
    font-size: 0.9rem;
    color: #475569;
    flex-grow: 1;
  }
  .config-card .btn {
    align-self: flex-start;
    font-weight: 600;
  }
</style>
@endpush
