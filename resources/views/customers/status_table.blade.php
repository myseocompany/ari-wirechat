<div id="statusSidebar">
    
    <span id="closeStatusSidebar">✖</span>
            
    <div class="box2">
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-800 text-white">
            <tr>
                <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide">Estado</th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide">Descripción</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
            @foreach ($statuses_options as $item)
                <tr>
                    <th scope="row" class="px-3 py-2 font-semibold text-slate-900">{{$item->name}}</th>
                    <td scope="row" class="px-3 py-2">{{$item->description}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
      
</div>

<style>
    /* Estilos del statusSidebar */
    #statusSidebar {
        position: fixed;
        top: 0;
        right: -300px;
        width: 300px;
        height: 100%;
        background: #f8f9fa;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
        transition: right 0.3s ease-in-out;
        overflow-y: auto;
        padding: 20px;
        z-index: 100000;
    }
    #statusSidebar.active {
        right: 0;
        color:black!important;
    }
    #closeStatusSidebar {
        cursor: pointer;
        color: red;
        font-size: 18px;
        font-weight: bold;
        float: right;
    }
    .box {
        display: none;
    }
</style>

<script>

</script>
