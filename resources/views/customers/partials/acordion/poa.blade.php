              <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3" id="headingfour">
                  <h3 class="text-base font-semibold text-slate-900">POA</h3>
                </div>

                  @php
                  $last_date = "";

                  @endphp
                  <div class="overflow-x-auto px-4 py-3">
                    <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                      @foreach($metas as $item)
                      @if($item->parent_id != 1 && $item->parent_id != 8 )
                      @if($item->created_at != $last_date)
                      <thead class="bg-slate-50">
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Preguntas</th>
                          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Respuestas</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-200 bg-white">
                        <tr>
                          <td colspan="11" class="px-3 py-2">
                            <h3 class="text-sm font-semibold text-slate-900">{{$item->created_at}}</h3>
                          </td>
                        </tr>
                        @endif
                        @php
                        $last_date = $item->created_at;
                        @endphp


                        <tr>
                          <th class="px-3 py-2 font-semibold text-slate-900">{{$item->name}}</th>
                          <td class="px-3 py-2">
                            {{$item->value}}
                          </td>
                        </tr>

                      </tbody>

                      @endif
                      @endforeach
                    </table>
                  </div>
              </div>
