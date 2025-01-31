<div class="accordion">
      <div class="card">
            <div class="card-header" id="headingOne">
             <h3>
              <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
               Envío de correos
             </button>
           </h3>
         </div>
         <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
           <form action="/customers/{{$customer->id}}/action/mail" method="POST">
            {{ csrf_field() }}
            <div>
              <select name="email_id" id="email_id">
                <option value="">Seleccione una opción</option>
                @foreach($email_options as $email_option)
                <option value="{{$email_option->id}}">{{$email_option->subject}}</option>
                @endforeach
              </select>
            </div>
            <div>
             <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
             <input class="btn btn-primary btn-sm" type="submit" value="Enviar correo">
           </div>
         </form>
       </div>
    </div>
    @include('customers.card_files')

</div>