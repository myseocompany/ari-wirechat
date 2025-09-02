<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon;
use App\Models\Role;
use DB;
use Auth;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\Email;
use Mail;
use App\Models\DateTime;
use App\Models\ActionType;

use App\Services\ActionService;

use App\Models\CustomerHistory;



class ActionController extends Controller
{

    public function __construct(ActionService $actionService)
    {
        $this->actionService = $actionService;

    } 
    
   public function show($id)
    {
        $model = Action::find($id);
        return view('actions.show', compact('model')); 
    }
	

  	// iud es user_id , eid es email_id 
  	public function trackEmail($cid, $eid)
    {
    	$customer = Customer::find($cid);
    	if($customer){
			if($customer->status_id==1 || $customer->status_id==18 || $customer->status_id==9){
				$customer->status_id=19;
				$customer->save();}
            $email = Email::find($eid);

	        Action::saveAction($cid, $eid, 4);

	        $subjet = 'El usuario '.$customer->name.' ha abierto el correo! '.$email->subjet;
	        $body= 'El usuario '.$customer->name.' ha abierto el correo!</br><a href="https://mqe.quirky.com.co/customers/'.$cid.'/show">https://mqe.quirky.com.co/customers/'.$cid.'/show</a>';
	        $user = User::find(10);
            $this->sendTrackEmail($user, $customer);

            $user = User::find(11);
            $this->sendTrackEmail($user, $customer);
        }
    }

    public function sendTrackEmail($user, $customer){

        $subject = 'El usuario '.$customer->name.' ha abierto el correo!';
        $view = 'emails.trackEmail';
        $emailcontent = array (
            'name' => $customer->name,
            'cid' => $customer->id,
          
        );

        Mail::send($view, $emailcontent, function ($message) use ($user, $subject){
                $message->subject($subject);
                $message->to($user->email);
            });   
    }

    public function sendMail($subjet,$body, $user) {

		$send = Email::raw($body, function ($message) use ($user, $subjet){
		        
	        $message->from('noresponder@mqe.com.co', 'Maquiempanadas');

	        $message->to($user->email, $user->name)->subject($subjet);  
	        return "mailed"; 

	    });
	}
    public function index( Request $request){
    $forcePending = $request->input('pending') === 'true';

    $overdueRequest  = $this->actionService->createFilteredRequest($request, 'overdue',  $forcePending);
    $todayRequest    = $this->actionService->createFilteredRequest($request, 'today',    $forcePending);
    $upcomingRequest = $this->actionService->createFilteredRequest($request, 'upcoming', $forcePending);

    $overdueActions  = $this->actionService->filterModel($overdueRequest);
    $todayActions    = $this->actionService->filterModel($todayRequest);
    $upcomingActions = $this->actionService->filterModel($upcomingRequest);

    // Listado principal (respeta from/to del request actual)
    $model = $this->actionService->filterModel($request);

    // “Todas” debe ser exactamente el mismo set que el listado cuando range_type=all
    $allRequest = clone $request;
    $allRequest->merge(['range_type' => 'all']);
    $totalFilteredActions = $this->actionService->countAllMatching($allRequest);

        $users = User::where('status_id' , '=' , 1)->get();
        $action_options = ActionType::orderby("weigth", "DESC")->get();
        $statuses_options = CustomerStatus::orderBy("stage_id", "ASC")->orderBy("weight", "ASC")->get();
        
        

    return view('actions.index', compact(
        'model','users','action_options','request',
        'overdueActions','todayActions','upcomingActions',
        'statuses_options','totalFilteredActions'
    ));
    }   

