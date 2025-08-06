<aside class="w-64 bg-white border-r p-4">
    <h2 class="text-lg font-bold mb-4">Acciones</h2>

    @include('actions.filter')
</aside>

<style>
.input-date {
    max-width: 100%;
}
.custom-select, .form-control {
    max-width: 100%;
}
</style>

<script>
function submitWithRange(value) {
    document.getElementById('range_type').value = value;
    document.getElementById('filter_form').submit();
}
</script>
