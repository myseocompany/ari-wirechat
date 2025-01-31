  <!-- Button to Open the PQR Modal -->
  <button type="button" class="btn btn-light" data-toggle="modal" data-target="#pqrModal"><i class="fa fa-exclamation-triangle"></i> PQR </button>


  <!-- The Modal -->
  <div class="modal" id="pqrModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Agregar petición</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

       <form action="/customers/{{$customer->id}}/action/pqr" method="POST">

        <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
          {{ csrf_field() }}

          <!-- Modal body -->
        <div class="modal-body">
          <div class="form-group">
            <label for="created_at">Fecha del reclamo</label>
            <input type="date" name="created_at" id="created_at" class="form-control">
          </div>
          <div class="form-group">
            <label for="note"></label>
            <textarea name="note"  id="note" cols="53" rows="5"></textarea>
          </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
       </form>

      </div>
    </div>
  </div>