    public function indexPending( Request $request){
        /*BUSCAR LOS QUE TIENEN ACCIONES*/
         $customers_id = Action::
                        rightJoin('customers', 'actions.customer_id', 'customers.id')
                        ->join('action_types', 'action_types.id', 'actions.type_id')
                        ->where('action_types.outbound', 1)
                        ->select(DB::raw('DISTINCT(customers.id)')) 
                        ->groupBy('customers.id')         
                        ->get();    

        $array_customer_id = array();
        foreach ($customers_id as $key => $value) {
            $array_customer_id[] = $value["id"];
        }
        $year = date("Y");
        $month = date("n");



        $model = Customer::
                        whereNotIn('id', $array_customer_id)
                        ->whereYear('created_at' ,$year) 
                        ->whereMonth('created_at' ,$month) 
                        ->orderBy('created_at', 'DESC')
                        ->get();
        $total_pending_action = $this->getPendingActions();

        $users = User::where('status_id' , '=' , 1)
            ->get();

            //dd($model);
        $action_options = ActionType::orderBy('weigth')->get();   



        //CLientes con acciones de Salida
        $customers_whith_actions = Customer::
                        leftjoin('actions', 'actions.customer_id', 'customers.id')
                        ->join('action_types', 'action_types.id', 'actions.type_id')
                        ->where('action_types.outbound', 1)
                        ->whereYear('actions.created_at' ,$year) 
                        ->whereMonth('actions.created_at' ,$month) 
                        ->select(DB::raw('DISTINCT(customers.id), customers.name, customers.status_id, customers.created_at as customer_created_at, customers.phone, customers.email, customers.source_id, customers.status_id, actions.created_at as action_created_at')) 
                        ->groupBy('customers.id','customers.name', 'customers.status_id', 'customers.created_at', 'actions.created_at', 'customers.phone', 'customers.email', 'customers.source_id','customers.status_id')  
                        ->orderBy('actions.created_at', 'DESC')       
                        ->get();


        $from_date = date("Y-m-d"). " 00:00:00";
        $to_date = date("Y-m-d"). " 23:59:59";

        /*GRAFICO*/
        $new_customers = Customer::whereYear('created_at' ,$year) 
                        ->whereMonth('created_at' ,$month) 
                        ->count();

        $attended_customers = Customer::
                            join('actions', 'actions.customer_id','customers.id')
                            ->join('action_types','action_types.id','actions.type_id')
                            ->where('action_types.outbound', 1)
                            //->whereBetween('customers.created_at', array($from_date, $to_date))
                            ->whereYear('customers.created_at' ,$year) 
                            ->whereMonth('customers.created_at' ,$month) 
                            ->select(DB::raw('COUNT(DISTINCT(customers.id)) as count')) 
                            ->first();
        /*FIN GRAFICO*/



        //SELECT count(a.customer_id), c.* FROM `customers` c left join actions a on c.id = a.customer_id inner join action_types aty on aty.id = a.type_id where c.user_id is not null and aty.outbound = 1 and c.status_id in(1,4,28) and c.created_at between "" GROUP by c.id

        $this_date = date('Y-m-j');
        $last_date = strtotime ( '-3 month' , strtotime ( $this_date ) ) ;
        $last_date = date ( 'Y-m-j' , $last_date );
        $active_customers = Customer::
                            leftJoin('actions', 'actions.customer_id','customers.id')
                            ->join('action_types','action_types.id','actions.type_id')
                            ->where('action_types.outbound', 1)
                            ->whereNotNull('customers.user_id')
                            ->whereIn('customers.status_id', array(1,4,28))
                            ->where('actions.created_at', ">=", $last_date)
                            ->select(DB::raw('customers.id, customers.name, customers.status_id, customers.created_at, customers.phone, customers.email, customers.source_id, customers.status_id, customers.created_at, actions.created_at as action_created_at'))
                            ->orderBy('actions.created_at', 'DESC') 
                            ->get();


        //SELECT * FROM customers c LEFT JOIN actions a ON a.customer_id = c.id INNER JOIN action_types aty ON aty.id = a.type_id WHERE aty.outbound = 1 AND c.user_id IS NOT NULL AND c.status_id IN(1,4,28) AND a.created_at <= "2021-04-16" AND c.created_at <= "2021-04-16"


        $inactive_customers = Customer::
                            leftJoin('actions', 'actions.customer_id','customers.id')
                            ->join('action_types','action_types.id','actions.type_id')
                            ->where('action_types.outbound', 1)
                            ->whereNotNull('customers.user_id')
                            ->whereIn('customers.status_id', array(1,4,28))
                            ->where('actions.created_at', "<=", "2021-04-16")
                            ->where('customers.created_at', "<=", "2021-04-16")
                            ->select(DB::raw('DISTINCT(customers.id), customers.name, customers.status_id, customers.created_at, customers.phone, customers.email, customers.source_id, customers.status_id, customers.created_at, actions.created_at as action_created_at'))
                            ->orderBy('actions.created_at', 'DESC') 
                            ->get();


        $customers_whith_out_actions  = Customer::
                        whereNotIn('id', $array_customer_id)
                        ->whereYear('created_at' ,$year) 
                        ->whereMonth('created_at' ,$month) 
                        ->whereNotNull('user_id')
                        ->orderBy('created_at', 'DESC')
                        ->paginate(5);
        return view('actions.pending_actions', compact('model','users', 'action_options','request','customers_whith_actions','new_customers','attended_customers','active_customers', 'inactive_customers','total_pending_action', 'customers_whith_out_actions')); 
    }




    public function paginacion(){
        $customers_id = Action::
                        rightJoin('customers', 'actions.customer_id', 'customers.id')
                        ->join('action_types', 'action_types.id', 'actions.type_id')
                        ->where('action_types.outbound', 1)
                        ->select(DB::raw('DISTINCT(customers.id)')) 
                        ->groupBy('customers.id')         
                        ->get();    

        $array_customer_id = array();
        foreach ($customers_id as $key => $value) {
            $array_customer_id[] = $value["id"];
        }
        $year = date("Y");
        $month = date("n");

        $customers_whith_out_actions  = Customer::
                        whereNotIn('id', $array_customer_id)
                        ->whereYear('created_at' ,$year) 
                        ->whereMonth('created_at' ,$month) 
                        ->whereNotNull('user_id')
                        ->orderBy('created_at', 'DESC')
                        ->paginate(5);

        $total_pending_action = $this->getPendingActions();
        return view('actions.pending_actions.whith_out_actions', compact('customers_whith_out_actions', 'total_pending_action'));
    }

