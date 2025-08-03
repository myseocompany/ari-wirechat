 <form action="/customers/{{$customer->id}}/action/poorly_rated" method="POST" id="form_pr"> 
    <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
    <input type="hidden" name="type_id" value="28">
    {{ csrf_field() }}
    <button type="submit" class="btn btn-light" id="submit_pr">
      <i class="fa fa-times"></i> Mal Calificado
    </button>
      </form> 

 
  <style>
    #form_pr{display:inline;}
  </style>