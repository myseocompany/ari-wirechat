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

        if (!$model) {
            return redirect()->back()->with('statustwo', 'La acción solicitada no existe.');
        }

        $user = Auth::user();

        if (!$user || !$user->canDeleteActions()) {
            return redirect('customers/'.$model->customer_id."/show")->with('statustwo', 'No tienes permisos para eliminar acciones.');
        }

        $customer_id = $model->customer_id;

        if ($model->delete()) {
            return redirect('customers/'.$customer_id."/show")->with('statusone', 'La acción <strong>'.$model->note.'</strong> fue eliminada con éxito!');
        }

        return redirect()->back()->with('statustwo', 'No fue posible eliminar la acción.');
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


    // App/Http/Controllers/ActionController.php

public function calendar(Request $request) {
    // Renderiza la vista con filtros iniciales (opcional)
    $users = \App\Models\User::active()->get();
    $types = \App\Models\ActionType::orderBy('weigth','desc')->get();
    return view('actions.calendar.index', compact('users','types','request'));
}


// App/Http/Controllers/ActionController.php

public function index(Request $request){
    $view = $request->get('view', 'list'); // 'list' | 'calendar'

    // Filtros y KPIs (se calculan igual para ambas vistas)
    $forcePending = $request->input('pending') === 'true';
    $overdueRequest  = $this->actionService->createFilteredRequest($request, 'overdue',  $forcePending);
    $todayRequest    = $this->actionService->createFilteredRequest($request, 'today',    $forcePending);
    $upcomingRequest = $this->actionService->createFilteredRequest($request, 'upcoming', $forcePending);

    $overdueActions  = $this->actionService->filterModel($overdueRequest);
    $todayActions    = $this->actionService->filterModel($todayRequest);
    $upcomingActions = $this->actionService->filterModel($upcomingRequest);

    // Si es lista, paginamos; si es calendario, no cargues la lista para ahorrar DB
    $model = $view === 'list' ? $this->actionService->filterModel($request) : null;

    $allRequest = clone $request; $allRequest->merge(['range_type' => 'all']);
    $totalFilteredActions = $this->actionService->countAllMatching($allRequest);

    $users = User::where('status_id',1)->get();
    $action_options = ActionType::orderby("weigth", "DESC")->get();
    $statuses_options = CustomerStatus::orderBy("stage_id")->orderBy("weight")->get();
    $types = $action_options;

    return view('actions.index', compact(
        'view','model','users','action_options','request',
        'overdueActions','todayActions','upcomingActions',
        'statuses_options','totalFilteredActions','types'
    ));
}

public function calendarFeed(Request $request)
{
    try {
        // 1) Ventana de calendario
        $startParam = $request->query('start');
        $endParam   = $request->query('end');

        // FullCalendar siempre manda start/end, pero por si acaso:
        $start = $startParam ? \Carbon\Carbon::parse($startParam)->startOfDay()
                             : \Carbon\Carbon::now()->startOfMonth();
        $end   = $endParam   ? \Carbon\Carbon::parse($endParam)->endOfDay()
                             : \Carbon\Carbon::now()->endOfMonth();

        // 2) Pendientes
        $pendingOnly = filter_var($request->query('pending', '1'), FILTER_VALIDATE_BOOLEAN);

        // 3) Query base
        $q = Action::query()
            ->when($pendingOnly, fn($q)=>$q->whereNull('delivery_date')->whereNotNull('due_date'))
            ->whereBetween('due_date', [$start, $end])
            ->when($request->filled('user_id'), fn($q)=>$q->where('creator_user_id', $request->user_id))
            ->when($request->filled('type_id'), fn($q)=>$q->where('type_id', $request->type_id))
            ->when($request->filled('search'),  fn($q)=>$q->where('note', 'like', '%'.$request->search.'%'))
            ->with('type');

        $nowStart = \Carbon\Carbon::now()->startOfDay();
        $nowEnd   = \Carbon\Carbon::now()->endOfDay();

        $events = $q->get()->map(function ($a) use ($nowStart, $nowEnd) {
            $status = 'upcoming';
            if ($a->due_date && $a->due_date < $nowStart)                        $status = 'overdue';
            elseif ($a->due_date && $a->due_date->between($nowStart, $nowEnd))   $status = 'today';
            if ($a->delivery_date)                                               $status = 'done';

            $colors = [
                'overdue' => ['#B91C1C','#FEE2E2'],
                'today'   => ['#1D4ED8','#DBEAFE'],
                'upcoming'=> ['#065F46','#D1FAE5'],
                'done'    => ['#6B7280','#E5E7EB'],
            ];
            [$border, $bg] = $colors[$status];

            // Guardas: no encadenar sobre optional()
            $start = $a->due_date ? $a->due_date->toIso8601String() : null;
            $end   = $a->due_date ? $a->due_date->copy()->addMinutes(30)->toIso8601String() : null;

            return [
                'id'    => $a->id,
                'title' => (($a->type->name ?? 'Acción').' · '.mb_strimwidth($a->note ?? 'Sin nota', 0, 50, '…')),
                'start' => $start,
                'end'   => $end,
                'url'   => route('actions.show', $a->id),
                'editable' => !$a->delivery_date,
                'backgroundColor' => $bg, 'borderColor' => $border, 'textColor' => '#111827',
            ];
        });

        return response()->json($events);

    } catch (\Throwable $e) {
        \Log::error('calendarFeed error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Internal'], 500);
    }
}


/** Reprogramar desde calendar (drag/drop o resize) */
public function reschedule(Request $request, Action $action)
{
    $this->authorize('update', $action); // opcional si usas policies
    $request->validate([
        'due_date' => 'required|date',      // ISO8601 desde FullCalendar
    ]);

    // No permitir mover acciones ya entregadas
    if ($action->delivery_date) {
        return response()->json(['message'=>'La acción ya fue completada'], 422);
    }

    $action->due_date = \Carbon\Carbon::parse($request->input('due_date'));
    $action->save();

    return response()->json(['ok'=>true]);
}


}
