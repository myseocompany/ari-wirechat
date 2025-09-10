<div class="card">
              <div class="card-header" id="headingThree">
                <h3>
                  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseThree" id="tabSurvey" aria-expanded="true" aria-controls="collapseThree">
                    Encuesta
                  </button>
                </h3>
              </div>
              <div id="collapseThree" class="collapse" arial-labelledby="collapseThree">

                <ul class="tabs">
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Productos')">Productos</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Servicios')">Servicios</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Cproductos')">Crear de productos</a></li>
                  <li><a href="#tabSurvey" class="tab-link" onclick="openTab(event, 'Cservicios')">Crear de servicios</a></li>
                </ul>

                <div id="Productos" class="tab-content">

                  <div class="table">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Preguntas</th>
                          <th>1</th>
                          <th>2</th>
                          <th>3</th>
                          <th>4</th>
                          <th>5</th>
                          <th>6</th>
                          <th>7</th>
                          <th>8</th>
                          <th>9</th>
                          <th>10</th>
                        </tr>
                      </thead>
                      <tbody>

                        @php
                        $last_date = "";

                        @endphp


                        @foreach($metas as $item)
                        @if($item->parent_id == 1)
                        @if($item->created_at != $last_date)
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

                          @if($item->type_id == 1)
                          <th>{{$item->name}}</th>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 1) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 2) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 3) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 4) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 5) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 6) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 7) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 8) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 9) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 10) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                        </tr>
                        @elseif($item->type_id == 4)
                        <tr>
                          <th>{{$item->name}}</th>

                          <td colspan="4">
                            {{$item->value}}
                          </td>
                        </tr>

                        @endif


                        @endif
                        @endforeach


                      </tbody>


                    </table>

                  </div>

                </div>

                <div id="Servicios" class="tab-content">

                  <div class="table">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Preguntas</th>
                          <th>1</th>
                          <th>2</th>
                          <th>3</th>
                          <th>4</th>
                          <th>5</th>
                          <th>6</th>
                          <th>7</th>
                          <th>8</th>
                          <th>9</th>
                          <th>10</th>
                        </tr>
                      </thead>
                      <tbody>

                        @php
                        $last_date = "";

                        @endphp


                        @foreach($metas as $item)
                        @if($item->parent_id == 8)
                        @if($item->created_at != $last_date)
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

                          @if($item->type_id == 1)
                          <th>{{$item->name}}</th>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 1) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 2) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 3) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 4) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 5) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="5" <?php if ($item->value == 6) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="1" <?php if ($item->value == 7) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="2" <?php if ($item->value == 8) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="3" <?php if ($item->value == 9) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                          <td><input type="radio" disabled value="4" <?php if ($item->value == 10) {
                                                                        echo 'checked';
                                                                      } ?>></td>
                        </tr>
                        @elseif($item->type_id == 4)
                        <tr>
                          <th>{{$item->name}}</th>

                          <td colspan="4">
                            {{$item->value}}
                          </td>
                        </tr>

                        @endif


                        @endif
                        @endforeach


                      </tbody>


                    </table>

                  </div>


                </div>

                <div id="Cproductos" class="tab-content">

                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10">

                      <form action="/metadata/{{$model->id}}/store" method="POST" style="margin-left:10px; margin-right:10px;">
                        {{ csrf_field() }}


                        <div class="table">
                          <table class="table table-striped">
                            <thead>

                              <tr>
                                <th></th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                              </tr>
                            </thead>
                            <tbody>

                              @foreach($meta_data as $item)
                              @if($item->parent_id ==1)

                              @if($item->type_id == 1)
                              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">

                              <tr>

                                <th>{{$item->value}}</th>
                                <td><input type="radio" name="meta_{{$item->id}}" value="1"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="2"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="3"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="4"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="5"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="6"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="7"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="8"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="9"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="10"></td>
                              </tr>

                              @elseif($item->type_id == 4)
                              <tr> <br>
                                <th> {{$item->value}}</th>
                                <td colspan="10">
                                  <textarea name="meta_{{$item->id}}" id="meta_{{$item->id}}" rows="5" style="width:100%" placeholder="{{$item->value}}"></textarea>
                                </td>
                              </tr>
                              @endif



                              @endif
                              @endforeach
                              <tr>
                                <th>Audiencia</th>


                                <td colspan="10">
                                  <select name="audience_id" id="audience_id" class="form-control">
                                    @foreach($audiences as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <td colspan="10" class="td_submit"><input type="submit" value="Enviar" class="btn btn-primary" size="7"> </td>
                              </tr>

                            </tbody>

                          </table>
                        </div>
                      </form>
                    </div>
                  </div>

                </div>

                <div id="Cservicios" class="tab-content">
                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10">

                      <form action="/metadata/{{$model->id}}/store" method="POST" style="margin-left:10px; margin-right:10px;">
                        {{ csrf_field() }}


                        <div class="table">
                          <table class="table table-striped">
                            <thead>

                              <tr>
                                <th></th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                              </tr>
                            </thead>
                            <tbody>

                              @foreach($meta_data as $item)
                              @if($item->parent_id ==8)

                              @if($item->type_id == 1)
                              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
                              <tr>

                                <th>{{$item->value}}</th>
                                <td><input type="radio" name="meta_{{$item->id}}" value="1"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="2"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="3"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="4"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="5"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="6"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="7"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="8"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="9"></td>
                                <td><input type="radio" name="meta_{{$item->id}}" value="10"></td>
                              </tr>

                              @elseif($item->type_id == 4)
                              <tr> <br>
                                <th> {{$item->value}}</th>
                                <td colspan="10">
                                  <textarea name="meta_{{$item->id}}" id="meta_{{$item->id}}" rows="5" style="width:100%" placeholder="{{$item->value}}"></textarea>
                                </td>
                              </tr>
                              @endif

                              @endif
                              @endforeach

                              <tr>
                                <th>Audiencia</th>


                                <td colspan="10">
                                  <select name="audience_id" id="audience_id" class="form-control">
                                    <option value=" ">Seleccionar...</option>
                                    @foreach($audiences as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <td colspan="10"></td>
                                <td> <input type="submit" value="Enviar" class="btn btn-primary" size="7"> </td>
                              </tr>

                            </tbody>

                          </table>
                        </div>
                      </form>
                    </div>
                  </div>

                </div>
              </div>