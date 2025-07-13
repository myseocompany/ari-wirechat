
Date.prototype.GetFirstDayOfWeek = function(date) {
    date = new Date(date);
    var day = date.getDay() || 7;  
    if( day !== 1 ) 
        date.setHours(-24 * (day - 1)); 
    return date;
    
}

Date.prototype.GetLastDayOfWeek = function(date) {
    date = new Date(date);
    var day = date.getDay() || 7;  
    if( day !== 1 ) 
        date.setHours(-24 * (day - 1));
    date.setDate(date.getDate() + 6); 
    return date;
}

function getThisWeek(){
  
  var firstday = new Date().GetFirstDayOfWeek(new Date());
  var lastday = new Date().GetLastDayOfWeek(new Date());
  
  $('#from_date').val(dateToString(firstday));
  $('#to_date').val(dateToString(lastday));

}


function getLastWeek(){
  var date = new Date();
  date.setDate(date.getDate()-7);  
  var firstday = new Date().GetFirstDayOfWeek(date);
  var lastday = new Date().GetLastDayOfWeek(date);
  
  $('#from_date').val(dateToString(firstday));
  $('#to_date').val(dateToString(lastday));

}

function dateToString(date){

  var dd = date.getDate();
  var mm = date.getMonth()+1; //January is 0!
  var yyyy = date.getFullYear();

  if(dd<10) {dd = '0'+dd} 

  if(mm<10) {mm = '0'+mm} 

  
  //alert(yyyy + '-' + mm + '-' + dd);
  return yyyy + '-' + mm + '-' + dd;
}

function getDate(interval){
  var date = new Date();
  date.setDate(date.getDate() + interval);

  return dateToString(date);
}


function getLastMonth(){
	var date = new Date();
	date.setDate(0);
	$('#to_date').val(dateToString(date));

	date.setDate(1);
	$('#from_date').val(dateToString(date));
  	
}

function getCurrentMonth(){

	var date = new Date();
	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
	var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

	var date = new Date();
	date.setDate(0);
	$('#to_date').val(dateToString(lastDay));

	date.setDate(1);
	$('#from_date').val(dateToString(firstDay));
  	
}

function getLastYear(){
	var date = new Date();
	var firstDay = new Date(date.getFullYear()-1, 0, 1);
	var lastDay = new Date(date.getFullYear()-1, 12, 0);

	var date = new Date();
	date.setDate(0);
	$('#to_date').val(dateToString(lastDay));

	date.setDate(1);
	$('#from_date').val(dateToString(firstDay));
  	
}

function getCurrentYear(){
	var date = new Date();
	var firstDay = new Date(date.getFullYear(), 0, 1);
	var lastDay = new Date(date.getFullYear(), 12, 0);

	var date = new Date();
	date.setDate(0);
	$('#to_date').val(dateToString(lastDay));

	date.setDate(1);
	$('#from_date').val(dateToString(firstDay));
  	
}

function update(){
    filter = $( "#filter option:selected" ).val();
    
   
    switch (filter){
      case "": $('#from_date').val(""); $('#to_date').val(""); break;
      case "0": $('#from_date').val(getDate(0)); $('#to_date').val(getDate(1)); break;
      case "-1": $('#from_date').val(getDate(-1)); $('#to_date').val(getDate(0)); break;
      case "thisweek":getThisWeek(); break;
      case "-7":$('#from_date').val(getDate(-6)); $('#to_date').val(getDate(0)); break;
      case "-30":$('#from_date').val(getDate(-30)); $('#to_date').val(getDate(-1)); break;
      case "-90":$('#from_date').val(getDate(-930)); $('#to_date').val(getDate(-1)); break;
      
      case "lastweek":getLastWeek(); break;
      case "lastmonth":getLastMonth();break;
      case "currentmonth":getCurrentMonth();break;
      case "lastyear":getLastYear();break;
      case "currentyear":getCurrentYear();break;
    }
  }
function cleanFilter(){
      filter = $( '#filter option[value=""]' ).attr('selected', 'selected');
}

function changeStatus(id){

  document.getElementById("status_id").value = id; // Esto seleccionarÃ¡ la opciÃ³n "Demo"
  console.log(id);
  $('#status_id').val(id);
  $('#filter_form').submit();

}
/*activar desactivar emails*/

function openTab(evt, tabName) {

 
  var i, tabContent, tabLinks;
  tabContent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabContent.length; i++) {
    tabContent[i].style.display = "none";
  }
  tabLinks = document.getElementsByClassName("tab-link");
  for (i = 0; i < tabLinks.length; i++) {
    tabLinks[i].className = tabLinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}