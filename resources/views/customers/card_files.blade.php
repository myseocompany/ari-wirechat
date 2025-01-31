<!-- ARCHIVOS -->
<div>
  <div class="card">
  <div class="card-header" id="headingTwo">
    <h2>
      <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
      Archivos</h2>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">

      <form method="POST" action="/customer_files" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="form-group">
          <div class="container">
            <div class="row">
              <div class="col">Seleccione el archivo</div>
              <div class="col"><input type="file" class="form-control" id="file" name="file" placeholder="email" ></div>
              <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
              <div class="col"><input type="submit" class="btn btn-sm btn-primary glyphicon glyphicon-pencil" aria-hidden="true"></div>
            </div>
          </div>


        </div>



      </form>

      <div>
        <div class="table">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>

                <th>Url</th>
                <th>Fecha de Creación</th>

                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($customer->customer_files as $file)
              <tr>
                <th>{{$file->id}}</th>

                <th><a href="/public/files/{{$file->customer_id}}/{{$file->url}}">{{$file->url}}</a></th>
                <th>{{$file->created_at}}</th>

                <th>
                  <a class="btn btn-danger btn-sm" href="/customer_files/{{$file->id}}/delete" title="Eliminar">Eliminar</a>
                </th>
              </tr>
              @endforeach                              
            </tbody>
          </table>        

        </div>
      </div>
    </div>

  </div>

</div>