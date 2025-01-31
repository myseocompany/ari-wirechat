
<div>
    <form action="/reports/users/customer/status" method="GET">
    	

      <div class="row">
      	<div class="col">
      		<select name="filter" class="custom-select" id="filter" onchange="update()">
	        <option value="">select time</option>

	        <option value="7" @if ($request->filter == "7") selected="selected" @endif>next 7 days</option>
	        <option value="0" @if ($request->filter == "0") selected="selected" @endif>today</option>
	        <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>this week</option>
	        <option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>this month</option>
	        
	        <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>yesterday</option>
	        <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>last week</option>
	        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>last month</option>
	      	
	      	<option value="-7" @if ($request->filter == "-7") selected="selected" @endif>last 7 days</option>
	        <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>last 30 days</option>
        
      	</select>
      	</div>
        <div class="col">
        	<input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
        </div>
        <div class="col">
          
          <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
       	</div>
       

        <div class="col">
			
			<select  name="user_id" class="slectpicker custom-select" id="user_id" onchange="submit();">
	        <option value="">Select user </option>


	        @foreach($users as $item)
	        <option value="{{$item->id}}"  @if ($item->id == $request->item_id)  selected="selected" @endif >
	        	{{$item->name}}
	        </option>
	        @endforeach
	       
	      </select>
      </div>
      <div class="col">
	     <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
       </div>
      </div>

<!----------------------------Actualizado o creado------------->
<div class="col-md-12">
          <div class="row">
            <div class="col-6">
              <label for="created">Creado</label>
              <input type="radio" name="created_updated" id="created" value="created" onchange="submit();" @if(!isset($request->created_updated) || (isset($request->created_updated)&&($request->created_updated=="created")) ) checked="checked"   @endif>
            </div>
            <div class="col-6">
              <label for="created">Actualizado</label>
              <input type="radio" name="created_updated" id="updated" value="updated" onchange="submit();" @if(isset($request->created_updated)&&($request->created_updated=="updated")) checked="checked" @endif>
            </div>
          </div>
        </div>
    </form>
</div>
