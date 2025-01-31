<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\TaskStatus;
use Carbon\Carbon;
use App\Models\CustomerStatus;
use App\Models\ActionType;
use App\Models\Action;
use App\Models\Customer;
use App\Models\ViewCustomerHasActions;


class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    function users(Request $request){
       
       //Esto lo cambio Amed
        $statuses = CustomerStatus::orderBy("weight", "ASC")->get(); 
        $actions = ActionType::all(); 
        $users = User::where('status_id',1)
            ->where('role_id', 2)
            ->where('role_id', 10)
            ->get();

        return view('reports.users', compact('statuses','actions' ,'users', 'request'));
    }


    function getStartAndEndDate($week, $year)
    {

        $time = strtotime("1 January $year", time());
        $day = date('w', $time);
        $time += ((7*$week)-$day)*24*3600;
        $return[0] = date('Y-m-d', $time);
        $time += 6*24*3600;
        $return[1] = date('Y-m-d', $time);
        //dd($return);
        return $return;
    }
    public function index2()
    {
        ///, week(updated_at) as week esto lo cambio Amed
        $tasks = \DB::table('tasks')
                     ->select(DB::raw('week(due_date) as week ,  count(*) as pr'))
                     ->where('status_id', '<>', 2)
                     ->where('status_id', '<>', 10)
                     ->groupBy('week')
                     ->get();


        $data = \Lava::DataTable();


        $data
            ->addDateColumn('Year')
            ->addNumberColumn('Points');
        
        
        foreach($tasks as $item){
           $data->addRow([
                $this->getStartAndEndDate($item->week, 2017)[0], intval($item->pr)
            ]);
        }
                   
                   

        \Lava::AreaChart('data', $data, [
            'title' => 'Data Growth',
            'legend' => [
                'position' => 'in'
            ]
        ]);


        return view('reports.index');
    }

    public function index(){
            $model = \DB::table('tasks')
                     ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                     ->where('status_id', '=', 3)
                     ->groupBy('year', 'week')
                     ->get();
            $users = Customer::
                     select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                     ->where('status_id', '=', 3)
                     ->groupBy('year', 'week','user_id')
                     ->get();
        foreach ($users as $item){
            
            $item->name = User::getName($item->user_id);
        }

        return view('reports.index', compact('model','users'));
    
    }
    public function weeksByTeam(Request $request){
        $model = \DB::table('tasks')
                 ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                 ->where(function ($query) use ($request){
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);
                 
                 })
                 ->where(function ($query) use ($request){
                    if(isset($request->project_id))   
                        $query = $query->where('tasks.project_id', "=", $request->project_id);
                    if(isset($request->user_id) && ($request->user_id != null)) {  
                        $query = $query->where('tasks.user_id', "=", $request->user_id);

                    }
                    
                 })
                 ->groupBy('year', 'week', 'user_id')
                 ->orderBy('year', 'desc')
                 ->orderBy('week', 'desc')
                 ->get();
        $graph = Customer::all();
        if(isset($request->user_id)){
            $graph = \DB::table('tasks')
                 ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                 ->where(function ($query) use ($request){
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);
                 
                 })
                 ->where(function ($query) use ($request){
                    if(isset($request->project_id))   
                        $query = $query->where('tasks.project_id', "=", $request->project_id);
                    if(isset($request->user_id) && ($request->user_id != null)) {  
                        $query = $query->where('tasks.user_id', "=", $request->user_id);
                    }
                    
                 })
                 ->groupBy('year', 'week', 'user_id')
                 ->get();
        }elseif(isset($request->project_id)){
            $graph = \DB::table('tasks')
                 ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                 ->where(function ($query) use ($request){
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);
                 
                 })
                 ->where(function ($query) use ($request){
                    if(isset($request->project_id))   
                        $query = $query->where('tasks.project_id', "=", $request->project_id);
                    if(isset($request->user_id) && ($request->user_id != null)) {  
                        $query = $query->where('tasks.user_id', "=", $request->user_id);
                    }
                    
                 })
                 ->groupBy('year', 'week', 'project_id')
                 ->get();
        }else{
            $graph = \DB::table('tasks')
                 ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                 ->where(function ($query) use ($request){
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);
                 
                 })
                 ->where(function ($query) use ($request){
                    if(isset($request->project_id))   
                        $query = $query->where('tasks.project_id', "=", $request->project_id);
                    if(isset($request->user_id) && ($request->user_id != null)) {  
                        $query = $query->where('tasks.user_id', "=", $request->user_id);
                    }
                    
                 })
                 ->groupBy('year', 'week')
                 ->get();
        }
            
        $controller = $this;
        $user_id = "";
        if(isset($request->user_id))
            $user_id = $request->user_id;

        return view('reports.weeks_by_team', compact('model', 'graph','controller', 'user_id'));
    
    }

    public function weeksByUser(){
        $model = Customer::
                     select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                     ->where(function ($query){
                        $query = $query->orwhere('status_id', '=', 6);
                        $query = $query->orwhere('status_id', '=', 56);
                        $query = $query->orwhere('status_id', '=', 3);
                     
                    })
                     ->groupBy('year', 'week','user_id')
                     ->orderBy('year', 'desc')
                    ->orderBy('week', 'desc')
                 
                     ->get();
        foreach ($model as $item){
            
            $item->name = User::getName($item->user_id);
        }
        $graph = $model;
            
        $controller = $this;

        return view('reports.weeks_by_user', compact('model', 'graph','controller'));
    
    }


    public function userCustomerStatus(Request $request){
        // obtengo los usuarios activos
        $dates_array = $this->getDates($request);

        $users = User::where('status_id', '=', 1)
                    //->where('role_id', 1)
                    ->where('include_reports', 1)
                    ->get();
        
                ;
        
        $data = array();
        $customer_statuses = CustomerStatus::where('stage_id',1)->orderBy('weight', 'ASC')->get();


        $date_at = $request->created_updated === "updated" ? 'customers.updated_at' : 'customers.created_at';

        

        foreach ($users as $user){
            $user_data = array();

            foreach($customer_statuses as $status){
                $model = DB::table('customers')
                     ->where(function($query) use ($request, $dates_array, $date_at){
                        if(isset($request->from_date) && $request->from_date != "") {
                            $query->whereBetween($date_at, $dates_array);
                        }
                     })
                     ->where('user_id', $user->id)
                     ->where('status_id', $status->id)
                     ->count('customers.id');
                    $user_data[] = $model;
                    //dd($from_date);
            }
            
        
            $data[] = $user_data;

        }
            
        $controller = $this;

        
        return view('reports.user_status', compact( 
            'controller', 'request', 'users' , 'data', 'customer_statuses'));
    
    }

    public function getTimeArray($dates_array){

        $from = $dates_array[0];
        $to = $dates_array[1];
        $from  = Carbon::createFromFormat('Y-m-d', $from);
        $to  = Carbon::createFromFormat('Y-m-d H:i:s', $to);

        $condition = "";
        if($from->diffInMonths($to) > 0 ){
            $condition = "months";
            $span = $from->diffInMonths($to);
        }else if($from->diffInWeeks($to) > 0 ){
            $condition = "weeks";
            $span = $from->diffInWeeks($to);
        }else if($from->diffInDays($to) > 0){
            $condition = "days";
            $span = $from->diffInDays($to);
        }
        //dd($span);

        $time_array = array();
        
        for( $i=0; $i<$span; $i++ ){
            if($condition == "months"){
               $time_array[] = Array($from->format('Y-m-d'), $from->addMonths(1)->format('Y-m-d')); 
            }else if($condition == "weeks"){
                $time_array[] = Array($from->format('Y-m-d'), $from->addWeeks(1)->format('Y-m-d'));
            }else if($condition == "days"){
                $time_array[] = Array($from->format('Y-m-d'), $from->addDays(1)->format('Y-m-d'));
            }
            
        }
        return $time_array;       
    }
    

    public function getUsersFromDates($date_array){
        $users_id = Action::distinct()->select('creator_user_id')
            ->whereBetween('created_at', $date_array)
            ->whereNotNull('creator_user_id')
            ->get();


        $users_id = $this->elocuentToArray2($users_id);


        $users = User::where('status_id', '=', 1)
                    ->whereIn('id', $users_id)
                     ->get();
        return $users;
    }
    public function customersTime(Request $request){
        // obtengo los usuarios activos
        $dates_array = $this->getDatesMontly($request);



        $customer_statuses = Customer::distinct()->select('status_id')
            ->whereBetween('created_at', $dates_array)
            ->get();



        $time_array = $this->getTimeArray($dates_array);

        //dd($customer_statuses);

        $customer_statuses = $this->elocuentToArrayStatus($customer_statuses);
        
        $users = $this->getUsersFromDates($dates_array);
 
        $data = array();
        $customer_statuses = CustomerStatus::where('stage_id',1)->orderBy('weight', 'ASC')->get();
        
        //dd($time_array);
        foreach ($time_array as $time){
            $user_data = array();

            foreach($customer_statuses as $status){
                $model = DB::table('customers')
                     ->whereBetween('created_at', $time)
                     ->where('status_id', $status->id)
                     ->count('id');
                    $user_data[] = $model;
                    
            }
            
        
            $data[] = $user_data;

        }

      
        
            
        $controller = $this;

        return view('reports.customers_time', compact( 
            'controller', 'request', 'users', 'time_array' , 'data', 'customer_statuses'));
    
    }

    function elocuentToArray($model){
        $array = Array();
        foreach ($model as $item) {
            $array[] = $item->type_id;

        }
        return $array;
    }

    function elocuentToArray2($model){
        $array = Array();
        foreach ($model as $item) {
            $array[] = $item->creator_user_id;

        }
        return $array;
    }

    function elocuentToArrayStatus($model){
        $array = Array();
        foreach ($model as $item) {
            $array[] = $item->status_id;

        }
        return $array;
    }

    public function getDates($request){
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(7);


        if(isset($request->from_date) && ($request->from_date!=null)){
            $to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date." 00:00:00");
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date." 00:00:00");
        }

        $date_array = 
            Array($from_date->format('Y-m-d'), $to_date->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s'));
        return $date_array;
    }

    public function getDatesMontly($request){
        $to_date = Carbon::today()->subMonths(0); // ayer
        $from_date = Carbon::today()->subMonths(3);


        if(isset($request->from_date) && ($request->from_date!=null)){
            $to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date." 00:00:00");
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date." 00:00:00");
        }

        $date_array = 
            Array($from_date->format('Y-m-d'), $to_date->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s'));
        return $date_array;
    }


    public function userCustomerActions(Request $request){
        // obtengo los usuarios activos
        $date_array = $this->getDates($request);

        $users_id = Action::distinct()->select('creator_user_id')
            ->whereBetween('created_at', $date_array)
            ->whereNotNull('creator_user_id')
            ->get();
        $users_id = $this->elocuentToArray2($users_id);

        if(isset($request->user_id)){
            $users = User::where('status_id', '=', 1)
                    ->where('id', $request->user_id)
                     ->get();
        }else{
            $users = User::where('status_id', '=', 1)
                    ->whereIn('id', $users_id)
                     ->get();
        }


        $data = array();

        $types_id = Action::distinct()->select('type_id')
            ->whereBetween('created_at', $date_array)
            ->whereNotNull('creator_user_id')
            ->get();

        if(isset($request->action_types_id)){
            $types_id = $this->elocuentToArray($types_id);
            $action_types = ActionType::where('id', $request->action_types_id)
            ->get();
        }else{
            $action_types = ActionType::whereIn('id', $types_id)
            ->get();
        }
        
        $actions_types = ActionType::whereIn('id', $types_id)
            ->get();
        
        
        
        foreach ($users as $user){
            $user_data = array();

            foreach($action_types as $type){
                $model = DB::table('actions')
                     ->whereBetween('created_at', $date_array)
                     ->where('creator_user_id', $user->id)
                     ->where('type_id', $type->id)
                     ->count('id');
                    $user_data[] = $model;
                    //dd($from_date);
            }
            
        
            $data[] = $user_data;

        }

        $controller = $this;

        return view('reports.user_action', compact( 
            'controller', 'request', 'users' , 'data', 'date_array', 'action_types'));
    
    }


    public function daysByUser(Request $request){
        // obtengo los usuarios activos
        $users = User::where('status_id', '=', 1)
                    ->whereIn('role_id', [1,2 ])
                     ->get();
        $days = 1;
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(1);


        if(isset($request->from_date) && ($request->from_date!=null)){
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date);
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date." 00:00:00");
        }

        
        $days = $from_date->diffInDays($to_date);

        if($days < 1){
            $days = 1;
        }
        
        $days_array = array();
        
        for( $i=0; $i<$days; $i++ ){
            $from = $from_date;
            $from->addDays(1);    

            $days_array[] = Array(
                $from->format('Y-m-d'), 
                $from->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s'));
        }

        $status_array = Array(3, 6, 56, 57);
        $data = array();

        
        foreach ($users as $user){
            $user_data = array();

            for($i=0; $i<$days; $i++ ){
                $tasks = DB::table('tasks')
                     ->whereIn('status_id', $status_array)
                     ->whereBetween('due_date', $days_array[$i])
                     ->where('user_id', $user->id)
                     ->sum('points');
                $user_data[] = $tasks;
            }
            $data[] = $user_data;

        }

        //dd($weeks_array[0]);
        
            
        $controller = $this;

        return view('reports.days_by_user', compact( 
            'controller', 'request', 'users', 'days' ,'from_date', 'to_date', 'data', 'days_array'));
    
    }

    //Manuel 2018-10-31
    public function projectsCustomerByStatuses(Request $request){
        $user_id = 1;
        if(isset($request->user_id)){
            $user_id = $request->user_id;
            
        }
        $projects = Project::where("status_id", "=", "3")->orderBy("name", "asc")->get();
        $task_statuses = CustomerStatus::all();
        $users = User::all();
        $model = Customer::
                     select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                     ->where('status_id', '=', 3)
                     ->groupBy('year', 'week','user_id')
                     ->orderBy('year', 'desc')
                    ->orderBy('week', 'desc')
                 
                     ->get();
        foreach ($model as $item){
            
            $item->name = User::getName($item->user_id);
        }
        $graph = $model;
            
        $controller = $this;

        return view('reports.projects', compact('model','request', 'graph','controller','users','projects','task_statuses'));
    
    }

    //INFORME DE SEGUIMIENTOS
    public function RFM_CustomersFollowups(){
         $model = \DB::table('view_customers_followup_by_weeks')
             ->where('year', '<>', '')
             ->whereNotNull('year')
             ->where('year', '<>', 0)
             ->orderBy('year', 'DESC')
             ->orderBy('week', 'DESC')
             ->get();
        return view('reports.views.ViewCustomersFollowups', compact('model'));
    }





    public function scrollActive(Request $request){
        if($request->ajax()){
            $model = \DB::table('view_customer_has_actions')
            ->where('id', ">" ,$request->id)
            ->whereNotNull('user_name')
            ->where(function ($query) use ($request){
                if(isset($request->user_id))   
                    $query = $query->where('user_id', "=", $request->user_id);
                    $query = $query->where('creator_user_id', "=", $request->user_id);
                if(isset($request->user_id) && ($request->user_id != null)) {  
                    $query = $query->where('user_id', "=", $request->user_id);
                    $query = $query->where('creator_user_id', "=", $request->user_id);
                }
                if(isset($request->from_date)&& ($request->from_date!=null)){
                    $query = $query->whereBetween('created_at_action_max', array($request->from_date, $request->to_date));
                }
            })
            ->orderBy('id','ASC')
            ->skip(0)->take(20)
            ->get();

            if(count($model) > 0){
                return response()->json([
                        "response" => true,
                        "model" => $model
                    ]
                );
            }
            return response()->json([
                    "response" => false
                ]   
            );
        }
        abort(403);
    }

    public function scrollInactive(Request $request){
        if($request->ajax()){
            $model = \DB::table('view_customer_0_actions')
            ->where('id', ">" ,$request->id)
            ->whereNotNull('user_name')
            ->where(function ($query) use ($request){
                if(isset($request->user_id))   
                    $query = $query->where('user_id', "=", $request->user_id);
                if(isset($request->user_id) && ($request->user_id != null)) {  
                    $query = $query->where('user_id', "=", $request->user_id);
                }
                
                if(isset($request->from_date)&& ($request->from_date!=null)){
                    $query = $query->whereBetween('created_at', array($request->from_date, $request->to_date));
                }
                
            })
            ->orderBy('id','ASC')
            ->skip(0)->take(20)
            ->get();
            if(count($model) > 0){
                return response()->json([
                        "response" => true,
                        "model" => $model
                    ]
                );
            }
            return response()->json([
                    "response" => false
                ]   
            );
        }
        abort(403);
    }

    public function scrollInactiveWithOutUser(Request $request){
        if($request->ajax()){
            $model = \DB::table('view_customer_0_actions')
            ->where('id', ">" ,$request->id)
            ->whereNull('user_name')
            ->where(function ($query) use ($request){                
                if(isset($request->from_date)&& ($request->from_date!=null)){
                    $query = $query->whereBetween('created_at', array($request->from_date, $request->to_date));
                }
            })
            ->orderBy('id','ASC')
            ->skip(0)->take(20)
            ->get();
            if(count($model) > 0){
                return response()->json([
                        "response" => true,
                        "model" => $model
                    ]
                );
            }
            return response()->json([
                    "response" => false
                ]   
            );
        }
        abort(403);
    }
    /** FIN DASHBOARD SIRENA**/


    public function getCustomerStatuses($request, $sid, $limit){
        
        $model = Customer::where("status_id", $sid)
            ->where("notes", "like", "%".$request."%")
            ->orderBy("created_at", "DESC")
            ->paginate($limit);

        return $model;
    }

    public function getCustomerHash($str){
        
        $model = Customer::where("notes", "like", "%".$str."%")
            ->orderBy("created_at", "DESC")
            ->get();

        return $model;
    }


    public function getCustomersGroupByMaker($request){
        $dates = $this->getDates($request);
        $customersGroup = Customer::leftJoin("projects", "customers.maker", "projects.id")
                ->where(
                // Búsqueda por...
                function ($query) use ($request, $dates) {
                    if (isset($request->from_date) && ($request->from_date != null)) {

                        if ( (isset($request->created_updated) &&  ($request->created_updated=="updated")) ) 
                            $query->whereBetween('customers.updated_at', $dates);
                        else
                            $query->whereBetween('customers.created_at', $dates);
                    }
                    $query->where("notes", "like",  "%" . $request->search . "%");
                    
                }
            )
            
            ->select(DB::raw('projects.name as project_name, projects.color as project_color, count(customers.id) as count'))
            ->groupBy('projects.name')
            ->groupBy('projects.color')
            
            ->get();

        
        
        return $customersGroup;
    }

    public function countShowUpMiamiAbrioMail2024($request) {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#MiamiAbrioMail2024%'; 
        $searchAsisteUS2024 = '%#AsisteUS2024%';
    
        $count = Customer::where('notes', 'like', $str)
                         ->where('notes', 'like', $searchAsisteUS2024)
                         ->count();
    
        return $count;
    }
    public function countShowUpMiami2024($request) {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#miami2024%'; 
        $searchAsisteUS2024 = '%#AsisteUS2024%';
    
        $count = Customer::where('notes', 'like', $str)
                         ->where('notes', 'like', $searchAsisteUS2024)
                         ->count();
    
        return $count;
    }
    public function countShowUpTipoAUS($request) {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#TipoA-US%'; 
        $searchAsisteUS2024 = '%#AsisteUS2024%';
    
        $count = Customer::where('notes', 'like', $str)
                         ->where('notes', 'like', $searchAsisteUS2024)
                         ->count();
    
        return $count;
    }
    
    

    public function getDatesInterval(&$request, $days){
        $from_date = Carbon::today()->subDays($days - 1 );
        $to_date = Carbon::today(); // ayer
        
        $request->to_date = $to_date->format('Y-m-d');
        $request->from_date = $from_date->format('Y-m-d');
    }

    public function getCustomerGroup($request){
        
        $pid = 1;
        $statuses = $this->getStatuses($request, $pid);

        $model = $this->countFilterCustomers($request, $statuses);
        
        return $model;
    }

    public function getStatuses(Request $request, $step) {
       
        if (isset($request->from_date) || ($request->from_date != ""))
            $statuses = $this->getAllStatusID();
        else
            $statuses = $this->getStatusID($request, $step);
        return $statuses;
    }

    function getAllStatusID(){

        $res = array();
        $model = CustomerStatus::orderBy("weight", "ASC")->get();


        foreach ($model as $item)
            $res[] = $item->id;
        return $res;
    }

    
    public function countFilterCustomers($request, $statuses){
        
        $dates = $this->getDates($request);

        
        $customersGroup = Customer::wherein('customers.status_id', $statuses)
            ->rightJoin("customer_statuses", 'customers.status_id', '=', 'customer_statuses.id')
            ->where(
                // Búsqueda por...

                
                function ($query) use ($request, $dates) {
                    
                    if (isset($request->from_date) && ($request->from_date != null)) {

                        if ( (isset($request->created_updated) &&  ($request->created_updated=="updated")) ) 
                            $query->whereBetween('customers.updated_at', $dates);
                        else
                            $query->whereBetween('customers.created_at', $dates);
                    }
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query->where('customers.user_id', $request->user_id);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring)  && ($request->scoring != null))
                        $query->where('customers.scoring', $request->scoring);

                    if (isset($request->project_id)  && ($request->project_id != null))
                        $query->where('project_id', $request->project_id);
                    if (isset($request->kpi)  && ($request->kpi != null))
                            $query->where('kpi', $request->kpi);
                    if (isset($request->search)  && ($request->search != null)){
                        
                        $query->where(
                            function ($innerQuery) use ($request) {
                                $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                                
                            }
                    );
                }}

            )
            ->select(DB::raw('customers.status_id as status_id, count(customers.id) as count'))
            ->groupBy('status_id')
            ->groupBy('weight')

            ->orderBy('weight', 'ASC')

            ->get();

        foreach ($customersGroup as $item) {
            $included = false;
            foreach ($statuses as $status => $value) {
                if ($value == $item->status_id) {
                    $included = true;
                }
            }
            if ($included) {
                $item->color = CustomerStatus::getColor($item->status_id);
                $item->name = CustomerStatus::getName($item->status_id);
                $item->id = $item->status_id;
            }
        }
        return $customersGroup;
    }

    function getStatusID($request, $stage_id){
        $url = $request->fullurl();
        $paramenters = explode("&", $url);
        $res = array();
        foreach ($paramenters as $key => $value) {
            if (strpos($value, "status_id") !== false && (str_replace("status_id=", "", $value) != 0)) {
                $res[] = str_replace("status_id=", "", $value);
            }
        }
        if (!count($res)) {

            $model = CustomerStatus::where("stage_id", $stage_id)
                ->orderBy("weight", "ASC")
                ->get();
            //$model = CustomerStatus::all();

            foreach ($model as $item)
                $res[] = $item->id;
        }

        return $res;
    }


    public function dashboard(Request $request){
        
        $request7 = new Request;
        $request7->search = "#TipoA";
        $this->getDatesInterval($request7, 90);
        // reporte de clientes de los ultimos 7 dias
        $model7 = $this->getCustomerGroup($request7);
        // reporte de clientes de los ultimos 30 dias
        $request30 =  new Request;
        $request30->search = "#TipoA";
        $this->getDatesInterval($request30,10000);
        $model30 = $this->getCustomerGroup($request30);


        $quote_state = 6;
        $customersQuotes20 = $this->getCustomerStatuses($request7, $quote_state,30);

        $customersByProject7 = $this->getCustomersGroupByMaker($request7);

        $customersByProject30 = $this->getCustomersGroupByMaker($request30);

        return view('reports.dashboard', compact('model7', 'model30', 'request7', 'request30', 'customersQuotes20',
            'customersByProject7', 'customersByProject30'));
    }


    public function dashboardMiami(Request $request){
        
        $request7 = new Request;
        $request7->search = "#miami2024";
        $this->getDatesInterval($request7, 90000);
        // reporte de clientes de los ultimos 7 dias
        $model7 = $this->getCustomerGroup($request7);

        //dd($request7);
        // reporte de clientes de los ultimos 30 dias
        $request30 =  new Request;
        $request30->search = "TipoA-US";
        $this->getDatesInterval($request30,10000);
        $model30 = $this->getCustomerGroup($request30);


        // reporte de clientes de los ultimos 30 dias
        $request3 =  new Request;
        $request3->search = "MiamiAbrioMail2024";
        $this->getDatesInterval($request3,10000);
        $model3 = $this->getCustomerGroup($request3);


        $quote_state = 6;
        $customersQuotes20 = $this->getCustomerHash("#AsisteUS2024");

        $customersByProject7 = $this->getCustomersGroupByMaker($request7);
        $customersByProject30 = $this->getCustomersGroupByMaker($request30);
        $customersByProject3 = $this->getCustomersGroupByMaker($request3);
        
        
        
        $countShowUp1 = $this->countShowUpMiami2024($request30);
        $countShowUp2 = $this->countShowUpTipoAUS($request7);
        $countShowUp3 = $this->countShowUpMiamiAbrioMail2024($request3);

       


        return view('reports.dashboard_miami', compact('model7', 'model30', 'model3',
        'request7', 'request30','request3', 'customersQuotes20',
            'customersByProject7', 'customersByProject30', 'customersByProject3', 
            'countShowUp1', 'countShowUp2', 'countShowUp3'));
    }

}
