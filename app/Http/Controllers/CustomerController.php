<?php
//*****  mqe
//*****  ultimo cambio 2014_11_04

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Mail;
use File;
use Illuminate\Support\Facades\Auth;
use Carbon;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\CustomerStatusPhase;
use App\Models\User;
use App\Models\CustomerSource;
use App\Models\CustomerHistory;
use App\Models\CustomerFile;
use App\Models\EmailBrochure;
use App\Models\Action;
use App\Models\Email;
use App\Models\ActionType;
use App\Models\Product;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use App\Models\Audience;
use App\Models\AudienceCustomer;
use App\Models\Reference;
use App\Models\RdStation;
use App\Models\Campaign;
use App\Models\CampaignMessage;

use App\Models\Log;
use App\Models\Country;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    protected $attributes = ['status_name'];
    protected $appends = ['status_name'];
    protected $status_name;
    protected $customerService;

    public function __construct(CustomerService $customerService) {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        return $this->customers(1, $request);
    }
    public function indexPhase($pid, Request $request)
    {
        return $this->customers($pid, $request);
    }
    public function getPendingActions()
    {
        $model = Action::whereNotNull('due_date')
            ->whereNull('delivery_date')
            ->where('creator_user_id', "=", Auth::id())
            ->get();
        return $model;
    }
    public function getInterestOptions(Request $request)
    {
        $model = DB::table('customers')
            ->select(DB::raw('distinct scoring_interest'))
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        if (isset($request->created_updated)  && ($request->created_updated == "updated"))
                            $query = $query->whereBetween('customers.updated_at', array($request->from_date, $request->to_date));
                        if (isset($request->created_updated)  && ($request->created_updated == "created"))
                            $query = $query->whereBetween('customers.created_at', array($request->from_date, $request->to_date));
                    }
                    /*
                $date_at = $request->created_updated === "updated" ? 'customers.updated_at' : 'customers.created_at';
                                if(isset($request->from_date) && $request->from_date != "") {
                    $query->whereBetween($date_at, $dates);
                }
                */
                    if (isset($request->product_id)  && ($request->product_id != null)) {
                        if ($request->product_id == 1)
                            $query = $query->whereIn('customers.product_id', array(1, 6, 7, 8, 9, 10, 11));
                        else
                            $query = $query->where('customers.product_id', $request->product_id);
                    }
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('customers.user_id', $request->user_id);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    if (isset($request->search)) {
                        $query = $query->where(
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
                                //$innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                            }
                        );
                    }
                }
            )
            ->orderby('scoring_interest', 'ASC')
            ->whereNotNull('scoring_interest')
            ->get();
        return $model;
    }
    public function getProfileOptions(Request $request)
    {
        $model = Customer::select(DB::raw('distinct scoring_profile'))
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        if (isset($request->created_updated)  && ($request->created_updated == "updated"))
                            $query = $query->whereBetween('customers.updated_at', array($request->from_date, $request->to_date));
                        if (isset($request->created_updated)  && ($request->created_updated == "created"))
                            $query = $query->whereBetween('customers.created_at', array($request->from_date, $request->to_date));
                    }
                    if (isset($request->product_id)  && ($request->product_id != null)) {
                        if ($request->product_id == 1)
                            $query = $query->whereIn('customers.product_id', array(1, 6, 7, 8, 9, 10, 11));
                        else
                            $query = $query->where('customers.product_id', $request->product_id);
                    }
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('customers.user_id', $request->user_id);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    if (isset($request->search)) {
                        $query = $query->where(
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
                                //$innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                            }
                        );
                    }
                }
            )
            ->orderby('scoring_profile', 'desc')
            ->whereNotNull('scoring_profile')
            ->get();
        return $model;
    }

    public function getProfileOptionsOrder()
    {
        $model = Customer::select(DB::raw('distinct scoring_profile'))
            ->orderby('scoring_profile', 'asc')
            ->whereNotNull('scoring_profile')
            ->get();
        return $model;
    }
    /*
    public function customers(Request $request) {
        $users = $this->getUsers();
        $customer_options = CustomerStatus::all();
        $statuses = $this->getStatuses($request, 1);
        $model= $this->getModel($request, $statuses, 'customers');
        //$customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $pid);
        $customersGroup = null;
        $sources = CustomerSource::all();
        $pending_actions = $this->getPendingActions();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $audiences = Audience::all();
        return view('customers.index', compact('model', 'request','customer_options','customersGroup', 'query','users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'audiences'));
    }*/
    /*  Esta función debe retornar todos los estados de la fase y previo los estados 
        que componen los demás estados
    */


    public function customers($pid, Request $request)
    {
        $menu = $this->customerService->getUserMenu(Auth::user(1));

        session(['stage_id' => $pid]);
        $users = $this->getUsers();

        $customer_options = $this->customerService->getCustomerWithParent($pid);

        $statuses = $this->getStatuses($request, $pid);
        //$statuses = CustomerStatus::all();
        $model = $this->customerService->filterCustomers($request, $statuses, $pid, false, 5);
        //$model = $this->customerService->getModelPhase($request, $statuses, $pid);

        

        //$customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $pid);
        $customersGroup = $this->customerService->filterCustomers($request, $statuses, $pid, true);
        
        $pending_actions = $this->getPendingActions();
        $phase = CustomerStatusPhase::find($pid);
        $sources = CustomerSource::all();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $inquiry_products = Product::all();
        $customer = null;
        $id = 0;
        if ($model && isset($model[0])) {
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            $id = $request->customer_id;
        }
        //dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('status_id', 1)
            ->where('stage_id', $pid)
            ->orderBy("weight", "ASC")
            ->get();
        //$country_options = Country::leftJoin("customers", "customers.country", "countries.iso2")->get();
        $country_options =  Country::select(DB::raw("DISTINCT(customers.country)"))
            ->leftJoin("customers", "customers.country", "countries.iso2")
            ->orderBy("customers.country", "ASC")
            ->get();
        //dd($country_options);
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $messages = CampaignMessage::where('campaign_id', 11)->get();
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby("created_at", "DESC")->get();
        }
        return view('customers.index', compact('country_options', 'inquiry_products', 'model', 'request',   'messages', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu'));
    }


    public function customersByStage($sid, Request $request)
    {
        $menu = $this->customerService->getUserMenu(Auth::user(1));
        session(['stage_id' => $sid]);
        $users = $this->getUsers();
        $customer_options = $this->customerService->getCustomerWithParent($sid);
        $statuses = $this->getStatuses($request, $sid);
        //$statuses = CustomerStatus::all();
        $model = $this->customerService->getModelPhase($request, $statuses, $sid);
        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $sid);
        $pending_actions = $this->getPendingActions();
        $phase = CustomerStatusPhase::find($sid);
        $sources = CustomerSource::all();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $customer = null;
        $id = 0;
        if ($model && isset($model[0])) {
            //dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            $id = $request->customer_id;
        }
        //dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $sid)->orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $messages = CampaignMessage::where('campaign_id', 11)->get();
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby("created_at", "DESC")->get();
        }
        return view('customers.index', compact('model', 'request',   'messages', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu'));
    }
    public function dragleads(Request $request)
    {
        $pid =  substr($request->path(), -1);
        $menu = $this->customerService->getUserMenu(Auth::user(1));
        session(['stage_id' => $pid]);
        $statuses = $this->getStatuses($request, $pid);
        $model = $this->customerService->getModelPhase($request, $statuses, $pid);
        $users = $this->getUsers();
        $customer_options = $this->customerService->getCustomerWithParent($pid);
        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $pid);
        $pending_actions = $this->getPendingActions();
        $phase = CustomerStatusPhase::find($pid);
        $sources = CustomerSource::all();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $customer = null;
        $id = 0;
        if ($model && isset($model[0])) {
            //dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            $id = $request->customer_id;
        }
        //dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $pid)->orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $messages = CampaignMessage::where('campaign_id', 11)->get();
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby("created_at", "DESC")->get();
        }
        return view('customers.newIndex', compact('model', 'request', 'messages', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu'));
    }
    public function getSources()
    {
        $model = CustomerSource::orderBy("name")->get();
        return $model;
    }


    public function leads(Request $request)
    {
        if (!session()->has('stage_id'))
            session(['stage_id' => 1]);
        $stage_id = session('stage_id');
        $users = $this->getUsers();
        $customer_options = $this->customerService->getCustomerWithParent($stage_id);
        $statuses = $this->getStatuses($request, 1);
        $model = $this->getModel($request, $statuses, 'leads');
        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $stage_id);
        $pending_actions = $this->getPendingActions();
        $sources = $this->getSources();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $country = Customer::where('status_id', 8)
            ->select(DB::raw("DISTINCT(country)"))
            ->get();
        $customer = null;
        $id = 0;
        if ($model && isset($model[0])) {
            //dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            $id = $request->customer_id;
        }
        //dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $stage_id)->orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        //$campaigns = Campaign::all();
        $messages = CampaignMessage::where('campaign_id', 8)->get();
        $metas = CustomerMetaData::find($request->customer_id);
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby("created_at", "DESC")->get();
        }
        return view('customers.index', compact('model', 'metas', 'request', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'country', 'messages'));
    }


    // Función para normalizar números de teléfono
    function normalizePhoneNumber($phoneNumber)
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }

    public function updateDesmechadora()
    {
        $emails = $this->extractEmailsFromLogs(); // Asume que esta es la función que escribimos antes
        foreach ($emails as $email) {
            Customer::where('email', $email)->update(['inquiry_product_id' => 15]);
        }
    }
    public function extractEmailsFromLogs()
    {
        $emails = Log::where('request', 'like', '%desmech%')
            ->get()
            ->map(function ($log) {
                $data = json_decode($log->request, true);
                $leads = Arr::get($data, 'leads', []);
                $emails = [];
                foreach ($leads as $lead) {
                    // Intenta extraer el correo electrónico de diferentes partes del JSON
                    $email = Arr::get($lead, 'email', null) ?: Arr::get($lead, 'email_lead', null);
                    if ($email) {
                        $emails[] = $email;
                    }
                }
                return $emails;
            })
            ->flatten()
            ->unique()
            ->values();
        return $emails;
    }


    public function statusName($id)
    {
        $datastatus = DB::table('customer_statuses')
            ->where('id', '=', $id)
            ->get();
        return $datastatus->name;
    }
    public function getModel(Request $request, $statuses, $action)
    {
        $model = $this->customerService->filterModel($request, $statuses);
        $model->getActualRows = $model->currentPage() * $model->perPage();
        if ($model->perPage() > $model->total())
            $model->getActualRows = $model->total();
        foreach ($model as $items) {
            if (isset($items->status_id)) {
                $status = CustomerStatus::find($items->status_id);
                if (isset($status))
                    $items->status_name = $status->name;
            }
        }
        $model->action = $action;
        return $model;
    }
    public function getUsers()
    {
        return  User::orderBy('name')
            ->where('users.status_id', 1)
            ->get();
    }
    public function getStatuses(Request $request, $step)
    {
        /*
            $statuses ="";
            if(isset($request->from_date)||($request->from_date!="") )
                $statuses = $this->getAllStatusID($step);
            else{
                
                $statuses = $this->getStatusID($request, $step);
            }    
            return $statuses;
            */
        return $statuses = CustomerStatus::all();
    }
    public function filterModelNew(Request $request, $statuses)
    {
        //        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Customer::leftJoin('view_customers_followups', 'view_customers_followups.cid', 'customers.id')
            ->leftJoin('audience_customer', 'audience_customer.customer_id', 'customers.id')
            ->select(
                'customers.id',
                'customers.status_id',
                'customers.product_id',
                'customers.user_id',
                'customers.created_at',
                'customers.updated_at',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.country',
                DB::raw('max(if(outbound=0, actions.created_at, null)) as last_inbound_date'),
                'notes',
                'source_id',
                'scoring_interest',
                'scoring_profile'
            )
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        if (isset($request->created_updated)  && ($request->created_updated == "updated"))
                            $query = $query->whereBetween('customers.updated_at', array($request->from_date, $request->to_date));
                        if (isset($request->created_updated)  && ($request->created_updated == "created"))
                            $query = $query->whereBetween('customers.created_at', array($request->from_date, $request->to_date));
                    }
                    if (isset($request->product_id)  && ($request->product_id != null)) {
                        if ($request->product_id == 1)
                            $query = $query->whereIn('customers.product_id', array(1, 6, 7, 8, 9, 10, 11));
                        else
                            $query = $query->where('customers.product_id', $request->product_id);
                    }
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('customers.user_id', $request->user_id);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    if (isset($request->search)) {
                        $query = $query->where(
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
                                //$innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                            }
                        );
                    }
                    if (isset($request->actions_number)) {
                        $query->havingRaw('lead07 = ' . $request->actions_number);
                        $query->where('outbound', '1');
                    }
                    //PENDIENTE 
                    if (isset($request->week)) {
                        $query->where('view_customers_followups.week', $request->week);
                        $query->where('view_customers_followups.year', $request->year);
                        $query->whereIn('customers.status_id', [1, 28]);
                        //$dates = $this->getWeek($request->week);
                        //$query->whereBetween('view_customers_followups.created_at', $dates);
                        //$query->whereBetween('created_at', array($request->from_date, $request->to_date));
                    }
                    if (isset($request->lead)) {
                        $lead = $this->getLead($request->lead);
                        $sql = "view_customers_followups." . $lead;
                        if (isset($request->with) && ($request->with == 1))
                            $query->where($sql, "<>", 0);
                        elseif (isset($request->with) && ($request->with == 0))
                            $query->where($sql, "=", 0);
                        //$query->select(DB::raw("sum(if(".$lead."<>0,1,0))>0 as lean_time"));
                        //$query->where('outbound', '1');    
                    }
                }
            )
            ->groupBy(
                'customers.id',
                'customers.status_id',
                'customers.email',
                'customers.name',
                'customers.phone',
                'customers.product_id',
                'customers.user_id',
                'customers.created_at',
                'customers.updated_at',
                'customers.country',
                'notes',
                'source_id',
                'scoring_interest',
                'scoring_profile'
            )
            ->orderBy('customers.created_at', 'DESC')
            //->havingRaw('(count(if(outbound=0, actions.created_at, null)))','is not null')
            ->paginate(10);
        return $model;
    }


    public function getWeek($week)
    {
        $from_date = \Carbon\Carbon::create(2021, 1, 1, 0, 0, 0, 'America/Bogota');
        $from_date = $from_date->addWeek($week);
        $to_date = \Carbon\Carbon::create(2021, 1, 1, 0, 0, 0, 'America/Bogota');
        $to_date = $to_date->addWeek($week);
        $to_date = $to_date->addDays(7);
        return array($from_date, $to_date);
    }
    /* public function userName(Request $request){
        $model = Users::
        leftJoin('customers', 'customers.id')
            ->select('users.name')
            ->where(
                function ($query) use ($request) {
                
                    if(isset($request->name)  && ($request->name!=null))
                    $query = $query->where('users.name', $request->name);
               
                }
           
            )
            ->groupBy('users.name')
            ->get();
            return $model;
    } */

    public function filterModel50(Request $request, $statuses)
    {
        //        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Customer::leftJoin('actions', 'actions.customer_id', 'customers.id')
            ->leftJoin('action_types', 'actions.type_id', 'action_types.id')
            ->select(
                'customers.id',
                'customers.status_id',
                'customers.product_id',
                'customers.user_id',
                'customers.created_at',
                'customers.updated_at',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('(count(if(outbound=0, actions.created_at, null))) / (now()-max(if(outbound=0, actions.created_at, null)))   as kpi'),
                DB::raw('
(now()-max(if(outbound=0, actions.created_at, null)))   as recency'),
                DB::raw('max(if(outbound=0, actions.created_at, null)) as last_inbound_date')
            )
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        if (isset($request->user_id)  && ($request->user_id != null))
                            $query = $query->whereBetween('customers.updated_at', array($request->from_date, $request->to_date));
                        else
                            $query = $query->whereBetween('customers.created_at', array($request->from_date, $request->to_date));
                    }
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('customers.user_id', $request->user_id);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    if (isset($request->search)) {
                        $query = $query->orwhere('customers.name', "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.email',   "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.document', "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.business', "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.position', "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.city',    "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.country', "like", "%" . $request->search . "%");
                        $query = $query->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                        //$query = $query->orwhere('customers.status_temp',"like", "%".$request->search."%");
                        $query = $query->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                        // $query = $innerQuery->orwhere('actions.note',"like", "%".$request->search."%");
                    }
                }
            )
            ->groupBy(
                'customers.id',
                'customers.status_id',
                'customers.email',
                'customers.name',
                'customers.phone',
                'customers.product_id',
                'customers.user_id',
                'customers.created_at',
                'customers.updated_at'
            )
            ->orderBy('customers.status_id', 'asc')
            ->orderByRaw('(count(if(outbound=0, actions.created_at, null))) / 
(now()-max(if(outbound=0, actions.created_at, null))) DESC')
            ->orderBy('customers.created_at', 'desc')
            ->paginate(50);
        return $model;
    }
    public function filterModelFullColombia(Request $request, $statuses)
    {
        //        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Customer::where(
            // Búsqueda por...
            function ($query) use ($request) {
                if (isset($request->from_date) && ($request->from_date != null)) {
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->whereBetween('customers.updated_at', array($request->from_date, $request->to_date));
                    else
                        $query = $query->whereBetween('customers.created_at', array($request->from_date, $request->to_date));
                }
                if (isset($request->user_id)  && ($request->user_id != null))
                    $query = $query->where('customers.user_id', $request->user_id);
                if (isset($request->source_id)  && ($request->source_id != null))
                    $query = $query->where('customers.source_id', $request->source_id);
                if (isset($request->status_id)  && ($request->status_id != null))
                    $query = $query->where('customers.status_id', $request->status_id);
                if (isset($request->search)) {
                    $query = $query->orwhere('customers.name', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.email',   "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.document', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.business', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.position', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.city',    "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.country', "like", "%" . "Colombia" . "%");
                    $query = $query->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                    //$query = $query->orwhere('customers.status_temp',"like", "%".$request->search."%");
                    $query = $query->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                    // $query = $innerQuery->orwhere('actions.note',"like", "%".$request->search."%");
                }
            }
        )
            ->orderBy('status_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        return $model;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $stage_id = session('stage_id', '1');
        $users = User::all();
        $customers_statuses = CustomerStatus::where('status_id', 1)
            ->where("stage_id", $stage_id)
            ->orderBy('stage_id', 'ASC')
            ->orderBy('weight', 'ASC')
            ->get();
        $customer_sources = CustomerSource::orderBy('name')->get();
        $products = Product::all();
        return view('customers.create', compact('products', 'customers_statuses', 'users', 'customer_sources'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $model = new Customer;
        $model->name = $request->name;
        $model->document = $request->document;
        $model->position = $request->position;
        $model->business = $request->business;
        $model->product_id = $request->product_id;
        $model->phone = $request->phone;
        $model->phone2 = $request->phone2;
        $model->email = $request->email;
        $model->notes = $request->notes;
        $model->count_empanadas = $request->count_empanadas;
        $model->address = $request->address;
        $model->city = $request->city;
        $model->country = $request->country;
        $model->department = $request->department;
        $model->bought_products = $request->bought_products;
        $model->total_sold = $request->total_sold;
        $model->purchase_date = $request->purchase_date;
        $model->status_id = $request->status_id;
        $model->user_id = $request->user_id;
        $model->source_id = $request->source_id;
        $model->technical_visit = $request->technical_visit;
        //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;
        $model->scoring_interest = $request->scoring_interest;
        $model->scoring_profile = $request->scoring_profile;
        $model->rd_public_url = $request->rd_public_url;
        $model->empanadas_size = $request->empanadas_size;
        $model->number_venues = $request->number_venues;
        if (Auth::id()) {
            $model->updated_user_id = Auth::id();
            $model->creator_user_id = Auth::id();
        }
        $model->maker = $request->maker;
        $this->sendToRDStationFromCRM($model);
        if ($model->save()) {
            $this->storeActionHandbook($model);
            $this->sendWelcomeMail($model);
            return redirect('leads?customer_id=' . $model->id)->with('status', 'El Cliente <strong>' . $model->name . '</strong> fué añadido con éxito!');
        }
    }
    public function sendWelcomeMail($customer)
    {
        $email_id = 46;
        $email = Email::find($email_id);
        $count = Email::sendUserEmailWelcome($customer->id, $email->subject, $email->view, $email->id);
        $this->storeEmailAction($email, $customer, "Correo automático de notificación");
        return back();
    }
    public function storeEmailAction($mail, $customer, $note)
    {
        $today = Carbon\Carbon::now();
        // envio mail
        $action_type_id = 2;
        $model = new Action;
        $model->note = $note;
        $model->type_id = $action_type_id;
        $model->creator_user_id = 0;
        $model->customer_id = $customer->id;
        $model->delivery_date = $today;
        $model->save();
    }
    public function storeActionHandbook($model)
    {
        $action = new Action;
        $action->type_id = 26;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $model->id;
        $action->save();
        return back();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Customer::find($id);
        $action_options = ActionType::orderby('weigth')->get();
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::
            where('status_id', 1)
            ->orderBy('stage_id', "ASC")
            ->orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $meta_data = CustomerMetaData::all();
        $metas = CustomerMetaData::leftJoin('customer_metas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
            ->select('customer_meta_datas.value as name', 'customer_metas.value', 'customer_metas.created_at', 'customer_meta_datas.type_id', 'customer_meta_datas.parent_id')
            ->where('customer_id', '=', $id)
            ->get();
        $weighted = 0;
        $test = CustomerMeta::leftJoin('customer_meta_datas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
            ->select(DB::raw('ROUND(SUM(customer_metas.value)/COUNT(customer_metas.meta_data_id)) AS average'))
            ->where('customer_metas.customer_id', '=', $id)
            ->where('customer_meta_datas.type_id', '=', 1)
            ->get();
        return view('customers.show', compact('model', 'histories', 'test',  'meta_data', 'metas', 'audiences', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today'));
    }
    public function showAction($id, $Aid)
    {
        $actionProgramed = Action::find($Aid);
        $model = Customer::find($id);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::all();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::all();
        $statuses_options = CustomerStatus::orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        return view('customers.show', compact('model', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'actionProgramed'));
    }
    public function showHistory($id)
    {
        $model = CustomerHistory::find($id);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::all();
        $histories = null;
        $email_options = Email::all();
        $statuses_options = CustomerStatus::orderBy("weight", "ASC")->get();
        $actual = false;
        return view('customers.show', compact('model', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $stage_id = session("stage_id", 1);
        $model = Customer::find($id);
        $customer_statuses = CustomerStatus::orderBy("stage_id", "ASC")
            ->where("stage_id", $stage_id)
            ->where('status_id', "1")
            ->orderBy("weight", "ASC")
            ->get();
        $customer_sources = CustomerSource::all();
        $users = User::all();
        $scoring_profile = $this->getProfileOptionsOrder();
        $products = Product::all();
        return view('customers.edit', compact('products', 'model', 'customer_statuses', 'users', 'customer_sources', 'scoring_profile'));
    }
    public function assignMe($id)
    {
        $model = Customer::find($id);
        if (is_null($model->user_id) || $model->user_id == 0) {
            $user =  Auth::id();
            $model->user_id = $user;
            if (Auth::id())
                $model->updated_user_id = Auth::id();
            $model->save();
        }
        return back();
    }
    public function updateAjax(Request $request)
    {
        $model = Customer::find($request->customer_id);
        $model->user_id = $request->user_id;
        $model->save();
        return $model->id;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //dd($request);
        $model = Customer::find($id);
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($model);
        $model->name = $request->name;
        $model->document = $request->document;
        $model->position = $request->position;
        $model->business = $request->business;
        $model->phone = $request->phone;
        $model->email = $request->email;
        $model->notes = $request->notes;
        $model->count_empanadas = $request->count_empanadas;
        $model->phone2 = $request->phone2;
        $model->department = $request->department;
        $model->address = $request->address;
        $model->city = $request->city;
        $model->country = $request->country;
        $model->technical_visit = $request->technical_visit;
        $model->bought_products = $request->bought_products;
        $model->purchase_date = $request->purchase_date;
        $model->user_id = $request->user_id;
        $model->source_id = $request->source_id;
        $model->status_id = $request->status_id;
        //Agregamos el producto en edicion de prospecto
        $model->product_id = $request->product_id;
        //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;
        $model->product_id = $request->product_id;
        $model->scoring_interest = $request->scoring_interest;
        $model->scoring_profile = $request->scoring_profile;
        $model->rd_public_url = $request->rd_public_url;
        $model->empanadas_size = $request->empanadas_size;
        $model->number_venues = $request->number_venues;
        $model->maker = $request->maker;
        $model->total_sold = $request->total_sold;
        if (Auth::id())
            $model->updated_user_id = Auth::id();
        if ($model->save()) {
            return redirect('customers/' . $model->id . '/show')->with('statusone', 'El Cliente <strong>' . $model->name . '</strong> fué modificado con éxito!');
        }
    }
    public function updateAjaxStatus(Request $request)
    {
        $model = Customer::find($request->customer_id);
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($model);
        $model->status_id = $request->status_id;
        $model->save();
        return redirect()->back();
    }
    // Color
    public function filterCustomers($request)
    {
        return Customer::where(
            function ($query) use ($request) {
                if (sizeof($request->status_id))
                    $query = $query->where('customers.status_id', "=", $request->status_id);
            }
        )
            ->select(DB::raw('customers.*'))
            ->orderBy('customers.status_id', 'asc', 'created_at', 'asc')
            ->paginate(20);
    }
    function getStatusID($request, $stage_id)
    {
        $url = $request->fullurl();
        $paramenters = explode("&", $url);
        $res = array();
        foreach ($paramenters as $key => $value) {
            if (strpos($value, "status_id") !== false && (str_replace("status_id=", "", $value) != 0)) {
                $res[] = str_replace("status_id=", "", $value);
            }
        }
        if (!count($res)) {
            $model = $this->customerService->getCustomerWithParent($stage_id);
            //$model = CustomerStatus::all();
            foreach ($model as $item)
                $res[] = $item->id;
        }
        return $res;
    }
    // Enviar email
    public function sendMail($id, $user)
    {
        $model = Email::find($id);
        $subjet = 'Gracias por escribirnos';
        Email::raw($model->body, function ($message) use ($user, $subjet) {
            $message->from('noresponder@mqe.com.co', 'Maquiempanadas');
            $message->to($user->email, $user->user_name)->subject($subjet);
        });
    }
    public function mail($cui)
    {
        //$model = Email::find(1);
        $customer = Customer::find($cui);
        $subjet = 'Bro';
        //dd($customer);
        /*
    Mail::raw($model->body, function ($message) use ($customer, $subjet){
        $message->from('noresponder@mqe.com.co', 'Maquiempanadas');
        $message->to($customer->email, $customer->user_name)->subject($subjet);   
    });
*/
        $emailcontent = array(
            'subject' => 'Gracias por contactarme',
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $cui
        );
        //dd($emailcontent);
        // Mail::send('emails.brochure', $emailcontent, function ($message) use ($customer){
        //         $message->subject('MQE');
        //         $message->to('nicolas@myseocompany.co');
        //     });
    }
    function getAllStatusID($stage_id)
    {
        $res = array();
        $model = $this->customerService->getCustomerWithParent($stage_id);
        //$model = CustomerStatus::all();
        foreach ($model as $item)
            $res[] = $item->id;
        return $res;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Customer::find($id);
        if ($model->delete()) {
            return redirect('customers')->with('statustwo', 'El Cliente <strong>' . $model->name . '</strong> fué eliminado con éxito!');
        }
    }
    public function saveAPICustomer($request)
    {
        //dd($request);
        $model = new Customer;
        $model->name = $request->name;
        $model->phone = $request->phone;
        $model->phone2 = $request->phone2;
        $model->email = $request->email;
        $model->country = $request->country;
        $model->city = $request->city;

        $model->department = $request->department;
        $model->company_type = $request->company_type;

        $model->business = $request->business;
        $model->notes = $request->notes . ' ' . $request->email;
        if (isset($request->count_empanadas))
            $model->count_empanadas = $request->count_empanadas;
        $model->bought_products = $request->product;
        $model->cid = $request->cid;
        $model->src = $request->src;


        if (isset($request->status) && ($request->status == 'Escuela'))
            $model->status_id = 22;
        else
            $model->status_id = 1;
        $model->source_id = 0;
        if (isset($request->campaign) && ($request->campaign == 'Facebook'))
            $model->source_id = 17;
        elseif (isset($request->campaign) && ($request->campaign == 'NewJersey'))
            $model->source_id = 19;
        elseif (isset($request->campaign) && ($request->campaign == 'USA'))
            $model->source_id = 16;
        elseif (isset($request->campaign) && ($request->campaign == '500'))
            $model->source_id = 15;
        elseif (isset($request->campaign) && ($request->campaign == 'Facebook New Jersey'))
            $model->source_id = 22;
        elseif (isset($request->campaign) && ($request->campaign == 'Leads Black Friday 2018'))
            $model->source_id = 24;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Desmechadora'))
            $model->source_id = 28;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Bogota'))
            $model->source_id = 30;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Promo Navideña'))
            $model->source_id = 32;
        elseif (isset($request->platform) && ($request->platform == 'fb'))
            $model->source_id = 17;
        elseif (isset($request->platform) && ($request->platform == 'ig'))
            $model->source_id = 31;
        $model->save();
        return $model;
    }
    public function isEqual($request)
    {
        $model = Customer::where(
            // Búsqueda por...
            function ($query) use ($request) {
                if (isset($request->user_id)  && ($request->user_id != null))
                    $query = $query->where('user_id', $request->user_id);
                if (isset($request->source_id)  && ($request->source_id != null))
                    $query = $query->where('source_id', $request->source_id);
                if (isset($request->status_id)  && ($request->status_id != null))
                    $query = $query->where('status_id', $request->status_id);
                if (isset($request->business)  && ($request->business != null))
                    $query = $query->where('business', $request->business);
                if (isset($request->phone)  && ($request->phone != null))
                    $query = $query->where('phone', $request->phone);
                if (isset($request->email)  && ($request->email != null))
                    $query = $query->whereRaw('lower(email) = lower("' . $request->email . '")');
                if (isset($request->phone2)  && ($request->phone2 != null))
                    $query = $query->where('phone2', $request->phone2);
                if (isset($request->notes)  && ($request->notes != null))
                    $query = $query->where('notes', $request->notes);
                if (isset($request->city)  && ($request->city != null))
                    $query = $query->where('city', $request->city);
                if (isset($request->country)  && ($request->country != null))
                    $query = $query->where('country', $request->country);
            }
        )
            ->count();
        return $model;
    }
    public function getSimilar($request)
    {
        $model = Customer::where(
            // Búsqueda por...
            function ($query) use ($request) {
                if (isset($request->phone)  && ($request->phone != null) && ($request->phone != 'NA'))
                    $query->orwhere('phone', $request->phone);
                if (isset($request->phone)  && ($request->phone != null) && ($request->phone != 'NA'))
                    $query->orwhere('phone2', $request->phone);
                if (isset($request->phone2)  && ($request->phone2 != null) && ($request->phone != 'NA'))
                    $query->orwhere('phone', $request->phone2);
                if (isset($request->phone2)  && ($request->phone2 != null) && ($request->phone != 'NA'))
                    $query->orwhere('phone2', $request->phone2);
                if (isset($request->email)  && ($request->email != null))
                    //$query->orWhereRaw('lower(email) = lower("'.$request->email.'")');
                    $query->orWhere('email', strtolower($request->email));
                //$query->orWhereRaw('email', strtolower($request->email));
            }
        )
            ->get();
        //dd($model);
        return $model;
    }
    public function saveAPI(Request $request)
    {
        // vericamos que no se inserte 2 veces
        $count = $this->isEqual($request);
        if (is_null($count) || ($count == 0)) {
            // verificamos uno similar
            $similar = $this->getSimilar($request);
            if ($similar->count() == 0) {
                $model = $this->saveAPICustomer($request);
                $email = Email::find(1);
                $source = CustomerSource::find($model->source_id);
                Email::addEmailQueue($email, $model, 10, null);
                if (isset($source))
                    return redirect('https://maquiempanadas.com/es/' . $source->redirect_url);
                else
                    return redirect('https://maquiempanadas.com/es/gracias-web');
            }
            // este cliente ya existe. Se agrega una nueva nota
            else {
                $model = $similar[0];
                $this->storeActionAPI($request, $model->id);
                $this->updateCreateDate($request, $model->id);
                return redirect('https://maquiempanadas.com/es/gracias-web');
                //return redirect('https://maquiempanadas.com/es/gracias-web/');
                //echo "similard";
            }
        } else {
        }
    }
    public function storeActionAPI(Request $request, $customer_id)
    {
        $model = new Action;
        $str = "";
        if (isset($request->phone))
            $str .= " telefono1:" . $request->phone;
        if (isset($request->phone2))
            $str .= " telefono2:" . $request->phone2;
        if (isset($request->email))
            $str .= " email:" . $request->email;
        if (isset($request->city))
            $str .= " ciudad:" . $request->city;
        if (isset($request->country))
            $str .= " pais:" . $request->country;
        if (isset($request->name))
            $str .= " Nombre:" . $request->name;
        $model->note = $request->notes . $str;
        $model->type_id = 16; // actualización
        $model->customer_id = $customer_id;
        $model->save();
        return back();
    }
    public function updateCreateDate(Request $request, $customer_id)
    {
        $customer = Customer::find($customer_id);
        $model = new Action;
        $model->note = "se actualizó la fecha de creación " . $customer->created_at;
        $model->type_id = 16; // actualización
        $model->customer_id = $customer_id;
        $model->save();
        $mytime = Carbon\Carbon::now();
        $customer->created_at = $mytime->toDateTimeString();
        $customer->status_id = 19;
        $customer->save();
        return back();
    }
    public function storeAction(Request $request)
    {
        //dd($request);
        $date_programed = Carbon\Carbon::parse($request->date_programed);
        $today = Carbon\Carbon::now();
        //dd($request);
        $customer = Customer::find($request->customer_id);
        if (is_null($request->type_id)) {
            return back()->with('statustwo', 'El Cliente <strong>' . $customer->name . '</strong> no fue modificado!');
        }
        $model = new Action;
        if (isset($request->ActionProgrameId)) {
            $ActionProgramed = Action::find($request->ActionProgrameId);
            $ActionProgramed->delivery_date = $today;
            $ActionProgramed->save();
            $model->note = $request->note . "//" . $ActionProgramed->note;
        } else {
            $model->note = $request->note;
        }
        $model->type_id = $request->type_id;
        $model->creator_user_id = Auth::id();
        $model->customer_owner_id = $customer->user_id;
        $model->customer_createad_at = $customer->created_at;
        $model->customer_updated_at = $customer->updated_at;
        $model->customer_id = $request->customer_id;
        if ($date_programed->gt($today)) {
            $model->due_date = $date_programed;
        }
        $model->save();
        if (!is_null($request->status_id)) {
            $cHistory = new CustomerHistory;
            $cHistory->saveFromModel($customer);
            $customer->status_id = $request->status_id;
            if (Auth::id())
                $customer->updated_user_id = Auth::id();
            $customer->save();
        }
        if (!is_null($request->file)) {
            $file     = $request->file('file');
            $path = $file->getClientOriginalName();
            $destinationPath = 'public/files/' . $request->customer_id;
            $file->move($destinationPath, $path);
            $model = new CustomerFile;
            $model->customer_id = $request->customer_id;
            $model->url = $path;
            $model->save();
            return back();
        }
        return redirect('/customers/' . $request->customer_id . '/show')->with('statusone', 'El Cliente <strong>' . $customer->name . '</strong> fué modificado con éxito!');
    }
    public function saleAction(Request $request)
    {
        $model = new Action();
        $model->type_id = 27;
        $model->sale_date = $request->sale_date;
        $model->sale_amount = $request->sale_amount;
        $model->customer_id = $request->customer_id;
        $model->creator_user_id = Auth::id();
        if ($request->machine == "on") {
            $model->note = "Venta de máquina";
        } else {
            $model->note = "No es una máquina";
        }
        $model->save();
        return redirect()->back();
    }
    public function opportunityAction(Request $request)
    {
        $action = new Action;
        $action->object_id = $request->id;
        $action->type_id = 28;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $request->customer_id;
        $action->save();
        return redirect()->back();
    }
    public function poorlyRatedAction(Request $request)
    {
        $action = new Action;
        $action->object_id = $request->id;
        $action->type_id = 32;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $request->customer_id;
        $action->save();
        /*$customer = Customer::find($request->customer_id);
    $customer->status_id = 53;
    $customer->save();*/
        return redirect()->back();;
    }
    public function pqrAction(Request $request)
    {
        $model = new Action();
        $model->type_id = 29;
        $model->created_at = $request->created_at;
        $model->note = $request->note;
        $model->customer_id = $request->customer_id;
        $model->creator_user_id = Auth::id();
        $model->save();
        $customer =  Customer::find($request->customer_id);
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($customer);
        $customer->status_id = 29;
        if (Auth::id())
            $customer->updated_user_id = Auth::id();
        $customer->save();
        return redirect()->back();
    }
    public function spareAction(Request $request)
    {
        $model = new Action();
        $model->type_id = 30;
        $model->delivery_date = $request->delivery_date;
        $model->note = $request->note;
        $model->customer_id = $request->customer_id;
        $model->creator_user_id = Auth::id();
        $model->save();
        $customer =  Customer::find($request->customer_id);
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($customer);
        $customer->status_id = 46;
        if (Auth::id())
            $customer->updated_user_id = Auth::id();
        $customer->save();
        return redirect()->back();
    }
    public function enviarCorreo()
    {
        $destinatario = 'mateogiraldo420@gmail.com';
        $mensaje = 'Este es el contenido del correo';
        //Mail::to($destinatario)->send(new DemoEmail($mensaje));
        return "Correo enviado correctamente";
    }
    public function storeMail(Request $request)
    {
        $this->enviarCorreo();
        $customer = Customer::find($request->customer_id);
        $email = Email::find($request->email_id);
        $emailcontent = array(
            'subject' => $email->subject,
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $customer->id,
            'email_id' => $email->id,
            'customer_mail' => $customer->email,
            'name' => $customer->name,
        );
        Mail::send($email->view, $emailcontent, function ($message) use ($customer, $email) {
            $message->subject($email->subject);
            $message->to($customer->email);
        });
        if (filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
            // La dirección de correo electrónico es válida, puedes enviar el correo
            Mail::send($email->view, $emailcontent, function ($message) use ($customer, $email) {
                $message->subject($email->subject);
                $message->to($customer->email);
            });
        } else {
            // La dirección de correo electrónico no es válida, muestra un mensaje de error o realiza alguna otra acción
            dd("direccion invalida");
        }
        /*  
Mail::send($email->view, $emailcontent, function ($message) use ($customer, $email){
$message->subject($email->subject);
$message->to("mateogiraldo420@gmail.com");
});*/
        //Action::saveAction($customer->id,$email->id, 2);
        $action = new Action;
        $action->object_id = $email->id;
        $action->type_id = 2;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $request->customer_id;
        $action->save();
        return back();
    }
    public function change_status(Request $request)
    {
        $statuses = $this->getStatuses($request, 2);
        $model = $this->customerService->filterCustomers($request, $statuses, 1);
        //$model = $this->customerService->filterModelFull($request, $statuses);
        
        foreach ($model as $item) {
            $item->status_id = $request->modal_status_id;
            $item->save();
        }
        return redirect()->back();
    }
    public function excel(Request $request)
    {
        $name = $this->getUsers();
        $users = $this->getUsers($request);
        $customer_options = CustomerStatus::all();
        $statuses = $this->getStatuses($request, 1);

        /* obtiene una lista de clientes a partir del request */
        //$model = $this->customerService->filterModelFull($request, $statuses);
        $model = $this->customerService->filterCustomers($request, $statuses, 1);
        // dd($statuses, $model)


        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, 1);
        $sources = CustomerSource::all();

        return view('customers.excel', compact('model', 'request', 'customer_options', 'customersGroup', 'users', 'sources'));
    }
    public function contact()
    {
        $customers = DB::table('customers')
            ->join('audience_customer', 'audience_customer.customer_id', 'customers.id')
            ->join('audiences', 'audiences.id', 'audience_customer.audience_id')
            ->select('customers.*')
            ->where('audiences.id', 6)
            ->where('customers.created_at', '>', '2020-01-01')
            ->get();
        return view('users.encuesta', compact('customers'));
    }
    public function contactId($id)
    {
        $customers = Customer::find($id);
        return view('users.encuesta_id', compact('customers'));
    }
    public function savePoll($id, Request $request)
    {
        //dd($request->suggestions);
        $customer = Customer::find($id);
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->business = $request->business;
        $customer->position = $request->position;
        $customer->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->number_employees = $request->number_employees;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = $request->empanadas;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 78;
        $meta_data->value = $request->quality;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 79;
        $meta_data->value = $request->confort;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 80;
        $meta_data->value = $request->security;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 81;
        $meta_data->value = $request->delivery_time;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 82;
        $meta_data->value = $request->atention;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 83;
        $meta_data->value = $request->responsive_time_personal;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 84;
        $meta_data->value = $request->atention_technical_support;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 85;
        $meta_data->value = $request->quality_technical_support;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 86;
        $meta_data->value = $request->satisfaction_level;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 87;
        $meta_data->value = $request->recommendation;
        $meta_data->save();
        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 88;
        $meta_data->value = $request->suggestions;
        $meta_data->save();
        redirect("http://mqe.myseotest.com.co/contact");
    }
    public function storeAudience(Request $request)
    {
        $model = new AudienceCustomer;
        $model->customer_id = $request->customer_id;
        $model->audience_id = $request->audience_id;
        $model->save();
        return redirect()->back();
    }
    public function sendToRDStationFromCRM($customer)
    {
        $model = new RdStation;
        $model->setName("");
        $model->setPersonalPhone("");
        $model->setEmail("");
        $model->setCountry("");
        $model->setTrafficMedium("");
        //dd($customer);
        if (isset($customer->source_id)) {
            $customer_source = CustomerSource::find($customer->source_id);
            if ($customer_source) {
                $model->setTrafficMedium($customer_source->name);
            }
        }
        if (isset($customer->name))
            $model->setName($customer->name);
        if (isset($customer->phone))
            $model->setPersonalPhone($customer->phone);
        if (isset($customer->email))
            $model->setEmail($customer->email);
        if (isset($customer->country))
            $model->setCountry($customer->country);
        $data = array(
            'event_type' => 'CONVERSION',
            'event_family' => 'CDP',
            'payload' =>
            array(
                'conversion_identifier' => 'Crm',
                'name' => $model->getName(),
                'email' => $model->getEmail(),
                'country' => $model->getCountry(),
                'personal_phone' => $model->getPersonalPhone(),
                'mobile_phone' => $model->getPersonalPhone(),
                'traffic_source' => 'others',
                'traffic_medium' => $model->getTrafficMedium(),
                'open_country' => $model->getCountry(),
                'available_for_mailing' => true,
                'legal_bases' =>
                array(
                    0 =>
                    array(
                        'category' => 'communications',
                        'type' => 'consent',
                        'status' => 'granted',
                    ),
                ),
            ),
        );
        $model->sendFromCrm($data);
    }
    public function daily(Request $request)
    {
        $menu = $this->customerService->getUserMenu(Auth::user(1));
        $pid = 1;
        session(['stage_id' => $pid]);
        $users = $this->getUsers();
        $pid = 1;
        $customer_options = CustomerStatus::where('stage_id', $pid)->orderBy("weight", "ASC")->get();
        $statuses = $this->getStatuses($request, $pid);
        $model = $this->customerService->getModelPhase($request, $statuses, $pid);
        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $pid);
        $pending_actions = $this->getPendingActions();
        $phase = CustomerStatusPhase::find($pid);
        $sources = CustomerSource::all();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $customer = null;
        $id = 0;
        if ($model && isset($model[0])) {
            //dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            $id = $request->customer_id;
        }
        //dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $pid)->orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $model->action = "reports/views/daily_customers_followup";
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby("created_at", "DESC")->get();
        }
        return view('customers.daily', compact('model', 'request', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu'));
    }

    public function startConversationFromCRM(Request $request)
    {

//        dd($request->all());
        $customer = Customer::findOrFail($request->customer_id);
        //$customerUser = $customer->getChatUser(); // el User equivalente al Customer
        $waUser = User::find(1); // Usuario con WA Toolbox activo
        
        $adminUser = User::find(Auth::id());
        // Usuario logueado en el CRM
    
        // Crear conversación o usar existente
        $conversation = $waUser->createConversationWith($customer);

        // Asegurarse de que el admin también esté en la conversación
        if (!$adminUser->belongsToConversation($conversation)) {
            $conversation->addParticipant($adminUser);
        }
    
        // Enviar mensaje como WAUser
        $message = $waUser->sendMessageTo($customer, $request->mensaje);
    
        // Redirigir al chat en una nueva pestaña
        $chatUrl = url("/chats/{$conversation->id}");
    
        return response()->json([
            'success' => true,
            'chat_url' => $chatUrl,
            'conversation_id' => $conversation->id,
        ]);
    }
    

}
