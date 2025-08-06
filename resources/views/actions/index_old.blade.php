@extends('layout')

@section('content')

<!-- Asegúrate de tener Alpine.js cargado -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<h1 class="text-2xl font-bold mb-2">Acciones</h1>
<p class="mb-4 text-gray-600">
    Mostrando del {{ $model->firstItem() }} al {{ $model->lastItem() }} de un total de {{ $model->total() }} registros.
</p>

@if (!$request->filled('filter') && !$request->filled('from_date') && !$request->filled('to_date') && !$request->filled('pending') && !$request->filled('type_id') && !$request->filled('user_id') && !$request->filled('action_search'))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="text-center cursor-pointer">
            <div class="bg-white p-4 rounded shadow">
                <h5 class="text-lg font-semibold flex items-center justify-center gap-2">
                    <span class="bg-yellow-400 text-black rounded-full w-12 h-12 flex items-center justify-center text-xl">
                        <i class="fa fa-exclamation-circle"></i>
                    </span>
                    Acciones vencidas
                </h5>
                <p class="text-gray-700 mt-2">{{ $overdueActions->total() }} acciones</p>
            </div>
        </div>
        <div class="text-center cursor-pointer">
            <div class="bg-white p-4 rounded shadow">
                <h5 class="text-lg font-semibold flex items-center justify-center gap-2">
                    <span class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl">
                        <i class="fa fa-calendar"></i>
                    </span>
                    Acciones para hoy
                </h5>
                <p class="text-gray-700 mt-2">{{ $todayActions->total() }} acciones</p>
            </div>
        </div>
        <div class="text-center cursor-pointer">
            <div class="bg-white p-4 rounded shadow">
                <h5 class="text-lg font-semibold flex items-center justify-center gap-2">
                    <span class="bg-red-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    Acciones pendientes
                </h5>
                <p class="text-gray-700 mt-2">{{ $upcomingActions->total() }} acciones</p>
            </div>
        </div>
        <div class="text-center cursor-pointer">
            <div class="bg-white p-4 rounded shadow">
                <h5 class="text-lg font-semibold flex items-center justify-center gap-2">
                    <span class="bg-black text-white rounded-full w-12 h-12 flex items-center justify-center text-xl">
                        <i class="fa fa-list"></i>
                    </span>
                    Todas las acciones
                </h5>
                <p class="text-gray-700 mt-2">{{ $model->total() }} acciones</p>
            </div>
        </div>
    </div>
@endif

@include('actions.filter')

<!-- Contenedor de secciones colapsables -->
<div class="space-y-4" x-data="{ open: 'all' }">

    @if (!$request->filled('filter') && !$request->filled('from_date') && !$request->filled('to_date') && !$request->filled('pending') && !$request->filled('type_id') && !$request->filled('user_id') && !$request->filled('action_search'))

        <!-- Acciones vencidas -->
        <div class="border rounded shadow">
            <div class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center"
                 @click="open = open === 'overdue' ? null : 'overdue'">
                <span class="text-blue-600 font-semibold">
                    <i class="fa fa-exclamation-circle text-yellow-400 mr-2"></i> Acciones vencidas
                </span>
                <i class="fa" :class="open === 'overdue' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
            <div x-show="open === 'overdue'" x-transition class="p-4 bg-white">
                @include('actions.table_data', ['model' => $overdueActions])
            </div>
        </div>

        <!-- Acciones para hoy -->
        <div class="border rounded shadow">
            <div class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center"
                 @click="open = open === 'today' ? null : 'today'">
                <span class="text-blue-600 font-semibold">
                    <i class="fa fa-calendar text-blue-600 mr-2"></i> Acciones para hoy
                </span>
                <i class="fa" :class="open === 'today' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
            <div x-show="open === 'today'" x-transition class="p-4 bg-white">
                @include('actions.table_data', ['model' => $todayActions])
            </div>
        </div>

        <!-- Acciones pendientes -->
        <div class="border rounded shadow">
            <div class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center"
                 @click="open = open === 'upcoming' ? null : 'upcoming'">
                <span class="text-blue-600 font-semibold">
                    <i class="fa fa-clock-o text-red-600 mr-2"></i> Acciones pendientes
                </span>
                <i class="fa" :class="open === 'upcoming' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
            <div x-show="open === 'upcoming'" x-transition class="p-4 bg-white">
                @include('actions.table_data', ['model' => $upcomingActions])
            </div>
        </div>
        <!-- Todas las acciones -->
      <div class="border rounded shadow">
          <div class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center"
              @click="open = open === 'all' ? null : 'all'">
              <span class="text-blue-600 font-semibold flex items-center">
                  <i class="fa fa-list text-black mr-2"></i> Todas las acciones
              </span>
              <i class="fa" :class="open === 'all' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          </div>
          <div x-show="open === 'all'" x-transition class="p-4 bg-white">
              @include('actions.table_data', ['model' => $model])
          </div>
        </div>
    @endif




@if($model instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="mt-6">
        {{ $model->appends(request()->input())->links() }}
    </div>
@endif

@endsection