    public function getPendingActions(){
        $customers_id = Action::
                        rightJoin('customers', 'actions.customer_id', 'customers.id')
                        ->join('action_types', 'action_types.id', 'actions.type_id')
                        ->where('action_types.outbound', 1)
                        ->select(DB::raw('DISTINCT(customers.id)')) 
                        ->groupBy('customers.id')         
                        ->get();    
        $array_customer_id = array();
        foreach ($customers_id as $key => $value) {
            $array_customer_id[] = $value["id"];
        }
        $year = date("Y");
        $month = date("n");
        $model = Customer::
                        whereNotIn('id', $array_customer_id)
                        ->whereYear('created_at' ,$year) 
                        ->whereMonth('created_at' ,$month) 
                        ->orderBy('created_at', 'DESC')
                        ->count();

        if($model<1){
            $model = 0;
        }
        return $model;
    }


	public function filterModel(Request $request){
        
//        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Action::leftJoin("customers", "customers.id", "actions.customer_id")
            ->where(
                // Búsqueda por...
                 function ($query) use ($request) {
                    $dates = $this->getDates($request);
                    if(isset($request->from_date)&& ($request->from_date!=null)){
                        /*
                        if (isset($request->from_date) && $request->from_date) {
                            $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                            $query = $query->whereBetween("customers.$column", $dates);
                        }
                        */
                        if(isset($request->user_id)  && ($request->user_id!=null))
                            $query->whereBetween('actions.updated_at', $dates);
                        else
                            $query->whereBetween('actions.created_at', $dates);
                    }
                    
                    if(isset($request->creator_user_id)  && ($request->creator_user_id!=null))
                        $query->where('actions.creator_user_id', $request->creator_user_id);
                    /*
                    if(isset($request->user_id)  && ($request->user_id!=null))
                        $query->where('customers.user_id', $request->user_id);
                    */

                    //dd($request);
                    if(isset($request->type_id)  && ($request->type_id!=null))
                        $query->where('actions.type_id', $request->type_id);
                    
                    
                }
                   

             )
                
            ->orderBy('actions.updated_at','desc')
            ->orderBy('type_id','asc')
            ->paginate(20);


        $model->getActualRows = $model->currentPage()*$model->perPage();

        return $model;
    }

    public function getDates($request){

        $to_date = Carbon\Carbon::today()->subDays(2); // ayer
        $from_date = Carbon\Carbon::today()->subDays(5000);
        if(isset($request->from_date) && ($request->from_date!=null)){
            $from_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date);
            $to_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date);
        }
        $to_date = $to_date->format('Y-m-d')." 23:59:59";
        $from_date = $from_date->format('Y-m-d');

        return array($from_date, $to_date); 
    }

    public function edit($id)
    {
        $model = Action::find($id);
        $action_options = ActionType::all();
        $users = User::all();

        return view('actions.edit', compact('model', 'action_options', 'users')); 
    }

    public function update(Request $request){      
        $model = Action::find($request->id);
        $model->note = $request->note;
        $model->type_id = $request->type_id;
        
        //$model->creator_user_id = Auth::id();
        //$model->customer_id = $request->customer_id;

        $model->save();

        return back();
    } 

    public function destroy($id)
    {
        $model = Action::find($id);
        $customer_id = $model->customer_id;
        if ($model->delete()) {
            return redirect('customers/'.$customer_id."/show")->with('statusone', 'La acción <strong>'.$model->name.'</strong> fué eliminada con éxito!'); 
        }
    }


    public function schedule(Request $request){
        if(Auth::user()){
            $from_date = date("Y-m-d"). " 00:00:00";
            $model = Action::where('created_at',">",$from_date)->get();
            return view('actions.calendar.schedule', compact('model'));
        }
        return redirect("/");
    }
    
    public function completePendingAction(Request $request)
    {
        $request->validate([
            'action_id' => 'required|exists:actions,id',
            'note' => 'required|string',
            'type_id' => 'required|exists:action_types,id',
            'status_id' => 'required|exists:customer_statuses,id',
        ]);

        $pendingAction = Action::findOrFail($request->action_id);
        $customer = $pendingAction->customer;

        $pendingAction->delivery_date = Carbon\Carbon::now();
        $pendingAction->save();

        $newAction = new Action();
        $newAction->note = $request->note;
        $newAction->type_id = $request->type_id;
        $newAction->creator_user_id = Auth::id();
        $newAction->customer_id = $pendingAction->customer_id;
        $newAction->save();

        if ($customer) {
            $history = new CustomerHistory();
            $history->saveFromModel($customer);
            $customer->status_id = $request->status_id;
            $customer->save();
        }

        return redirect()->back()->with('statusone', 'Acción completada con éxito');
    }
}
