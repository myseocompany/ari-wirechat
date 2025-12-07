              <div class="card">
                <div class="card-header" id="headingfour">
                  <h3>
                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapsefour" aria-expanded="false" aria-controls="collapsefour">
                      POA
                    </button>
                  </h3>
                </div>

                <div id="collapsefour" class="collapse" aria-labelledby="headingfour">

                  @php
                  $last_date = "";

                  @endphp
                  <div class="table">
                    <table class="table table-striped">
                      @foreach($metas as $item)
                      @if($item->parent_id != 1 && $item->parent_id != 8 )
                      @if($item->created_at != $last_date)
                      <thead>
                        <th>Preguntas</th>
                        <th>Respuestas</th>
                      </thead>
                      <tbody>
                        <tr>
                          <td colspan="11">
                            <h3>{{$item->created_at}}</h3>
                          </td>
                        </tr>
                        @endif
                        @php
                        $last_date = $item->created_at;
                        @endphp


                        <tr>
                          <th>{{$item->name}}</th>
                          <td>
                            {{$item->value}}
                          </td>
                        </tr>

                      </tbody>

                      @endif
                      @endforeach
                    </table>
                  </div>
                </div>
              </div>
