
<div>
    <form action="/reports/weeks_team" method="GET">
    	

      <div class="row">
      	<div class="col">
      		<select name="filter" class="custom-select" id="filter" onchange="update()">
	        <option value="">select time</option>

	        <option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>this month</option>
	        <option value="currentyear" @if ($request->filter == "currentyear") selected="selected" @endif>this year</option>
	        
	        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>last month</option>
	      	
	      	<option value="-30" @if ($request->filter == "-30") selected="selected" @endif>last 30 days</option>
	      	<option value="-60" @if ($request->filter == "-60") selected="selected" @endif>last 60 days</option>
        	<option value="-90" @if ($request->filter == "-90") selected="selected" @endif>last 90 days</option>
        
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
    </form>
</div>
