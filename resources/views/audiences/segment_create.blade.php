@extends('layout')

@php
    $defaultSegment = [
        'conditions' => [
            [
                'boolean' => 'AND',
                'field' => 'created_at',
                'operator' => 'between',
                'value' => [now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d')],
            ],
        ],
    ];
    $segmentJsonValue = old('segment_json', json_encode($defaultSegment, JSON_UNESCAPED_UNICODE));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div>
        <h1 class="h3 mb-1">Crear audiencia por variables</h1>
        <p class="text-muted mb-0">Selecciona condiciones, previsualiza la SQL y guarda la audiencia para campañas de WhatsApp.</p>
    </div>
    <a href="/audiences" class="btn btn-outline-secondary btn-sm">Volver a audiencias</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa la configuración:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('audiences.segment.store') }}" id="audienceSegmentForm">
    @csrf
    <input type="hidden" name="segment_json" id="segment_json" value="{{ $segmentJsonValue }}">

    <div class="card mb-4">
        <div class="card-header">Datos de la audiencia</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="name">Nombre de la audiencia</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', 'Audiencia segmentada '.now()->format('d-m-Y H:i')) }}" required maxlength="200">
                </div>
                <div class="form-group col-md-5">
                    <label for="max_recipients">Límite de contactos (opcional)</label>
                    <input type="number" id="max_recipients" name="max_recipients" class="form-control" min="1" max="50000" value="{{ old('max_recipients') }}" placeholder="Sin límite">
                    <small class="form-text text-muted">Si lo dejas vacío se incluirán todos los resultados.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <strong>Reglas de segmentación</strong>
                <span class="text-muted d-block small">Combina condiciones con AND / OR para construir tu audiencia.</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addConditionBtn">+ Agregar condición</button>
        </div>
        <div class="card-body">
            <div id="conditionsContainer" class="d-flex flex-column gap-2"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <strong>Previsualización</strong>
                <span class="text-muted d-block small">Genera la SQL y revisa una muestra antes de guardar.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="previewBtn">Previsualizar SQL</button>
                <span class="badge badge-info" id="previewCounter">Sin calcular</span>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3" id="previewFeedback"></div>
            <label class="small text-muted mb-1">SQL generada</label>
            <pre class="segment-sql-preview" id="sqlPreview">-- Aún no se ha generado la consulta.</pre>

            <label class="small text-muted mt-3 mb-1">Muestra de leads</label>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Ciudad</th>
                            <th>País</th>
                            <th>Creado</th>
                        </tr>
                    </thead>
                    <tbody id="sampleBody">
                        <tr>
                            <td colspan="8" class="text-muted">Aún no hay muestra.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <a href="/audiences" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar audiencia desde segmento</button>
    </div>
</form>

<template id="conditionTemplate">
    <div class="segment-condition border rounded p-3 mb-2">
        <div class="form-row align-items-end">
            <div class="form-group col-md-2 mb-2">
                <label class="small text-muted">Conector</label>
                <select class="form-control form-control-sm condition-boolean">
                    <option value="AND">Y</option>
                    <option value="OR">O</option>
                </select>
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small text-muted">Variable</label>
                <select class="form-control form-control-sm condition-field"></select>
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="small text-muted">Operador</label>
                <select class="form-control form-control-sm condition-operator"></select>
            </div>
            <div class="form-group col-md-3 mb-2 condition-value-wrapper"></div>
            <div class="form-group col-md-1 mb-2 text-right">
                <button type="button" class="btn btn-sm btn-outline-danger condition-remove" title="Eliminar condición">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('styles')
