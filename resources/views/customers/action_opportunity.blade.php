 <form action="/customers/{{$customer->id}}/action/opportunity" method="POST" id="form_o"> 
    <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
    <input type="hidden" name="type_id" value="28">
    {{ csrf_field() }}
    <button type="submit"class="btn btn-light" id="submit_o">
      <i class="fa fa-bell"></i> Oportunidad
    </button>
  </form> 
  <style> #form_o{display:inline;} </style>


  