  <!-- Button to Open the Modal -->
  <button type="button" class="btn btn-light" data-toggle="modal" data-target="#saleModal"><i class="fa fa-money"></i> Venta</button>

  <!-- The Modal -->
  <div class="modal" id="saleModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Agregar venta</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

       <form action="/customers/{{$customer->id}}/action/sale" method="POST">

          <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
          {{ csrf_field() }}
 
          <!-- Modal body -->
          <div class="modal-body">
            <div class="form-group">
              <label for="sale_date">Fecha de la venta</label>
              <input type="date" name="sale_date" id="sale_date" class="form-control">
            </div>

          <div class="form-group">
            <label for="users_id">Asignación de la venta</label>
              <select name="users_id" id="users_id">
                <option value="92">Estefanía Vélez</option>
                <option value="74">Daniel Marín</option>
                <option value="60">Yeimi Alexandra garcia</option>
                <option value="93">Laura Abad </option>
                <option value="88">Angie Soto </option>
                <option value="95">FARID GONZALES </option>
                <option value="11">Francisco</option>
                <option value="37">Martín</option>
                
                
                
              </select>

          </div>


            <div class="form-group">
              <label for="sale_amount">Valor</label>
              <input type="number" name="sale_amount" id="sale_amount" class="form-control">
            </div>
            <div class="form-group">
              <label for="machine">Es una máquina</label>
              <input type="checkbox" name="machine" checked="">
            </div>
          </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
       </form>

      </div>
    </div>
  </div>

