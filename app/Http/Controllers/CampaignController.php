<?php

// mqe

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Audience;
use App\Models\Customer;
use App\Models\User;
use App\Models\Project;
use App\Models\AudienceCustomer;
use App\Models\CustomerStatus;
use App\Models\CustomerSource;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Action;
use Carbon;
use DB;
use Auth;

class CampaignController extends Controller{

	public function __construct(){   
        //$this->middleware('auth'); 
    }
	
	public function index(Request $request){
		$model = Campaign::all();
		
		return view('campaigns.index', compact('model'));
	}

	public function show($id){
        $messages = Message::where('audience_id', $id)->get();
        $audience = Audience::find($id);
        $model = Customer::select("customers.id as id", "phone", "phone2", "name", "project_id")
            //->where('status_id', '=', 1)
            ->where('audience_id',$id)
            ->join('audience_customer','customers.id', 'audience_customer.customer_id')
            ->orderBy('customers.created_at', 'DESC')
            ->whereNull('sended_at')
            ->get();
        return view('campaigns.show', compact('model','audience', 'messages'));
	}

	public function create(Request $request){
		$model = $this->filterModel($request);
		return view('campaigns.create', compact('model', 'request'));
	}

    public function store(Request $request){
        $model = new Campaign;
        $model->name = $request->name;
        $model->save();
        return redirect('/campaigns');
    }

