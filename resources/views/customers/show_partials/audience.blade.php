
  <div>
    <input type="submit" value="Asignar una audiencia" data-toggle="modal" data-target="#customer" class="btn btn-primary btn-sm" style="margin-top:5px;" size="3">
    <form action="/customers/{{$model->id}}/audience" method="POST">
      {{ csrf_field() }}
      <div class="modal" id="customer">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">¿A que audiencia desea asignar al lead?</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <br>
              <label for="">Audiencias:</label>
              <input type="text" id="customer_id" name="customer_id" value="{{$model->id}}" hidden>
              <select name="audience_id" id="audience_id" class="form-control">
                @foreach($audiences as $item)
                <option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
              </select>

              <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>

              <input type="submit" value="Asignar" class="btn btn-primary " size="7">
            </div>

          </div>
        </div>
      </div>
    </form>
  </div>