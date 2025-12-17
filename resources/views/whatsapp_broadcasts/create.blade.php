@extends('layout')

@section('content')
@php
    $defaultAccount = $accounts->firstWhere('is_default', true) ?? $accounts->first();
    $defaultAccountId = optional($defaultAccount)->id;
    $defaultTemplate = optional($defaultAccount)->templates->first();
    $selectedAccountId = old('account_id', $defaultAccountId);
    $preselectedTemplateId = old('template_id', optional($defaultTemplate)->id);
    $oldStatusIds = old('filters.status_ids', []);
    if (!is_array($oldStatusIds)) {
        $oldStatusIds = [];
    }
    $oldTagIds = old('filters.tag_ids', []);
    if (!is_array($oldTagIds)) {
        $oldTagIds = [];
    }
    $oldOwnerIds = old('filters.owner_ids', []);
    if (!is_array($oldOwnerIds)) {
        $oldOwnerIds = [];
    }
    $bodyParameters = old('body_parameters', [
        ['source' => 'customer.name', 'fallback' => null],
    ]);
    if (empty($bodyParameters)) {
        $bodyParameters = [
            ['source' => null, 'fallback' => null],
        ];
    }
    $bodyParameterSources = [
        'customer.name' => 'Nombre completo del contacto',
        'customer.first_name' => 'Nombre corto (primer nombre)',
        'customer.business' => 'Nombre del negocio',
        'customer.city' => 'Ciudad',
        'customer.country' => 'País',
        'customer.phone' => 'Teléfono',
        'advisor.name' => 'Asesor asignado',
        'custom' => 'Texto fijo (escribe debajo)',
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div>
        <h1 class="h3 mb-1">Envío masivo por WhatsApp</h1>
        <p class="text-muted mb-0">Define la audiencia, la plantilla y la cadencia para reemplazar el flujo de n8n.</p>
    </div>
    <div class="btn-toolbar">
        <a href="{{ route('whatsapp-accounts.index') }}" class="btn btn-outline-secondary btn-sm mr-2">Gestionar cuentas</a>
        <a href="/customers" class="btn btn-outline-light btn-sm">Ver clientes</a>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <p class="mb-2"><strong>Revisa la información:</strong></p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($accounts->isEmpty())
    <div class="alert alert-warning">
        Primero debes registrar al menos una cuenta de WhatsApp Business y sincronizar plantillas.
        <a href="{{ route('whatsapp-accounts.create') }}" class="alert-link">Hazlo aquí.</a>
    </div>
@endif

<form method="POST" action="{{ route('whatsapp-broadcasts.store') }}" id="whatsappBroadcastForm">
    @csrf
    <div class="card mb-4">
        <div class="card-header">Información general</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">Nombre interno del envío</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', 'Invitación en vivo 11 de diciembre') }}" required>
                    <small class="form-text text-muted">Solo lo verán los usuarios del CRM.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="description">Descripción / notas (opcional)</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}" placeholder="Ej: Recordatorio Quiz + segmento importación">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <strong>Audiencia</strong>
                <span class="text-muted d-block small">Selecciona desde el CRM qué leads entran a este envío.</span>
            </div>
            <span class="badge badge-light" id="audienceCounter">Pendiente de cálculo</span>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="filters_search">Buscar (nombre, email, etc.)</label>
                    <input type="text" class="form-control" id="filters_search" name="filters[search]" value="{{ old('filters.search') }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="filters_limit">Límite máximo de contactos</label>
                    <input type="number" class="form-control" id="filters_limit" name="limit" min="1" max="10000" value="{{ old('limit', 500) }}">
                    <small class="form-text text-muted">Deja el valor por defecto para recorrer la lista completa.</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Solo contactos con WhatsApp</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="filters_has_phone" name="filters[has_phone]" value="1" @checked(old('filters.has_phone'))>
                        <label class="custom-control-label" for="filters_has_phone">Obligatorio tener teléfono limpio</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="filters_status_ids">Estados</label>
                    <select multiple size="6" class="form-control" id="filters_status_ids" name="filters[status_ids][]">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" @selected(in_array($status->id, $oldStatusIds))>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Mantén presionado Ctrl / Cmd para seleccionar varios.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="filters_tag_ids">Etiquetas</label>
                    <select multiple size="6" class="form-control" id="filters_tag_ids" name="filters[tag_ids][]">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $oldTagIds))>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="filters_owner_ids">Asesores</label>
                    <select multiple size="6" class="form-control" id="filters_owner_ids" name="filters[owner_ids][]">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, $oldOwnerIds))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="filters_created_from">Creado desde</label>
                    <input type="date" class="form-control" id="filters_created_from" name="filters[created_from]" value="{{ old('filters.created_from') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="filters_created_to">Creado hasta</label>
                    <input type="date" class="form-control" id="filters_created_to" name="filters[created_to]" value="{{ old('filters.created_to') }}">
                </div>
                <div class="form-group col-md-6">
                    <label class="d-block">Segmento objetivo</label>
                    <div class="p-3 bg-light border rounded h-100">
                        <p class="small mb-2">
                            Combina los filtros para recrear lo que antes venía de Google Sheets.
                            El conteo real se calculará justo antes de ejecutar el envío.
                        </p>
                        <ul class="small pl-3 mb-0 text-muted">
                            <li>Puedes guardar este segmento como reporte si lo necesitas más adelante.</li>
                            <li>Evita seleccionar más de 10.000 registros por lote.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Mensaje de WhatsApp</strong>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="account_id">Cuenta de envío</label>
                    <select class="form-control" id="account_id" name="account_id" @disabled($accounts->isEmpty())>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected($account->id == $selectedAccountId)>
                                {{ $account->name ?? 'Sin nombre' }} @if($account->is_default) (Predeterminada) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="template_id">Plantilla aprobada</label>
                    <select class="form-control" id="template_id" name="template_id">
                        <option value="">Selecciona una plantilla</option>
                        @if ($defaultAccount && $defaultAccount->id == $selectedAccountId)
                            @foreach ($defaultAccount->templates as $tpl)
                                <option value="{{ $tpl->id }}" data-language="{{ $tpl->language }}" data-name="{{ $tpl->name }}" data-status="{{ $tpl->status }}" data-category="{{ $tpl->category }}" @selected($tpl->id == $preselectedTemplateId)>
                                    {{ $tpl->name }} ({{ $tpl->language ?? 'sin idioma' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <small class="text-muted d-block" id="template_meta"></small>
                </div>
                <div class="form-group col-md-4">
                    <label for="template_language">Idioma (código)</label>
                    <input type="text" class="form-control" id="template_language" name="template_language" value="{{ old('template_language', optional($defaultTemplate)->language ?? 'es') }}" placeholder="es, en_US, pt_BR...">
                </div>
            </div>
            <div class="form-group">
                <label for="template_name">Nombre que espera Meta</label>
                <input type="text" class="form-control" id="template_name" name="template_name" value="{{ old('template_name', optional($defaultTemplate)->name) }}" placeholder="crecer_2025_original" required>
                <small class="text-muted">Se completará automáticamente al elegir una plantilla sincronizada.</small>
            </div>

            <hr>
            <h6>Encabezado</h6>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="header_type">Tipo</label>
                    <select class="form-control" id="header_type" name="header_type">
                        @php $headerTypeOld = old('header_type', 'image'); @endphp
                        <option value="none" @selected($headerTypeOld === 'none')>Sin encabezado</option>
                        <option value="image" @selected($headerTypeOld === 'image')>Imagen</option>
                        <option value="video" @selected($headerTypeOld === 'video')>Video</option>
                        <option value="document" @selected($headerTypeOld === 'document')>Documento</option>
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <label for="header_media_url">URL del archivo</label>
                    <input type="text" class="form-control" id="header_media_url" name="header_media_url" value="{{ old('header_media_url', 'https://maquiempanadas.com/quiz-escalable/img/envivo_dic_2025_11.jpg') }}" placeholder="https://ejemplo.com/imagen.jpg">
                    <small class="text-muted">Se usa para replicar el header del flujo de n8n.</small>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Parámetros del cuerpo</h6>
            <p class="text-muted small">
                Cada parámetro se enviará como {{ '{' }}{{ '{' }}1{{ '}' }}{{ '}' }}, {{ '{' }}{{ '{' }}2{{ '}' }}{{ '}' }}, etc. según el orden. Si dejas filas vacías no se enviarán.
            </p>
            <div id="bodyParametersWrapper">
                @foreach ($bodyParameters as $index => $parameter)
                    <div class="border rounded p-3 mb-3 body-parameter" data-index="{{ $index }}">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Parámetro #{{ $index + 1 }}</label>
                                <select class="form-control body-parameter-source" name="body_parameters[{{ $index }}][source]">
                                    <option value="">No usar</option>
                                    @foreach ($bodyParameterSources as $value => $label)
                                        <option value="{{ $value }}" @selected(($parameter['source'] ?? null) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Texto fijo / fallback</label>
                                <input type="text" class="form-control body-parameter-fallback" name="body_parameters[{{ $index }}][fallback]" value="{{ $parameter['fallback'] ?? '' }}" placeholder="Opcional: se usa con fuente 'Texto fijo' o como fallback">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="addBodyParameter">Agregar parámetro</button>
            <div class="alert alert-light small">
                Disponibles como variables: <strong>Nombre</strong>, <strong>Ciudad</strong>, <strong>País</strong>, <strong>Negocio</strong>, <strong>Teléfono</strong>, <strong>Asesor</strong>. Más campos podrán añadirse luego.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Ejecución y seguimiento</strong>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="wait_seconds">Tiempo de espera entre envíos (segundos)</label>
                    <input type="number" class="form-control" id="wait_seconds" name="wait_seconds" min="0" max="3600" value="{{ old('wait_seconds', 30) }}" required>
                    <small class="text-muted">Reemplaza la espera de 30s configurada en n8n.</small>
                </div>
                <div class="form-group col-md-8">
                    <label for="action_note">Texto que se almacenará como acción</label>
                    <input type="text" class="form-control" id="action_note" name="action_note" value="{{ old('action_note', 'Envío masivo: invitación en vivo 11 de Dic Quiz') }}" required maxlength="255">
                    <small class="text-muted">Este texto aparecerá en la línea de tiempo de cada cliente cuando se dispare el job.</small>
                </div>
            </div>
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <div class="text-muted small mb-1">Vista previa del registro de acción</div>
                            <div id="actionNotePreview" class="font-weight-bold">{{ old('action_note', 'Envío masivo: invitación en vivo 11 de Dic Quiz') }}</div>
                        </div>
                        <span class="text-muted small">Se guardará con el ID de acción tipo 14.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <a href="/customers" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-primary" @disabled($accounts->isEmpty())>Guardar configuración</button>
    </div>
</form>

<template id="bodyParameterTemplate">
    <div class="border rounded p-3 mb-3 body-parameter" data-index="__INDEX__">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Parámetro #__HUMAN_INDEX__</label>
                <select class="form-control body-parameter-source" name="body_parameters[__INDEX__][source]">
                    <option value="">No usar</option>
                    @foreach ($bodyParameterSources as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-8">
                <label>Texto fijo / fallback</label>
                <input type="text" class="form-control body-parameter-fallback" name="body_parameters[__INDEX__][fallback]" placeholder="Opcional: se usa con fuente 'Texto fijo' o como fallback">
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateCatalog = @json($templateCatalog);
    const templateSelect = document.getElementById('template_id');
    const accountSelect = document.getElementById('account_id');
    const templateNameInput = document.getElementById('template_name');
    const templateLanguageInput = document.getElementById('template_language');
    const templateMeta = document.getElementById('template_meta');
    const headerType = document.getElementById('header_type');
    const headerUrl = document.getElementById('header_media_url');
    const actionNote = document.getElementById('action_note');
    const actionPreview = document.getElementById('actionNotePreview');
    const addBodyParameterBtn = document.getElementById('addBodyParameter');
    const bodyWrapper = document.getElementById('bodyParametersWrapper');
    const bodyTemplate = document.getElementById('bodyParameterTemplate').innerHTML;
    let bodyParameterIndex = {{ count($bodyParameters) }};

    function renderTemplateOptions(accountId) {
        if (!templateSelect) {
            return;
        }
        const selected = templateSelect.value;
        templateSelect.innerHTML = '<option value="">Selecciona una plantilla</option>';
        const options = templateCatalog[accountId] || [];
        options.forEach((tpl) => {
            const option = document.createElement('option');
            option.value = tpl.id;
            option.textContent = `${tpl.name} (${tpl.language || 'sin idioma'})`;
            option.dataset.language = tpl.language || '';
            option.dataset.name = tpl.name || '';
            option.dataset.status = tpl.status || '';
            option.dataset.category = tpl.category || '';
            if (String(tpl.id) === String(selected)) {
                option.selected = true;
            }
            templateSelect.appendChild(option);
        });
        if (!templateSelect.value && options.length) {
            templateSelect.value = options[0].id;
        }
        templateSelect.dispatchEvent(new Event('change'));
    }

    function updateTemplateMeta() {
        if (!templateSelect) {
            return;
        }
        const option = templateSelect.options[templateSelect.selectedIndex];
        if (option && option.value) {
            templateNameInput.value = option.dataset.name || option.textContent;
            if (option.dataset.language) {
                templateLanguageInput.value = option.dataset.language;
            }
            const status = option.dataset.status ? option.dataset.status : 'sin estado';
            const category = option.dataset.category ? option.dataset.category : 'sin categoría';
            templateMeta.textContent = `${status} · ${category}`;
        } else {
            templateMeta.textContent = '';
        }
    }

    if (accountSelect) {
        accountSelect.addEventListener('change', function () {
            renderTemplateOptions(this.value);
        });
        renderTemplateOptions(accountSelect.value);
    }

    if (templateSelect) {
        templateSelect.addEventListener('change', updateTemplateMeta);
        const presetTemplateId = @json($preselectedTemplateId);
        if (presetTemplateId) {
            templateSelect.value = presetTemplateId;
        }
        updateTemplateMeta();
    }

    if (headerType && headerUrl) {
        const toggleHeaderUrl = () => {
            const disabled = headerType.value === 'none';
            headerUrl.disabled = disabled;
            if (disabled) {
                headerUrl.classList.add('bg-light');
            } else {
                headerUrl.classList.remove('bg-light');
            }
        };
        headerType.addEventListener('change', toggleHeaderUrl);
        toggleHeaderUrl();
    }

    if (actionNote && actionPreview) {
        const syncPreview = () => {
            actionPreview.textContent = actionNote.value || '—';
        };
        actionNote.addEventListener('input', syncPreview);
        syncPreview();
    }

    if (addBodyParameterBtn) {
        addBodyParameterBtn.addEventListener('click', function () {
            const nextIndex = bodyParameterIndex++;
            const html = bodyTemplate
                .replace(/__INDEX__/g, nextIndex)
                .replace(/__HUMAN_INDEX__/g, nextIndex + 1);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            bodyWrapper.appendChild(wrapper.firstElementChild);
        });
    }
});
</script>
@endpush
