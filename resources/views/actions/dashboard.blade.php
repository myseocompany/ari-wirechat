<div class="grid grid-cols-2 gap-2 mb-6">
    <button type="button" onclick="submitWithRange('overdue')" class="flex flex-col items-center justify-center p-2 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
        <span class="text-xs font-medium">Vencidas</span>
        <span class="font-bold text-lg">{{ $overdueActions->total() }}</span>
    </button>
    <button type="button" onclick="submitWithRange('today')" class="flex flex-col items-center justify-center p-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
        <span class="text-xs font-medium">Hoy</span>
        <span class="font-bold text-lg">{{ $todayActions->total() }}</span>
    </button>
    <button type="button" onclick="submitWithRange('upcoming')" class="flex flex-col items-center justify-center p-2 bg-red-100 text-red-700 rounded hover:bg-red-200">
        <span class="text-xs font-medium">Pendientes</span>
        <span class="font-bold text-lg">{{ $upcomingActions->total() }}</span>
    </button>
    <button type="button" onclick="submitWithRange('all')" class="flex flex-col items-center justify-center p-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
        <span class="text-xs font-medium">Todas</span>
        <span class="font-bold text-lg">{{ $overdueActions->total() +  $todayActions->total() + $upcomingActions->total() }}</span>
    </button>
</div>