    public function filterModel(Request $request){   
        
        $dates = $this->getDates($request);

        //        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Customer::
            leftJoin('actions', 'actions.customer_id', 'customers.id')
            ->leftJoin('action_types', 'actions.type_id', 'action_types.id')
            ->select('customers.id','customers.status_id', 'customers.user_id','customers.source_id', 'customers.created_at', 'customers.updated_at',
        'customers.name','customers.phone','customers.email', DB::raw('(count(if(outbound=0, actions.created_at, null))) / 
(now()-max(if(outbound=0, actions.created_at, null)))   as kpi'), DB::raw('max(if(outbound=0, actions.created_at, null)) as last_inbound_date'))
            ->where(
            // Búsqueda por...
            function ($query) use ($request, $dates) {
                //if(isset($request->from_date) && ($request->from_date!=null)){
                        
                    if ( (isset($request->created_updated) &&  ($request->created_updated=="updated")) ) 
                        $query->whereBetween('customers.updated_at', $dates);
                    else
                        $query->whereBetween('customers.created_at', $dates);
                    
                
                if (isset($request->user_id)  && ($request->user_id != null))
                    $query->where('user_id', $request->user_id);
                if (isset($request->source_id)  && ($request->source_id != null))
                    $query->where('source_id', $request->source_id);
                
                if (isset($request->kpi)  && ($request->kpi != null))
                    $query->where('kpi', $request->kpi);
                if (isset($request->status_id)  && ($request->status_id != null))
                    $query->where('customers.status_id', $request->status_id);
                
                if(isset($request->search)){
                    $query = $query->where(
                        function ($query) use ($request) {
                            
                            $search = html_entity_decode($request->search );
                            
                            $query->orwhere('customers.name', "like", "%" . $search . "%");
                            $query->orwhere('customers.email',   "like", "%" . $search . "%");
                            $query->orwhere('customers.document', "like", "%" . $search . "%");
                            $query->orwhere('customers.position', "like", "%" . $search . "%");
                            $query->orwhere('customers.business', "like", "%" . $search . "%");
                            $query->orwhere('customers.phone',   "like", "%" . $search . "%");
                            $query->orwhere('customers.phone2',   "like", "%" . $search . "%");
                            $query->orwhere('customers.notes',   "like", "%" . $search . "%");
                            $query->orwhere('customers.city',    "like", "%" . $search . "%");
                            $query->orwhere('customers.country', "like", "%" . $search . "%");
                            $query->orwhere('customers.bought_products', "like", "%" . $search . "%");
                            $query->orwhere('customers.contact_name', "like", "%" . $search . "%");
                            $query->orwhere('customers.contact_phone2', "like", "%" . $search . "%");
                            $query->orwhere('customers.contact_email', "like", "%" . $search . "%");
                            $query->orwhere('customers.contact_position', "like", "%" . $search . "%");                            
                        }
                    );
                }                
            }
        )   ->groupBy('customers.id','customers.status_id', 'customers.email', 
        'customers.name','customers.phone','customers.source_id','customers.user_id','customers.created_at', 'customers.updated_at' )
            ->orderBy('customers.status_id', 'asc')
            ->orderByRaw('(count(if(outbound=0, actions.created_at, null))) / 
(now()-max(if(outbound=0, actions.created_at, null))) DESC')
            ->orderBy('customers.created_at', 'desc')
            ->get();

        return $model;
    }

    public function getDates($request){
        $to_date = Carbon\Carbon::today()->subDays(0); // ayer
        $from_date = Carbon\Carbon::today()->subDays(0);

        if(isset($request->from_date) && ($request->from_date!=null)){
                        
            $from_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date);
            $to_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date);
        }

        $to_date = $to_date->format('Y-m-d')." 23:59:59";
        $from_date = $from_date->format('Y-m-d');
        $data = array($from_date, $to_date);

        //dd($data);
        return $data; 
    }

    /*public function store(Request $request){
    	$model = new Audience;

    	$model->name = $request->name;
        //$model->msg1 = $request->message;
    	$model->type_id = 1;
    	$model->save();
    	//return view('campaigns.create_customers', "model");
    	return redirect('/campaigns/'.$model->id."/customers");
    }*/

    public function edit($id)
    {
        $campaign = Campaign::find($id);
        $messages = CampaignMessage::where('campaign_id', $id)->get();
        return view('campaigns.edit', compact('campaign','messages'));
    }

    public function update(Request $request, $id)
    {
        //dd($request);
        $model = Audience::find($id);
        $model->name = $request->name;

        $data = Array();
        foreach ($request->message as $key => $value) {
            $message = Message::where('name', $value)->first();
            if($message == null){
                $data[] = array("audience_id"=>$id, "name"=>$value);     
            }      
        }
        $model->save();
        Message::insert($data);
        return redirect('/campaigns/'.$id."/customers");
    }
	
	public function getUsers(){
        return  User::orderBy('name')
            ->where('users.status_id', 1)
            ->get();
    }


    function storeCustomers($id, Request $request){
    	// si los tiene todos seleccionados
    	//dd($request->customer_id);
    	// guardo todo los registros
    	$data = Array();
    	foreach ($request->customer_id as $key => $value) {
    	   /*
        	$model = AudienceCustomer::where('customer_id', $value)
    			->where('audience_id', $id)
    			->first();
    		if(!$model)
            */
    			$data[] = array("audience_id"=>$id, "customer_id"=>$value);
    			
    	}
        //dd($request->customer_id);

    	AudienceCustomer::insert($data);
    	
    	return redirect('/campaigns/'.$id."/customers");
    	
    }

    function destroyCustomer($aid, $cid){
		$model = AudienceCustomer::where('customer_id', $cid)
			->where('audience_id', $aid)
			->first();
		$model->delete();

    	return redirect('/campaigns/'.$aid."/customers");
    }



    public function messageEdit($id){
        $audience = Audience::find($id);
        $messages = Message::where('audience_id', $id)->get();
        return view('campaigns.messages.edit', compact('audience','messages'));
    }

    public function messageUpdate(Request $request, $id){
        $model = Audience::find($id);
        $data = Array();
        foreach ($request->message as $key => $value) {
            $message = Message::where('name', $value)->first();
            if($message == null){
                $data[] = array("audience_id"=>$id, "name"=>$value);     
            }      
        }
        $model->save();
        Message::insert($data);
        return redirect('/campaigns/'.$id."/customers");
    }


    public function destroyMessage($mid){
        $model = CampaignMessage::find($mid);
        $model->delete();
        return response()->json(['mensaje'=>'Eliminado Satisfactoriamente!']);
    }

    public function updateMessage(Request $request, $mid){
        $model = CampaignMessage::find($mid);
        $model->text = $request->name;
        $model->campaign_id = $request->cid;
        $model->save();
        return response()->json(['mensaje'=>'Actualizado Satisfactoriamente!']);
    }

    public function storeMessage(Request $request){
        //dd($request);
        $model = new CampaignMessage;
        $model->text = $request->name;
        $model->campaign_id = $request->cid;
        $model->save();
        return response()->json(['mensaje'=>'Registrado Satisfactoriamente!']);
    }

    public function updateCampaign($aid, Request $request){
        $model = Campaign::find($aid);
        $model->name = $request->name;
        $model->save();
        return response()->json(['mensaje'=>'Actualizado Satisfactoriamente!']);
    }

      public function getMessages($id){      
        $model = CampaignMessage::where('campaign_id', $id)
            ->get();
        return $model->toJson();
    }

   public function getPhone($id, $message){      
        $action = new Action;
        $action->customer_id = $id;
        $action->object_id = null;
        $action->type_id = 14;
        $action->note = "Se envió mensaje: ". urldecode($message);
        $action->creator_user_id = Auth::id();
        date_default_timezone_set('America/Bogota');
        $date = date('Y-m-d H:i:s');
        $action->delivery_date= $date;
        $action->save();

        $customer = Customer::find($id);
        $customer->status_id = 28;//Seguimiento
        $customer->save();

        $model = Customer::find($id);
        return $model->getPhone();
    }



}