<style>
    .segment-sql-preview {
        background: #0b1220;
        color: #d7e1ff;
        border-radius: 0.5rem;
        padding: 0.75rem;
        max-height: 220px;
        overflow: auto;
        font-size: 0.83rem;
        line-height: 1.35;
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fieldCatalog = @json($segmentFields);
    const segmentInput = document.getElementById('segment_json');
    const conditionTemplate = document.getElementById('conditionTemplate');
    const conditionsContainer = document.getElementById('conditionsContainer');
    const addConditionBtn = document.getElementById('addConditionBtn');
    const previewBtn = document.getElementById('previewBtn');
    const previewCounter = document.getElementById('previewCounter');
    const previewFeedback = document.getElementById('previewFeedback');
    const sqlPreview = document.getElementById('sqlPreview');
    const sampleBody = document.getElementById('sampleBody');
    const form = document.getElementById('audienceSegmentForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const previewUrl = @json(route('audiences.segment.preview'));

    const fieldKeys = Object.keys(fieldCatalog);
    let segmentState = parseSegmentInput(segmentInput.value);

    if (!Array.isArray(segmentState.conditions) || segmentState.conditions.length === 0) {
        segmentState = { conditions: [createDefaultCondition()] };
    }

    renderConditions();
    syncSegmentInput();

    addConditionBtn.addEventListener('click', function () {
        segmentState.conditions.push(createDefaultCondition());
        renderConditions();
        syncSegmentInput();
    });

    previewBtn.addEventListener('click', function () {
        syncSegmentInput();
        previewSegment();
    });

    form.addEventListener('submit', function (event) {
        syncSegmentInput();
        if (!segmentState.conditions || segmentState.conditions.length === 0) {
            event.preventDefault();
            previewFeedback.innerHTML = '<div class="alert alert-warning mb-0">Debes agregar al menos una condición para guardar la audiencia.</div>';
        }
    });

    function parseSegmentInput(raw) {
        try {
            const parsed = JSON.parse(raw || '{}');
            return typeof parsed === 'object' && parsed !== null ? parsed : { conditions: [] };
        } catch (error) {
            return { conditions: [] };
        }
    }

    function createDefaultCondition() {
        const defaultField = fieldKeys[0] || 'created_at';
        const defaultOperator = getDefaultOperator(defaultField);

        return {
            boolean: 'AND',
            field: defaultField,
            operator: defaultOperator,
            value: getDefaultValue(defaultField, defaultOperator),
        };
    }

    function getDefaultOperator(field) {
        const fieldConfig = fieldCatalog[field] || {};
        const operators = Object.keys(fieldConfig.operators || {});

        return operators.length > 0 ? operators[0] : 'eq';
    }

    function getDefaultValue(field, operator) {
        const fieldConfig = fieldCatalog[field] || {};
        const type = fieldConfig.type;
        const options = Array.isArray(fieldConfig.options) ? fieldConfig.options : [];

        if (operator === 'between') {
            return ['', ''];
        }

        if (operator === 'in') {
            return [];
        }

        if (type === 'boolean') {
            return true;
        }

        if (type === 'select') {
            if (options.length === 0) {
                return '';
            }

            return String(options[0].value);
        }

        return '';
    }

    function renderConditions() {
        conditionsContainer.innerHTML = '';

        segmentState.conditions.forEach(function (condition, index) {
            const row = conditionTemplate.content.firstElementChild.cloneNode(true);
            const booleanSelect = row.querySelector('.condition-boolean');
            const fieldSelect = row.querySelector('.condition-field');
            const operatorSelect = row.querySelector('.condition-operator');
            const valueWrapper = row.querySelector('.condition-value-wrapper');
            const removeBtn = row.querySelector('.condition-remove');

            booleanSelect.value = condition.boolean === 'OR' ? 'OR' : 'AND';
            booleanSelect.disabled = index === 0;
            if (index === 0) {
                booleanSelect.value = 'AND';
                condition.boolean = 'AND';
            }
            booleanSelect.addEventListener('change', function (event) {
                condition.boolean = event.target.value === 'OR' ? 'OR' : 'AND';
                syncSegmentInput();
            });

            renderFieldOptions(fieldSelect, condition.field);
            fieldSelect.addEventListener('change', function (event) {
                condition.field = event.target.value;
                condition.operator = getDefaultOperator(condition.field);
                condition.value = getDefaultValue(condition.field, condition.operator);
                renderConditions();
                syncSegmentInput();
            });

            renderOperatorOptions(operatorSelect, condition.field, condition.operator);
            operatorSelect.addEventListener('change', function (event) {
                condition.operator = event.target.value;
                condition.value = getDefaultValue(condition.field, condition.operator);
                renderConditions();
                syncSegmentInput();
            });

            renderValueControl(valueWrapper, condition, index);

            removeBtn.addEventListener('click', function () {
                if (segmentState.conditions.length === 1) {
                    segmentState.conditions[0] = createDefaultCondition();
                } else {
                    segmentState.conditions.splice(index, 1);
                }
                renderConditions();
                syncSegmentInput();
            });

            conditionsContainer.appendChild(row);
        });
    }

    function renderFieldOptions(select, selectedField) {
        select.innerHTML = '';
        Object.entries(fieldCatalog).forEach(function ([field, config]) {
            const option = document.createElement('option');
            option.value = field;
            option.textContent = config.label || field;
            if (field === selectedField) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }

    function renderOperatorOptions(select, field, selectedOperator) {
        select.innerHTML = '';
        const operators = fieldCatalog[field] ? fieldCatalog[field].operators : {};
        Object.entries(operators || {}).forEach(function ([operator, label]) {
            const option = document.createElement('option');
            option.value = operator;
            option.textContent = label;
            if (operator === selectedOperator) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }

    function renderValueControl(wrapper, condition, index) {
        wrapper.innerHTML = '';
        const fieldConfig = fieldCatalog[condition.field] || {};
        const operator = condition.operator;
        const type = fieldConfig.type;

        const label = document.createElement('label');
        label.className = 'small text-muted';
        label.textContent = 'Valor';
        wrapper.appendChild(label);

        if (type === 'date' && operator === 'between') {
            const row = document.createElement('div');
            row.className = 'd-flex gap-2';

            const fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.className = 'form-control form-control-sm';
            fromInput.value = Array.isArray(condition.value) ? (condition.value[0] || '') : '';
            fromInput.addEventListener('change', function () {
                const current = Array.isArray(condition.value) ? condition.value : ['', ''];
                current[0] = fromInput.value;
                condition.value = current;
                syncSegmentInput();
            });

            const toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.className = 'form-control form-control-sm';
            toInput.value = Array.isArray(condition.value) ? (condition.value[1] || '') : '';
            toInput.addEventListener('change', function () {
                const current = Array.isArray(condition.value) ? condition.value : ['', ''];
                current[1] = toInput.value;
                condition.value = current;
                syncSegmentInput();
            });

            row.appendChild(fromInput);
            row.appendChild(toInput);
            wrapper.appendChild(row);
            return;
        }

        if (operator === 'in') {
            const select = document.createElement('select');
            select.className = 'form-control form-control-sm';
            select.multiple = true;
            select.size = 4;

            const values = Array.isArray(condition.value) ? condition.value.map(String) : [];
            const options = Array.isArray(fieldConfig.options) ? fieldConfig.options : [];
            options.forEach(function (item) {
                const option = document.createElement('option');
                option.value = String(item.value);
                option.textContent = item.label;
                option.selected = values.includes(String(item.value));
                select.appendChild(option);
            });

            select.addEventListener('change', function () {
                condition.value = Array.from(select.selectedOptions).map(function (option) {
                    return option.value;
                });
                syncSegmentInput();
            });

            wrapper.appendChild(select);
            return;
        }

        if (type === 'select' || type === 'boolean') {
            const select = document.createElement('select');
            select.className = 'form-control form-control-sm';
            const options = Array.isArray(fieldConfig.options) ? fieldConfig.options : [];

            options.forEach(function (item) {
                const option = document.createElement('option');
                option.value = String(item.value);
                option.textContent = item.label;
                if (String(condition.value) === String(item.value)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            if (type === 'select' && (condition.value === '' || condition.value === null || condition.value === undefined) && options.length > 0) {
                condition.value = String(options[0].value);
                select.value = condition.value;
                syncSegmentInput();
            }

            if (type === 'boolean') {
                select.value = String(Boolean(condition.value));
            }

            select.addEventListener('change', function () {
                if (type === 'boolean') {
                    condition.value = select.value === 'true';
                } else {
                    condition.value = select.value;
                }
                syncSegmentInput();
            });

            wrapper.appendChild(select);
            return;
        }

        if (type === 'date') {
            const input = document.createElement('input');
            input.type = 'date';
            input.className = 'form-control form-control-sm';
            input.value = typeof condition.value === 'string' ? condition.value : '';
            input.addEventListener('change', function () {
                condition.value = input.value;
                syncSegmentInput();
            });
            wrapper.appendChild(input);
            return;
        }

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.placeholder = 'Escribe el valor...';
        input.value = typeof condition.value === 'string' ? condition.value : '';
        input.addEventListener('input', function () {
            condition.value = input.value;
            syncSegmentInput();
        });
        wrapper.appendChild(input);
    }

    function syncSegmentInput() {
        segmentInput.value = JSON.stringify({
            conditions: segmentState.conditions || [],
        });
    }

    function previewSegment() {
        previewBtn.disabled = true;
        previewFeedback.innerHTML = '<div class="alert alert-info mb-0">Calculando segmento...</div>';

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                segment_json: segmentInput.value,
            }),
        })
            .then(async function (response) {
                const payload = await response.json();
                if (!response.ok) {
                    throw payload;
                }
                return payload;
            })
            .then(function (payload) {
                previewCounter.textContent = (payload.count || 0) + ' contacto(s)';
                sqlPreview.textContent = payload.sql || '-- Sin SQL --';
                renderSample(payload.sample || []);
                previewFeedback.innerHTML = '<div class="alert alert-success mb-0">Previsualización actualizada correctamente.</div>';
            })
            .catch(function (errorPayload) {
                previewCounter.textContent = 'Error';
                sqlPreview.textContent = '-- No se pudo generar la consulta --';
                renderSample([]);

                let message = 'No se pudo previsualizar el segmento.';
                if (errorPayload && errorPayload.errors) {
                    const firstKey = Object.keys(errorPayload.errors)[0];
                    if (firstKey && Array.isArray(errorPayload.errors[firstKey]) && errorPayload.errors[firstKey][0]) {
                        message = errorPayload.errors[firstKey][0];
                    }
                }

                previewFeedback.innerHTML = '<div class="alert alert-danger mb-0">' + escapeHtml(message) + '</div>';
            })
            .finally(function () {
                previewBtn.disabled = false;
            });
    }

    function renderSample(sample) {
        sampleBody.innerHTML = '';
        if (!Array.isArray(sample) || sample.length === 0) {
            sampleBody.innerHTML = '<tr><td colspan="8" class="text-muted">No hay resultados con estas condiciones.</td></tr>';
            return;
        }

        sample.forEach(function (row) {
            const tr = document.createElement('tr');
            tr.innerHTML = '' +
                '<td>' + escapeHtml(row.id ?? '') + '</td>' +
                '<td>' + escapeHtml(row.name ?? '') + '</td>' +
                '<td>' + escapeHtml(row.business ?? '') + '</td>' +
                '<td>' + escapeHtml(row.phone ?? '') + '</td>' +
                '<td>' + escapeHtml(row.email ?? '') + '</td>' +
                '<td>' + escapeHtml(row.city ?? '') + '</td>' +
                '<td>' + escapeHtml(row.country ?? '') + '</td>' +
                '<td>' + escapeHtml(row.created_at ?? '') + '</td>';
            sampleBody.appendChild(tr);
        });
    }

    function escapeHtml(value) {
        const text = value === null || value === undefined ? '' : String(value);
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush
