@foreach ($nodes as $node)
    <li class="list-group-item menu-node" data-id="{{ $node['id'] }}">
        <div class="menu-node-content d-flex align-items-center justify-content-between">
            <div class="menu-node-handle">
                <span class="fa fa-bars text-muted mr-2" aria-hidden="true"></span>
                <strong>{{ $node['name'] }}</strong>
                <small class="text-muted d-block">{{ $node['url'] ?? 'Sin URL' }}</small>
            </div>
            <span class="badge badge-light">{{ count($node['children']) }} sub</span>
        </div>

        @if (!empty($node['children']))
            <ul class="list-group menu-sortable nested mt-2">
                @include('menus.partials.tree', ['nodes' => $node['children']])
            </ul>
        @endif
    </li>
@endforeach
