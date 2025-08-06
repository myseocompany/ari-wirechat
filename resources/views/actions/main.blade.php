<main class="flex-1 p-6 bg-gray-50 overflow-y-auto">

    <!-- h1 class="text-2xl font-bold mb-4 text-blue-600">Acciones</h1 -->

    <p class="mb-4 text-gray-600">
        Mostrando del {{ $model->firstItem() }} al {{ $model->lastItem() }} de un total de {{ $model->total() }} registros.
    </p>

    <!-- Tabla de datos -->
    @include('actions.table_data', ['model' => $model])

    <!-- Paginación -->
    @if($model instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $model->appends(request()->input())->links() }}
        </div>
    @endif

</main>
