@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>

@section('content')
<h1>Importar clientes</h1>
<div class="row">
    <table class="table table-striped">
      <form name="userImporter" action="/import" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <tr>
          <td id="divUserImporter">
            <a class="nav-link btn btn-import" href="#" onclick="loadImporter()" id="btn-import">Importar</a>             
          </td>
          <td>
            <input type="file" class="form-control" name="importerFile" id="inputImporter" accept=".csv">
          </td>
          <td>
            <button type="submit" class="btn btn-create">Enviar</button>
          </td>
        </tr>
      </form>

    </table>


  </div>
@endsection