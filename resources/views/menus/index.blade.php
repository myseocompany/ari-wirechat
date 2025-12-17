@extends('layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Menús</h1>
            <small class="text-muted">Administra los accesos visibles en la navegación.</small>
        </div>
        <a href="{{ route('menus.create') }}" class="btn btn-primary btn-sm">Nuevo menú</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>URL</th>
                    <th>Padre</th>
                    <th>Orden</th>
                    <th>Tipo</th>
                    <th>Submenús</th>
                    <th class="text-center" style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr>
                        <td>{{ $menu->name }}</td>
                        <td class="text-monospace">{{ $menu->url ?? '-' }}</td>
                        <td>{{ $menu->parent?->name ?? 'Raíz' }}</td>
                        <td>{{ $menu->weight ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $menu->inner_link ? 'info' : 'secondary' }}">
                                {{ $menu->inner_link ? 'Interno' : 'Externo' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-light">
                                {{ $menu->children_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este menú?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No hay menús configurados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Organizar menús</strong>
                <p class="mb-0 text-muted small">Arrastra los elementos para cambiar su orden o jerarquía.</p>
            </div>
            <button id="menu-save-order" class="btn btn-sm btn-success" disabled>Guardar orden</button>
        </div>
        <div class="card-body">
            @if (count($menuTree))
                <div class="alert alert-secondary py-2">
                    <small class="mb-0 d-block">Tips: arrastra un ítem encima de otro para convertirlo en submenú. Los cambios no se guardan hasta presionar “Guardar orden”.</small>
                </div>
                <ul class="list-group menu-sortable" id="menu-sortable-root">
                    @include('menus.partials.tree', ['nodes' => $menuTree])
                </ul>
                <div id="menu-reorder-feedback" class="mt-3"></div>
            @else
                <p class="text-muted mb-0">Aún no hay menús que reorganizar.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .menu-sortable .menu-node {
        cursor: move;
        margin-bottom: 0.5rem;
    }
    .menu-sortable .menu-node .menu-node-handle {
        display: flex;
        flex-direction: column;
    }
    .menu-sortable .menu-node .fa-bars {
        cursor: move;
    }
    .menu-sortable .nested {
        margin-top: 0.75rem;
    }
    .menu-placeholder {
        border: 1px dashed #999;
        min-height: 45px;
        background: rgba(0,0,0,0.04);
    }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        const $saveButton = $('#menu-save-order');
        const $feedback = $('#menu-reorder-feedback');

        const initSortable = function() {
            $('.menu-sortable').sortable({
                connectWith: '.menu-sortable',
                placeholder: 'menu-placeholder list-group-item',
                handle: '.menu-node-handle, .fa-bars',
                items: '> li.menu-node',
                tolerance: 'pointer',
                start: function () {
                    $feedback.empty();
                },
                stop: function () {
                    $saveButton.prop('disabled', false);
                }
            }).disableSelection();
        };

        initSortable();

        function collectItems($list, parentId) {
            const items = [];

            $list.children('li.menu-node').each(function (index) {
                const $item = $(this);
                const id = $item.data('id');
                items.push({
                    id: id,
                    parent_id: parentId,
                    weight: index + 1
                });

                const $children = $item.children('ul.menu-sortable');
                if ($children.length) {
                    const childItems = collectItems($children, id);
                    Array.prototype.push.apply(items, childItems);
                }
            });

            return items;
        }

        $saveButton.on('click', function () {
            const payload = collectItems($('#menu-sortable-root'), null);
            if (!payload.length) {
                return;
            }

            $saveButton.prop('disabled', true).text('Guardando...');
            $feedback
                .removeClass('alert-success alert-danger')
                .addClass('alert alert-info')
                .text('Guardando nuevo orden...');

            $.ajax({
                url: '{{ route('menus.reorder') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    items: payload
                }
            }).done(function () {
                $feedback
                    .removeClass('alert-info alert-danger')
                    .addClass('alert alert-success')
                    .text('Orden actualizado correctamente.');
            }).fail(function (xhr) {
                $feedback
                    .removeClass('alert-info alert-success')
                    .addClass('alert alert-danger')
                    .text((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo guardar el orden.');
                $saveButton.prop('disabled', false);
            }).always(function () {
                $saveButton.text('Guardar orden');
            });
        });
    });
</script>
@endpush
