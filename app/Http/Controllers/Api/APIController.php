<?php
//MQE
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Mail;

use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\User;
use App\Models\CustomerSource;
use App\Models\CustomerHistory;
use App\Models\Campaign;
// use App\Models\EmployeeStatus;
// use App\Models\Mail;
use App\Models\Action;
use App\Models\Email;
use App\Models\ActionType;
use Auth;
use Carbon;
use App\Models\Product;
use App\Models\Order;
use App\Models\Country;
use App\Models\AudienceCustomer;
use App\Models\MessageSource;
use App\Models\Quote;
use App\Models\Session;
use App\Models\Reference;
use App\Models\RdStation;
use Illuminate\Support\Facades\Http;
use App\Models\RequestLog;
use App\Services\WAToolboxService;

class APIController extends Controller
{

    protected $attributes = ['status_name'];
    protected $appends = ['status_name'];
    protected $status_name;

    protected $waToolboxService;
    protected $defaultMessageSource;

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        return $this->customers($request);
    }

    public function getPendingActions()
    {
        $model = Action::whereNotNull('due_date')
            ->whereNull('delivery_date')
            ->where('creator_user_id', "=", Auth::id())
            ->get();

        return $model;
    }

    public function customers(Request $request)
    {
        $users = $this->getUsers();
        $customer_options = CustomerStatus::all();
        $statuses = $this->getStatuses($request, 1);

        $model = $this->getModel($request, $statuses, 'customers');
        $customersGroup = $this->countFilterCustomers($request, $statuses);

        $sources = CustomerSource::all();

        $pending_actions = $this->getPendingActions();

        return view('customers.index', compact('model', 'request', 'customer_options', 'customersGroup', 'query', 'users', 'sources', 'pending_actions'));
    }

    public function leads(Request $request)
    {
        $users = $this->getUsers();
        $customer_options = CustomerStatus::all();
        $statuses = $this->getStatuses($request, 1);
        $model = $this->getModel($request, $statuses, 'leads');
        $customersGroup = $this->countFilterCustomers($request, $statuses);

        $pending_actions = $this->getPendingActions();




        $sources = CustomerSource::all();


        return view('customers.index', compact('model', 'request', 'customer_options', 'customersGroup', 'query', 'users', 'sources', 'pending_actions'));
    }

    public function getModel(Request $request, $statuses, $action)
    {
        $model = $this->filterModel($request, $statuses);


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
        $statuses;
        if (isset($request->from_date) || ($request->from_date != ""))
            $statuses = $this->getAllStatusID();
        else
            $statuses = $this->getStatusID($request, $step);
        return $statuses;
    }







    public function filterModel(Request $request, $statuses)
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
                    $query = $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery = $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.status_temp', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                        }
                    );
                }
            }


        )
            ->orderBy('customers.status_id', 'asc')
            ->orderBy('customers.created_at', 'desc')
            ->paginate();

        return $model;
    }

    public function filterModelFull(Request $request, $statuses)
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
                    $query = $query->orwhere('customers.country', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                    $query = $query->orwhere('customers.status_temp', "like", "%" . $request->search . "%");
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
                    $query = $query->orwhere('customers.status_temp', "like", "%" . $request->search . "%");
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

    public function countFilterCustomers($request, $statuses)
    {
        //$customersGroup = Customer::wherein('customers.status_id', $statuses)

        $customersGroup = Customer::wherein('customers.status_id', $statuses)
            ->rightJoin("customer_statuses", 'customers.status_id', '=', 'customer_statuses.id')
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
                    $query = $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery = $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.status_temp', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                            $innerQuery = $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                        }
                    );
                }
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



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $customers_statuses = CustomerStatus::all();
        $customer_sources = CustomerSource::all();
        return view('customers.create', compact('customers_statuses', 'users', 'customer_sources', 'customersGroup'));
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
        $model->status_id = $request->status_id;
        $model->user_id = $request->user_id;
        $model->source_id = $request->source_id;
        $model->technical_visit = $request->technical_visit;

        //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;

        if (Auth::id())
            $model->updated_user_id = Auth::id();

        if ($model->save()) {
            //$this->sendMail(1, $model);
            return redirect('customers')->with('status', 'El Cliente <strong>' . $model->name . '</strong> fué añadido con éxito!');
        }
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
        $actions = Action::where('customer_id', '=', $id)->orderby("created_at", "DESC")->get();
        $action_options = ActionType::all();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::all();
        $statuses_options = CustomerStatus::orderBy("weight", "ASC")->get();
        $actual = true;
        $today = Carbon\Carbon::now();

        return view('customers.show', compact('model', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today'));
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
    public function edit($id)
    {
        $model = Customer::find($id);
        $customer_statuses = CustomerStatus::orderBy("weight", "ASC")->get();
        $customer_sources = CustomerSource::all();
        $users = User::all();

        return view('customers.edit', compact('model', 'customer_statuses', 'customersGroup', 'users', 'customer_sources'));
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
        $model->user_id = $request->user_id;
        $model->source_id = $request->source_id;
        $model->status_id = $request->status_id;

        //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;

        if (Auth::id())
            $model->updated_user_id = Auth::id();

        if ($model->save()) {
            return redirect('customers/' . $model->id . '/show')->with('statusone', 'El Cliente <strong>' . $model->name . '</strong> fué modificado con éxito!');
        }
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

            $model = CustomerStatus::where("stage_id", $stage_id)
                ->orderBy("weight", "ASC")
                ->get();
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



    function getAllStatusID()
    {

        $res = array();
        $model = CustomerStatus::orderBy("weight", "ASC")->get();
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

    public function getSource($request)
    {
        $source_id = 10; //Nuevo
        if (isset($request->source_id))
            $source_id = $request->source_id;
        else
            $source_id = 10;

        if (isset($request->campaign) && ($request->campaign == 'Facebook'))
            $source_id = 17;
        elseif (isset($request->campaign) && ($request->campaign == 'NewJersey'))
            $source_id = 19;
        elseif (isset($request->campaign) && ($request->campaign == 'USA'))
            $source_id = 16;
        elseif (isset($request->campaign) && ($request->campaign == '500'))
            $source_id = 15;
        elseif (isset($request->campaign) && ($request->campaign == 'Facebook New Jersey'))
            $source_id = 22;
        elseif (isset($request->campaign) && ($request->campaign == 'Leads Black Friday 2018'))
            $source_id = 24;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Desmechadora'))
            $source_id = 28;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Bogota'))
            $source_id = 30;
        elseif (isset($request->campaign) && ($request->campaign == 'Landing Promo Navideña'))
            $source_id = 32;


        elseif (isset($request->platform) && ($request->platform == 'fb'))
            $source_id = 17;
        elseif (isset($request->platform) && ($request->platform == 'ig'))
            $source_id = 31;
        return $source_id;
    }


    public function saveAPICustomer($request)
    {
        $model = new Customer;

        if (isset($request->product_id)) $model->name = $request->name;
        if (isset($request->phone)) $model->phone = $request->phone;
        if (isset($request->phone2)) $model->phone2 = $request->phone2;
        if (isset($request->email)) $model->email = $request->email;
        if (isset($request->country)) $model->country = $request->country;
        if (isset($request->city)) $model->city = $request->city;
        if (isset($request->utm_source)) $model->utm_source = $request->utm_source;
        if (isset($request->utm_medium)) $model->utm_medium = $request->utm_medium;
        if (isset($request->utm_campaign)) $model->utm_campaign = $request->utm_campaign;
        if (isset($request->utm_content)) $model->utm_content = $request->utm_content;
        if (isset($request->utm_term)) $model->utm_term = $request->utm_term;

        if (isset($request->product_id))
            $model->product_id = $request->product_id;
        if (isset($request->technical_visit))
            $model->technical_visit = $request->technical_visit;
        if (isset($request->name)) {
            $model->name = $request->name;
            $model->save();
        }



        if (isset($request->count_empanadas))
            $model->count_empanadas = $request->count_empanadas;

        if (isset($request->product))
        $model->bought_products = $request->product;
        
        if(isset($request->cid))
        $model->cid = $request->cid;

        if (isset($request->src))
        $model->src = $request->src;

        if (isset($request->department))
        $model->department = $request->department;

        if (isset($request->rd_id))
            $model->cid = $request->rd_id;
        if (isset($request->score))
            $model->score = $request->score;

        if (isset($request->status) && ($request->source == "Maquiempanadas - MQE_Form leads desmechadora")) {
            $model->status_id = 41;
        } else {
            $model->status_id = 1;
        }

        if (isset($request->notes))
        $model->notes .= $request->notes . ' ' . $request->email;

        if (isset($request->session_id))
            $model->session_id = $request->session_id;

        $model->source_id = $this->getSource($request);

        if (isset($request->utm_source)) {
            $model->utm_source = $request->utm_source;
        }
        if (isset($request->utm_medium)) {
            $model->utm_medium = $request->utm_medium;
        }
        if (isset($request->utm_campaign)) {
            $model->utm_campaign = $request->utm_campaign;
        }
        if (isset($request->utm_content)) {
            $model->utm_content = $request->utm_content;
        }
        if (isset($request->utm_term)) {
            $model->utm_term = $request->utm_term;
        }


        /*
        if($model->source_id == 26){
            if(isset($request->utm_source)){
                $model->utm_source = $request->utm_source;
            }
            if(isset($request->utm_medium)){
                 $model->utm_medium = $request->utm_medium;
            }
            if(isset($request->utm_campaign)){
                 $model->utm_campaign = $request->utm_campaign;
            }
            if(isset($request->utm_content)){
                 $model->utm_content = $request->utm_content;
            }
            if(isset($request->utm_adset_name)){
                 $model->utm_adset_name = $request->utm_adset_name;
            }
        }
        */




        $model->save();
        
        return $model;
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

    public function customerSourceToActionType($source_id)
    {
        $action_id = 0;
        switch ($source_id) {
            case 10: //  Web
                $action_id = 3;
                break;
            case 12: // Whatsapp
                $action_id = 8;
                break;
            case 15: // Calculadora Web
                $action_id = 3;
                break;
            case 26: // Whatsapp
                $action_id = 8;
                break;
            case 33: // redes sociales
                $action_id = 6;
                break;
            case 6: // fb lead
                $action_id = 6;
                break;
            case 39: // fb Messenger
                $action_id = 6;
                break;
            case 40: // ínstagram
                $action_id = 7;
                break;
            case 41: // chat bot
                $action_id = 6;
                break;
        }
        return $action_id;
    }

    public function isEqualModel($request)
    {
        //dd($request);
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

                if (isset($request->fit_score)  && ($request->fit_score != null))
                    $query = $query->where('scoring_profile', $request->fit_score);

                if (isset($request->interest)  && ($request->interest != null))
                    $query = $query->where('scoring_interest', $request->interest);
            }
        )
            ->first();
        return $model;
    }

    public function isEqual($request)
    {
        //dd($request);
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
    /*
    public function getSimilar($request){
        $model = Customer::where(
                // Búsqueda por...
                 function ($query) use ($request) {
                    if(isset($request->phone)  && ($request->phone!=null) && ($request->phone!='NA'))
                        $query->orwhere('phone', $request->phone);
                    
                    if(isset($request->phone)  && ($request->phone!=null) && ($request->phone!='NA'))
                        $query->orwhere('phone2', $request->phone);

                    if(isset($request->phone2)  && ($request->phone2!=null) && ($request->phone!='NA'))
                        $query->orwhere('phone', $request->phone2);

                    if(isset($request->phone2)  && ($request->phone2!=null) && ($request->phone!='NA'))
                        $query->orwhere('phone2', $request->phone2);

                    if(isset($request->email)  && ($request->email!=null))
                       $query->orWhere('email', strtolower($request->email));
                 
                })->get();
            //dd($model);
        return $model;
    }
    */
    // Función para normalizar números de teléfono
    function normalizePhoneNumber($phoneNumber)
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }





    function looksLikePhoneNumber($input)
    {
        // Esta expresión regular es más flexible y debería coincidir con una variedad
        // más amplia de formatos de números de teléfono.
        return preg_match('/^\+?\d{1,4}(\s|-)?(\d{1,4}(\s|-)?){1,4}$/', $input);
    }

    public function getSimilar($request)
    {
        $query = Customer::query();
        $normalizedSearch = "";


        if ($request->phone && $request->phone != 'NA') {
            $query->orWhere('phone', $request->phone)
                ->orWhere('phone2', $request->phone);
            $normalizedSearch = $this->normalizePhoneNumber($request->phone);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
        }

        if ($request->phone2 && $request->phone2 != 'NA') {
            $query->orWhere('phone', $request->phone2)
                ->orWhere('phone2', $request->phone2);
            $normalizedSearch = $this->normalizePhoneNumber($request->phone);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
            $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
        }

        if ($request->email) {
            $query->orWhere('email', strtolower($request->email));
        }

        return $query->get();
    }


    public function getSimilarModelOld($request)
    {
        $model = Customer::where(
            // Búsqueda por...
            function ($query) use ($request) {
                /*
            if(isset($request->phone)  && ($request->phone!=null) && ($request->phone!='NA'))
                $query->orwhere('phone', $request->phone);
            
            if(isset($request->phone)  && ($request->phone!=null) && ($request->phone!='NA'))
                $query->orwhere('phone2', $request->phone);

            if(isset($request->phone2)  && ($request->phone2!=null) && ($request->phone!='NA'))
                $query->orwhere('phone', $request->phone2);

            if(isset($request->phone2)  && ($request->phone2!=null) && ($request->phone!='NA'))
                $query->orwhere('phone2', $request->phone2);

*/
                $normalizedSearch = "";

                if ($request->phone && $request->phone != 'NA') {
                    $query->orWhere('phone', $request->phone)
                        ->orWhere('phone2', $request->phone);
                    $normalizedSearch = $this->normalizePhoneNumber($request->phone);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                }

                if ($request->phone2 && $request->phone2 != 'NA') {
                    $query->orWhere('phone', $request->phone2)
                        ->orWhere('phone2', $request->phone2);
                    $normalizedSearch = $this->normalizePhoneNumber($request->phone);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    $query->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                }

                if (isset($request->email)  && ($request->email != null))
                    $query->orWhere('email', strtolower($request->email));
            }
        )->first();
        //dd($model);
        return $model;
    }

    public function getSimilarModel($request)
    {
        $model = Customer::where(function ($query) use ($request) {
            // Verificación y normalización de phone
            if (!empty($request->phone) && $request->phone != 'NA') {
                $normalizedPhone = $this->normalizePhoneNumber($request->phone);
                $query->where(function ($q) use ($normalizedPhone) {
                    $q->orWhere('phone', $normalizedPhone)
                        ->orWhere('phone2', $normalizedPhone)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone%"])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone%"])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone%"]);
                });
            }

            // Verificación y normalización de phone2
            if (!empty($request->phone2) && $request->phone2 != 'NA') {
                $normalizedPhone2 = $this->normalizePhoneNumber($request->phone2);
                $query->where(function ($q) use ($normalizedPhone2) {
                    $q->orWhere('phone', $normalizedPhone2)
                        ->orWhere('phone2', $normalizedPhone2)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone2%"])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone2%"])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedPhone2%"]);
                });
            }

            // Verificación de email
            if (!empty($request->email)) {
                $query->orWhere('email', strtolower($request->email));
            }
        })->first();

        return $model;
    }


    public function getSourceId($rd_source)
    {
        $model = CustomerSource::where('rd_source', $rd_source)
            ->whereNotNull('rd_source')
            ->first();

        if (!$model) {
            $model = new CustomerSource;
            $model->name = $rd_source;
            $model->rd_source = $rd_source;

            $model->save();
        }

        return $model->id;
    }

    public function phoneToCountry($phone)
    {
        $start = strpos($phone, "+");
        $end = strpos($phone, " ");

        $code = substr($phone, $start + 1, $end - $end);
        $model = Country::where('phone_code', $code)->first();

        $str = "";
        if ($model)
            $str = $model->name;

        return $str;
    }

    public function saveFromRD(Request $request)
    {
        $request->source_id = $this->getSourceId($request->event);
        $request->notes .= $request->event;
        //$request->request = $request->all()."RD";

        if (isset($request->phone) && ($request->phone == "" || is_null($request->phone)))
            if (isset($request->phone2) && ($request->phone2 != "" && !is_null($request->phone2))) {
                $request->phone = $request->phone2;
                $request->phone2 = "";
            }


        if (isset($request->country) && ($request->country == "" || is_null($request->country))) {
            $request->country = $this->phoneToCountry($request->phone);
        }

        // vericamos si existe uno igual en la base de datos
        $this->saveAPI($request);
    }

    public function saveRDSationCustomer($model)
    {

        $url = "https://hooks.zapier.com/hooks/catch/2377806/oxuu5vx/";

        $method = 'GET'; //change to 'POST' for post method
        $data = array(
            'name' => $model->name,
            'phone' => $model->phone,
            'country' => $model->country,
            'email' => $model->email,
            'product_id' => $model->product_id,
            'source_id' => $model->source_id
        );

        if ($method == 'POST') {
            //Make POST request
            $data = http_build_query($data);
            $context = stream_context_create(
                array(
                    'http' => array(
                        'method' => "$method",
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $data
                    )
                )
            );
            $response = file_get_contents($url, false, $context);
        } else {
            // Make GET request
            $data = http_build_query($data, '', '&');
            $response = file_get_contents($url . "?" . $data, false);
        }

        return $response;
    }

    public function saveAPI(Request $request)
    {
        $model = null;
        $this->saveLogFromRequest($request);
        // vericamos que no se inserte 2 veces
       
        $count = $this->isEqual($request);
        
        $similar = $this->getSimilar($request);

        //dd($similar);

        if (is_null($count) || ($count == 0)) {
            // verificamos uno similar


            if ($similar->count() == 0) {
                $model = $this->saveAPICustomer($request);
                $this->storeActionAPI($request, $model->id);

                if ($model->source_id == 23) {

                    $model->rd_station_response = $this->saveRDSationCustomer($model);
                    $model->save();
                }
                $email = Email::find(1);
                $source = CustomerSource::find($model->source_id);
                if ($request->product_id == 3 || $model->source_id == 28) {
                    //Email::addEmailQueue($email, $model, 10, null);
                    return $this->redirectingDesmechadora();
                } else {

                    if (isset($source)) {

                        if ($source->id == 26) { //Sitio Web - WhatsApp Manual
                            return redirect('https://maquiempanadas.com/es/gracias-web');
                        }
                        return redirect('https://maquiempanadas.com/es/' . $source->redirect_url);
                    } else {
                        return redirect('https://maquiempanadas.com/es/gracias-web');
                    }
                }
            } else {

                $model = $similar[0];

         

                $this->storeActionAPI($request, $model->id);
                $this->updateCreateDate($request, $model->id);
                
            }
            // este cliente ya existe. Se agrega una nueva nota
            //else{


            //$this->updateCreateDate($request, $model->id);
            // return redirect('https://maquiempanadas.com/es/gracias-web');
            //return redirect('https://maquiempanadas.com/es/gracias-web/');
            //echo "similard";


            //}
        } else {
            $model = $similar[0];
            if ($model && $model->status_id == 1) { //miro si está nuevo


                $cHistory = new CustomerHistory;
                $cHistory->saveFromModel($model);
                $model->status_id  = 28; // se cambia a seguimiento 1
                $model->save();
            }

            if (($model->name == null) && (isset($request->name))) {
                $model->name = $request->name;
                $model->save();
            }




            $this->storeActionAPI($request, $similar[0]->id);


            if (isset($request->session_id)) {

                $model = Customer::where("email", $request->email)->first();
                
            }
            return redirect('https://maquiempanadas.com/es/gracias-web/');
        }
    }


    public function saveAPIWatoolbox(Request $request)
    {
        $this->saveLogFromRequest($request);
        // vericamos que no se inserte 2 veces
        $equal = $this->isEqual($request);
        if (is_null($equal) || ($equal == 0)) {
            if (isset($request->maker)) {
                $equal->maker = $request->maker;
                $equal->save();
            }
            $this->storeActionAPI($request, $equal->id);
        } else {
            $similar = $this->getSimilar($request);


            if ($similar->count() != 0) {
                $model = $similar[0];
                if (isset($request->maker)) {
                    $model->maker = $request->maker;
                    $model->save();
                }
                $this->storeActionAPI($request, $model->id);
            }
        }
    }






    public function getProduct($pid)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        $id = $pid;
        if ($pid == "cm06") {
            $id = 6;
        } else if ($pid == "cm07") {
            $id = 8;
        } else if ($pid == "cm08") {
            $id = 10;
        } else if ($pid == "cm05s") {
            $id = 11;
        } else if ($pid == "cm05c") {
            $id = 12;
        } else if ($pid == "cm06b") {
            $id = 7;
        } else if ($id == "cho") { //chocotera
            $id = 23;
        } else if ($id == "MOLDES-NAC") { //chocotera
            $id = 141;
        } else if ($id == "CANASTA") { //CANASTAs
            $id = 25;
        } else if ($id == "JUEGOCAN") { //CANASTAS CON MANGO x 2
            $id = 24;
        } else if ($id == "mez-var") { //MEZCLADORA CON VARIADOR
            $id = 19;
        } else if ($id == "mes") { //MESAS
            $id = 18;
        } else if ($id == "CE04CV-N") { //LAMINADORA VARIADOR
            $id = 17;
        } else if ($id == "CE02-N") { //LAMINADORA
            $id = 16;
        } else if ($id == "des") { //DESMECHADORA
            $id = 15;
        } else if ($id == "conx3") { //CONOS MANGO x 3
            $id = 14;
        } else if ($id == "con") { //CONOS
            $id = 13;
        } else if ($id == "tab-pic") { //TABLA DE PICAR
            $id = 22;
        } else if ($id == "BARRIL") { //BARRIL AZADOR Nuevo
            $id = 123;
        } else if ($id == "esc-mai") { //ESCUELA MAIZ
            $id = 26;
        } else if ($id == "esc-tri") { //ESCUELA TRIGO
            $id = 27;
        } else if ($id == "ALMIDON_GEL") { //ALMIDON 2 KILOS EN ADELANTE
            $id = 120;
        } else if ($id == "ALMIDON_BUL") { //ALMIDON BULTO 25 KILOS
            $id = 121;
        } else if ($id == "CEREAL-CERE") { //CEREAL BULTO NACIONAL
            $id = 124;
        } else if ($id == "TICALOID_10") { //GOMA 1 KILO
            $id = 128;
        } else if ($id == "TABLAMOLDES") { //TABLA CON 3 MOLDES Y RODILLO
            $id = 149;
        } else if ($id == "MOLDES-NAC-CIR") { //Molde circular para empanadas de maíz, morocho y verde
            $id = 157;
        } else if ($id == "MOLDES-NAC-DOB") { //Molde circular para empanadas de maíz, morocho y verde
            $id = 158;
        } else if ($id == "MOLDES-NAC-COT-UNI") { //Molde coctelera única para empanadas de maíz, morocho y verde
            $id = 159;
        } else if ($id == "MOLDES-NAC-REC") { //Molde rectangular para empanadas de maíz, morocho y verde
            $id = 160;
        } else if ($id == "MOLDES-NAC-TRI") { //Molde rectangular para empanadas de maíz, morocho y verde
            $id = 161;
        }


        $model = Product::find($id);
        return $model;
    }

    public function getProducts()
    {
        $products = Product::where('active', 1)
            ->where('colombia_price', '>', 0)->get();
        return $products;
    }




    public function saveAPICheckout(Request $request)
    {
        $model = new Customer;

        // vericamos que no se inserte 2 veces
        $count = $this->isEqual($request);

        if (is_null($count) || ($count == 0)) {
            // verificamos uno similar
            $similar = $this->getSimilar($request);

            if ($similar->count() == 0) {
                $model = $this->saveAPICustomer($request);
            } else {
                $model = $similar[0];
                $this->storeActionAPI($request, $model->id);
            }
        } else {
            $similar = $this->getSimilar($request);
            $model = $similar[0];
        }

        /// crear orden
        $order = new Order;
        $order->customer_id = $model->id;
        //dd($request);

        $id = $request->product_id;
        if ($id == "cm06") {
            $id = 6;
        } else if ($id == "cm07") {
            $id = 8;
        } else if ($id == "cm08") {
            $id = 10;
        } else if ($id == "cm05s") {
            $id = 11;
        } else if ($id == "cm05c") {
            $id = 12;
        } else if ($id == "cm06B") {
            $id = 7;
        } else if ($id == "cm06b") {
            $id = 7;
        } else if ($id == "cho") { //chocotera
            $id = 23;
        } else if ($id == "MOLDES-NAC") { //chocotera
            $id = 141;
        } else if ($id == "CANASTA") { //CANASTAs
            $id = 25;
        } else if ($id == "JUEGOCAN") { //CANASTAS CON MANGO x 2
            $id = 24;
        } else if ($id == "mez-var") { //MEZCLADORA CON VARIADOR
            $id = 19;
        } else if ($id == "mes") { //MESAS
            $id = 18;
        } else if ($id == "CE04CV-N") { //LAMINADORA VARIADOR
            $id = 17;
        } else if ($id == "CE02-N") { //LAMINADORA
            $id = 16;
        } else if ($id == "des") { //DESMECHADORA
            $id = 15;
        } else if ($id == "conx3") { //CONOS MANGO x 3
            $id = 14;
        } else if ($id == "con") { //CONOS
            $id = 13;
        } else if ($id == "tab-pic") { //TABLA DE PICAR
            $id = 22;
        } else if ($id == "BARRIL") { //BARRIL AZADOR Nuevo
            $id = 123;
        } else if ($id == "esc-mai") { //ESCUELA MAIZ
            $id = 26;
        } else if ($id == "esc-tri") { //ESCUELA TRIGO
            $id = 27;
        } else if ($id == "ALMIDON_GEL") { //ALMIDON 2 KILOS EN ADELANTE
            $id = 120;
        } else if ($id == "ALMIDON_BUL") { //ALMIDON BULTO 25 KILOS
            $id = 121;
        } else if ($id == "CEREAL-CERE") { //CEREAL BULTO NACIONAL
            $id = 124;
        } else if ($id == "TICALOID_10") { //GOMA 1 KILO
            $id = 128;
        } else if ($id == "TABLAMOLDES") { //TABLA CON 3 MOLDES Y RODILLO
            $id = 149;
        } else if ($id == "MOLDES-NAC-CIR") { //Molde circular para empanadas de maíz, morocho y verde
            $id = 157;
        } else if ($id == "MOLDES-NAC-DOB") { //Molde circular para empanadas de maíz, morocho y verde
            $id = 158;
        } else if ($id == "MOLDES-NAC-COT-UNI") { //Molde coctelera única para empanadas de maíz, morocho y verde
            $id = 159;
        } else if ($id == "MOLDES-NAC-REC") { //Molde rectangular para empanadas de maíz, morocho y verde
            $id = 160;
        } else if ($id == "MOLDES-NAC-TRI") { //Molde rectangular para empanadas de maíz, morocho y verde
            $id = 161;
        }



        $order->product_id = $id;
        $mytime = Carbon\Carbon::now();
        $order->added_at = $mytime->toDateTimeString();

        $order->save();

        return "$order";
    }


    public function redirectingDesmechadora()
    {
        return redirect('https://maquiempanadas.com/gracias-desmechadora/');
    }

    public function getActionType($request)
    {
        $id = 3;

        if (isset($request->platform) && ($request->platform == 'fb'))
            $id = 6;
        elseif (isset($request->platform) && ($request->platform == 'ig'))
            $id = 7;
        return $id;
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

        $model->type_id = 16; // actualización 
        if (isset($request->content)) {
            $model->type_id = 8; // WhatsApp de entrada
            $model->creator_user_id = 96;
            if (isset($request->type_id) && ($request->type_id != "")) {
                $model->type_id = $request->type_id;
                $model->creator_user_id = $request->creator_user_id;
            }
            $str .= "WP Mensaje:" . $request->content;
        }

        $model->note .= $str;


        $model->customer_id = $customer_id;

        $model->save();

        return back();
    }



    public function updateCreateDate(Request $request, $customer_id)
    {

        $customer = Customer::find($customer_id);


        $model = new Action;
        $model->note .= "se actualizó la fecha de creación " . $customer->created_at;
        $model->type_id = 16; // actualización
        $model->customer_id = $customer_id;
        $model->save();


        $mytime = Carbon\Carbon::now();
        $customer->created_at = $mytime->toDateTimeString();
        $customer->status_id = 63; // pendiente

        // ajusto la nota
        if (isset($request->notes))
            $customer->notes .= $request->notes;
        $customer->save();


        return back();
    }


    public function storeAction(Request $request)
    {
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

        return redirect('/customers/' . $request->customer_id . '/show')->with('statusone', 'El Cliente <strong>' . $customer->name . '</strong> fué modificado con éxito!');
    }

    public function storeMail(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        $email = Email::find($request->email_id);
        $emailcontent = array(
            'subject' => $email->subject,
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $customer->id,
            'email_id' => $email->id
        );

        Mail::send($email->view, $emailcontent, function ($message) use ($customer, $email) {
            $message->subject($email->subject);
            $message->to($customer->email);
        });
        //Action::saveAction($customer->id,$email->id, 2);
        $action = new Action;
        $action->object_id = $email->id;
        $action->type_id = 2;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $request->customer_id;
        $action->save();
        return back();
    }

    public function excel(Request $request)
    {
        $users = $this->getUsers();
        $customer_options = CustomerStatus::all();
        $statuses = $this->getStatuses($request, 2);

        $model = $this->filterModelFull($request, $statuses);
        $customersGroup = $this->countFilterCustomers($request, $statuses);

        $sources = CustomerSource::all();

        return view('customers.excel', compact('model', 'request', 'customer_options', 'customersGroup', 'query', 'users', 'sources'));
    }

    


    public function trackWPAction($cid,  $aid, $tid, $msg,  Request $request)
    {
        $msg = urldecode($msg);
        $model = Customer::find($cid);

        if ($model && $model->status_id == 1) {
            $model->status_id  = 28;
            $model->save();
        }
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($model);

        $cAudience = AudienceCustomer::where('audience_id', $aid)
            ->where('customer_id', $cid)
            ->first();
        $cAudience->sended_at = Carbon\Carbon::now();
        if ($cAudience->save()) {
            echo "guardado";
        }
        $this->saveAction($cid, null, $tid, $msg);
    }


    public  function saveAction($cid, $oid, $tid, $str)
    {
        $model = new Action;
        $model->customer_id = $cid;
        $model->object_id = $oid;
        $model->type_id = $tid;
        $model->note = $str;
        $model->creator_user_id = Auth::id();
        if (is_null(Auth::id())) {
            $model->creator_user_id = 8;
        }

        date_default_timezone_set('America/Bogota');
        $date = date('Y-m-d H:i:s');
        $model->delivery_date = $date;
        $model->save();

        /*
        $customer = Customer::find($cid);
        $customer->status_id = 28;//Seguimiento
        $customer->save();
        */
    }




    public function getHistory($cid)
    {
        $histories = CustomerHistory::where('customer_id', '=', $cid)->get();
        return $histories;
    }
    public function getAction($aid)
    {
        $actions = Action::where('customer_id', '=', $aid)->orderby("created_at", "DESC")->get();
        return $actions;
    }

    public function saveFromRD2(Request $request)
    {
        $url = 'https://hooks.zapier.com/hooks/catch/4539341/opzpcqt/'; // add your Zapier webhook url 
        $json = json_encode($_POST);
        $headers = array('Accept: application/json', 'Content-Type: application/json');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        echo $output;
        curl_close($ch);
    }



    public function getCountry($data)
    {
        $str = "";
        if (isset($data["first_conversion"]["content"]["País"])) {
            $str = $data["first_conversion"]["content"]["País"];
        }
        if (isset($data['first_conversion']['content']['País_'])) {
            $str = $data['first_conversion']['content']['País_'];
        }
        if (isset($data['first_conversion']['content']['Pais'])) {
            $str = $data['first_conversion']['content']['Pais'];
        }
        if (isset($data['custom_fields']['Pais'])) {
            $str = $data['custom_fields']['Pais'];
        }
        if (isset($data["custom_fields"]["País_"])) {
            $str = $data["custom_fields"]["País_"];
        }

        return $str;
    }

    public function saveLogFromRequest(Request $request)
    {
        $model = new RequestLog();

        // Verificar si la solicitud es JSON
        if ($request->isJson()) {
            $requestData = $request->json()->all();
        } else {
            // Si no es JSON, obtener todos los datos de la solicitud
            $requestData = $request->all();
        }

        // Guardar la solicitud como JSON
        $model->request = json_encode($requestData);
        $model->save();
    }


    private function mapDataToCustomer(Customer $model, array $data): void
    {
        $map = [
            'name'            => 'name',
            'email'           => 'email',
            'public_url'      => 'rd_public_url',
            'personal_phone'  => 'phone',
            'mobile_phone'    => 'phone2',
            'state'           => 'department',
            'city'            => 'city',
            'company'         => 'business',
            'job_title'       => 'position',
            'fit_score'       => 'scoring_profile',
            'interest'        => 'scoring_interest',
        ];

        foreach ($map as $key => $attribute) {
            if (isset($data[$key])) {
                $model->{$attribute} = $data[$key];
            }
        }

        if (isset($data['bio'])) {
            $model->notes .= $data['bio'];
        }
    }


    public function updateFromRD(Request $request)
    {


        $this->saveLogFromRequest($request);

        $json = $request->json()->all();
        
        $data = "";
        if (isset($json["leads"]))
            $data = $json["leads"][0];

        $tags = "";

        if (isset($data["tags"]))
            $tags = $data["tags"];

        $model = new Customer;


        $status_id = 0;
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (!str_contains($model->notes, "#$tag")) {
                    $model->notes .= " #$tag";
                }

                switch ($tag) {
                    case 'Tour_Bogota':
                        if (!str_contains($model->notes, '#BogotaTour2025')) {
                            $model->notes .= ' #BogotaTour2025';
                        }
                        break;

                    case 'pqr':
                        $model->status_id = 29;
                        $status_id = 29;
                        break;

                    case 'desmechadora':
                        $model->status_id = 41;
                        break;
                }
            }
        }

        
        $lead = $json["leads"][0]; // Toma el primer lead

        // Verifica si 'first_conversion', 'content' y 'note' están presentes
        if (isset($lead["first_conversion"]) && isset($lead["first_conversion"]["content"]) && isset($lead["first_conversion"]["content"]["note"])) {
            $note = $lead["first_conversion"]["content"]["note"];
            $model->notes .= $note; // Agrega la nota a las notas del modelo

            // Verifica si la nota es una de las fechas especificadas
            if ("22_de_noviembre" == $note || "23_de_noviembre" == $note) {
                $model->notes .= " #madrid2023";
            }
        }

        //dd($request->json());
        $model->request = json_encode($json);
        //$model->save();

        $opportunity = "";
        if (isset($data["opportunity"])) {
            $opportunity = $data["opportunity"];
        }
        

        if (isset($request->campaign) && ($request->campaign != ""))
            if (($request->campaign == "Maquiempanadas - MQE_Form leads desmechadora")) {
                $model->status_id = 41; //Desmechadora
            }
        if (isset($data["campaign"]) && ($request->campaign != ""))
            if ($data["campaign"] == "Maquiempanadas - MQE_Form leads desmechadora") {
                $model->status_id = 41;
            }
        // Verifica si 'desmechadora' está en el identificador de la primera o última conversión
        $firstConversionIdentifier = $data["first_conversion"]["content"]["identificador"] ?? '';
        $lastConversionIdentifier = $data["last_conversion"]["content"]["identificador"] ?? '';

        if (str_contains($firstConversionIdentifier, "desmechadora") || str_contains($lastConversionIdentifier, "desmechadora")) {
            $model->status_id = 41; // Desmechadora
            $model->inquiry_product_id = 15; // desmechadora
        }


        // Extrae información de la conversión original
        $firstConversionCampaign = $lead["first_conversion"]["conversion_origin"]["campaign"] ?? '';
        $lastConversionCampaign = $lead["last_conversion"]["conversion_origin"]["campaign"] ?? '';

        // Asigna el valor a $model->campaign_name si alguno de los campos está presente
        if ($firstConversionCampaign) {
            $model->campaign_name = $firstConversionCampaign;
        } elseif ($lastConversionCampaign) {
            $model->campaign_name = $lastConversionCampaign;
        }


        $this->mapDataToCustomer($model, $data);




        if ($this->getCountry($data) != "") {
            $model->country = $this->getCountry($data);
            //dd($model->country);
        }



        if (isset($data["custom_fields"]["Producción diaria de empanadas"])) {
            $model->count_empanadas = $data["custom_fields"]["Producción diaria de empanadas"];
            if ($data["custom_fields"]["Producción diaria de empanadas"] == "No produzco. Tengo un proyecto")
                $model->maker = 0;
            else
                $model->maker = 1;
        }
        if (isset($data["custom_fields"])) {
            $model->custom_fields = json_encode($data["custom_fields"]);
        }

        if (isset($data["custom_fields"]["Número de empleados"])) {
            $model->notes .= $data["custom_fields"]["Número de empleados"];
        }

        if (isset($data["custom_fields"]["Dirección"])) {
            $model->address = $data["custom_fields"]["Dirección"];
        }




        if (isset($data["custom_fields"]["Número de Puntos de venta"])) {
            $model->number_venues = $data["custom_fields"]["Número de Puntos de venta"];
        }
        if (isset($data["custom_fields"]["Tipo de empresa"])) {
            $model->company_type = $data["custom_fields"]["Tipo de empresa"];
        }




        if (isset($data["custom_fields"]["Tamaño de las empanadas que fabrican"])) {
            $model->empanadas_size = $data["custom_fields"]["Tamaño de las empanadas que fabrican"];
        }

        if (isset($data["custom_fields"]["Número de sedes de la empresa"])) {
            $model->number_venues = $data["custom_fields"]["Número de sedes de la empresa"];
        }

        if (isset($data["custom_fields"]["Cargo que ocupas dentro de la empresa"])) {
            $model->position = $data["custom_fields"]["Cargo que ocupas dentro de la empresa"];
        }

        $model->status_id = -1;
        //dd($this->getStatusRD($data));
        $model->status_id = $this->getStatusRD($data);
        if ($request->campaign == "Maquiempanadas - MQE_Form leads desmechadora") {
            $model->status_id = 41; //Desmechadora
        }

        $model->source_id = $this->getSourceRD($request);



        $modelRD = $this->saveAPIRD($model, $opportunity);

        $this->sendToN8n($modelRD->id);
        
        return $modelRD->id;
    }

    function getNextUserID()
    {
        // obtener el usuario que fue asignado por última vez
        $lastAssignedUser = User::where('last_assigned', 1)->first();

        // restablecer el estado de asignación del usuario anterior
        if ($lastAssignedUser) {
            $lastAssignedUser->last_assigned = 0;
            $lastAssignedUser->save();
        }

        // obtener el próximo usuario para asignar
        $nextUser = User::where('id', '>', $lastAssignedUser->id ?? 0)
            ->where('status_id', '=', 1)
            ->where('assignable', '>', 0)
            ->first();

        // si hemos llegado al final de la lista de usuarios, comenzamos desde el principio
        if (!$nextUser) {
            $nextUser = User::where('status_id', '=', 1)
                ->where('assignable', '>', 0)
                ->first();
        }

        // marcar este usuario como el último asignado
        $nextUser->last_assigned = 1;
        $nextUser->save();

        
        // devolver el ID del usuario
        return $nextUser->id;
    }

    function getStatusRD($data){
        $status = 1;
        if (isset($data["lead_stage"])) {
            /*
            $status = $data["lead_stage"];
            
            if (isset($opportunity) && ($opportunity == 'true')) {
                $model->status_id = 19; //Oportunidad
            }
            if (isset($status) && (($status == 'Lead'))) {

                $status_id = 1; //Calificado
            }
            if (isset($status) && (($status == 'Desmechadora'))) {

                $status_id = 41; //Desmechadora
            }
            if (isset($status) && (($status == 'Lead Qualificado'))) {
               // $status_id = 36; //Calificado
            }
            
            if (isset($status) && (($status == 'Cliente'))) {
                $status_id = 19; //Demo
            }
            if ($status_id) {
                $status_id = 29; //PQR
            }*/
        }
        return $status;
    }

    function getRandomNextUserID()
    {
        // Obtener todos los usuarios activos y asignables
        $users = User::where('status_id', '=', 1)
            ->where('assignable', '>', 0)
            ->get();

        // Si no hay usuarios, retornar nulo o manejar el caso adecuadamente
        if ($users->isEmpty()) {
            return null; // o manejo alternativo
        }

        // Preparar una lista ponderada basada en 'assignable'
        $weightedUsers = [];
        foreach ($users as $user) {
            $weightedUsers[$user->id] = $user->assignable;
        }

        // Seleccionar un usuario basado en el peso 'assignable'
        $selectedUserId = $this->weightedRandomSelection($weightedUsers);

        // Actualizar el estado de asignación de usuarios
        User::query()->update(['last_assigned' => 0]); // Restablecer todos a no asignados
        User::where('id', $selectedUserId)->update(['last_assigned' => 1]); // Marcar el seleccionado como asignado

        // Devolver el ID del usuario seleccionado
        return $selectedUserId;
    }

    /**
     * Realiza una selección aleatoria ponderada.
     * 
     * @param array $weights Array asociativo de id => peso
     * @return int El ID seleccionado basado en el peso
     */
    function weightedRandomSelection($weights)
    {
        $totalWeight = array_sum($weights);
        $rand = mt_rand(1, $totalWeight);
        foreach ($weights as $id => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $id;
            }
        }
    }


    public function updateStatusFromRD(Request $request, $sid)
    {

        $model_rd = $this->getModelFromRD($request);


        $model_rd->status_id = $sid;

        $json = $request->json()->all();
        $data = $json["leads"][0];
        if (isset($data["opportunity"])) {
            $opportunity = $data["opportunity"];
        }

        $equal = $this->isEqualModel($model_rd);
        if (!$equal) {
            $similar = $this->getSimilarModel($model_rd);

            if ($similar) {
                $model = $similar;
                $model->status_id = $sid;
                $this->updateCustomerHistory($opportunity, $model, $model_rd);
            } else {
                $model_rd->status_id = $sid;
                $model_rd->save();
                $model = $model_rd;
            }
        } else {
            $model = $equal;
            $model->status_id = $sid;
        }

        $this->storeActionAPIRD($model_rd, $model->id);
    }


    public function getModelFromRD($request)
    {

        $json = $request->json()->all();

        $data = $json["leads"][0];
        //dd($data);
        $model = new Customer;
        if (isset($data["opportunity"])) {
            $opportunity = $data["opportunity"];
        }
        if (isset($data["name"])) {
            $model->name = $data["name"];
        }
        if (isset($data["email"])) {
            $model->email = $data["email"];
        }

        if (isset($data["public_url"])) {
            $model->rd_public_url = $data["public_url"];
        }

        if (isset($data["bio"])) {
            $model->notes .= $data["bio"];
        }
        if (isset($data["personal_phone"])) {
            $model->phone = $data["personal_phone"];
        }
        if (isset($data["mobile_phone"])) {
            $model->phone2 = $data["mobile_phone"];
        }
        if (isset($data["state"])) {
            $model->department = $data["state"];
        }
        if (isset($data["city"])) {
            $model->city = $data["city"];
        }
        if ($this->getCountry($data) != "") {
            $model->country = $this->getCountry($data);
        }

        if (isset($data["custom_fields"]["Producción diaria de empanadas"])) {
            $model->count_empanadas = $data["custom_fields"]["Producción diaria de empanadas"];
            if ($data["custom_fields"]["Producción diaria de empanadas"] == "No produzco. Tengo un proyecto")
                $model->maker = 0;
            else
                $model->maker = 1;
        }

        if (isset($data["city"])) {
            $model->city = $data["city"];
        }

        if (isset($data["custom_fields"]["Número de Puntos de venta"])) {
            $model->number_venues = $data["custom_fields"]["Número de Puntos de venta"];
        }
        if (isset($data["custom_fields"]["Tipo de empresa"])) {
            $model->company_type = $data["custom_fields"]["Tipo de empresa"];
        }


        if (isset($data["company"])) {
            $model->business = $data["company"];
        }
        if (isset($data["job_title"])) {
            $model->position = $data["job_title"];
        }

        if (isset($data["fit_score"])) {
            $model->scoring_profile = $data["fit_score"];
        }

        if (isset($data["interest"])) {
            $model->scoring_interest = $data["interest"];
        }

        if (isset($data["public_url"])) {
            $model->rd_public_url = $data["public_url"];
        }


        if (isset($data["custom_fields"]["Tamaño de las empanadas que fabrican"])) {
            $model->empanadas_size = $data["custom_fields"]["Tamaño de las empanadas que fabrican"];
        }

        if (isset($data["custom_fields"]["Número de sedes de la empresa"])) {
            $model->number_venues = $data["custom_fields"]["Número de sedes de la empresa"];
        }

        if (isset($data["custom_fields"]["Cargo que ocupas dentro de la empresa"])) {
            $model->position = $data["custom_fields"]["Cargo que ocupas dentro de la empresa"];
        }

        $model->status_id = -1;
        if (isset($data["lead_stage"])) {
            $status = $data["lead_stage"];
            /*
            if (isset($opportunity) && ($opportunity == 'true')) {
                $model->status_id = 19; //Oportunidad
            }
            if (isset($status) && (($status == 'Cliente'))) {
                $model->status_id = 19; //Demo
            }*/
            if (isset($status) && (($status == 'Lead'))) {
                $model->status_id = 1; //Calificado
            }
            if (isset($status) && (($status == 'Lead Qualificado'))) {
                $model->status_id = 36; //Calificado
            }
            
        }
        $tags = $data["tags"];
        if ($tags) {
            foreach ($tags as $key  => $value) {
                if ($value == "desmechadora") {
                    $model->status_id = 41; //Desmechadora
                }
                if ($value == "#alimentec20222") {
                    $model->notes .=  " #alimentec2022"; //alimentec
                }
                if ($value == "pqr") {
                    $model->notes .=  "update #pqr"; //alimentec
                    $model->status_id = 29; //Desmechadora
                }
            }
            $model->notes .= "Con etiquetas";
        } else {
            $model->notes .= "sin etiquetas";
        }
        $model->source_id = $this->getSourceRD($request);

        return $model;
    }

    public function audienceFromRD(Request $request)
    {
        //dd($request);
        $json = $request->json()->all();

        $data = $json["leads"][0];

        $model = new Customer;
        if (isset($data["opportunity"])) {
            $opportunity = $data["opportunity"];
        }
        if (isset($data["name"])) {
            $model->name = $data["name"];
        }
        if (isset($data["email"])) {
            $model->email = $data["email"];
        }

        if (isset($data["public_url"])) {
            $model->rd_public_url = $data["public_url"];
        }

        if (isset($data["bio"])) {
            $model->notes .= $data["bio"];
        }
        if (isset($data["personal_phone"])) {
            $model->phone = $data["personal_phone"];
        }
        if (isset($data["mobile_phone"])) {
            $model->phone2 = $data["mobile_phone"];
        }
        if (isset($data["state"])) {
            $model->department = $data["state"];
        }
        if (isset($data["city"])) {
            $model->city = $data["city"];
        }

        if (isset($data["first_conversion"]["content"]["País"])) {
            $model->country = $data["first_conversion"]["content"]["País"];
        }


        if (isset($data["custom_fields"]["Producción diaria de empanadas"])) {
            $model->count_empanadas = $data["custom_fields"]["Producción diaria de empanadas"];
            if ($data["custom_fields"]["Producción diaria de empanadas"] == "No produzco. Tengo un proyecto")
                $model->maker = 0;
            else
                $model->maker = 1;
        }

        if (isset($data["city"])) {
            $model->city = $data["city"];
        }

        if (isset($data["custom_fields"]["Número de Puntos de venta"])) {
            $model->number_venues = $data["custom_fields"]["Número de Puntos de venta"];
        }
        if (isset($data["custom_fields"]["Tipo de empresa"])) {
            $model->company_type = $data["custom_fields"]["Tipo de empresa"];
        }


        if (isset($data["company"])) {
            $model->business = $data["company"];
        }
        if (isset($data["job_title"])) {
            $model->position = $data["job_title"];
        }

        if (isset($data["fit_score"])) {
            $model->scoring_profile = $data["fit_score"];
        }

        if (isset($data["interest"])) {
            $model->scoring_interest = $data["interest"];
        }

        if (isset($data["public_url"])) {
            $model->rd_public_url = $data["public_url"];
        }


        if (isset($data["custom_fields"]["Tamaño de las empanadas que fabrican"])) {
            $model->empanadas_size = $data["custom_fields"]["Tamaño de las empanadas que fabrican"];
        }

        if (isset($data["custom_fields"]["Número de sedes de la empresa"])) {
            $model->number_venues = $data["custom_fields"]["Número de sedes de la empresa"];
        }

        if (isset($data["custom_fields"]["Cargo que ocupas dentro de la empresa"])) {
            $model->position = $data["custom_fields"]["Cargo que ocupas dentro de la empresa"];
        }

        $model->status_id = 1;
        $model->status_id = $this->getStatusRD($data);
        $model->source_id = $this->getSourceRD($request);

        $model = $this->saveAPIRD($model, $opportunity);
        //CREAR AUDIENCIA 7
        $audienceCustomer = new AudienceCustomer;
        $audienceCustomer->audience_id = 7; //Agendados
        $audienceCustomer->customer_id = $model->id;
        $audienceCustomer->save();
    }

    public function updateCustomerHistory($opportunity, $model, $rd_model)
    {
        // actualiza el existente
        if (($opportunity == "false") && ($model->status_id == 18 || $model->status_id == 36)) {
            //nuevo - no contesta - calificado
            $model->status_id = 36;
        } elseif (($opportunity == "true") && ($model->status_id == 1 || $model->status_id == 18 || $model->status_id == 36 || $model->status_id == 19)) {
            //nuevo - no contesta - calificado - oportunidad
            $model->status_id = 19;
        }
        if ($model->source_id == 23 || $model->source_id == 37) {
            $model->rd_public_url = $rd_model->rd_public_url;
            $model->scoring_profile = $rd_model->scoring_profile;
            $model->scoring_interest = $rd_model->scoring_interest;
        }
        $this->updateAPICustomerRD($model);
    }

    public function saveAPIRD($request_model, $opportunity)
    {



        //   igual   |   similar     
        //     No          No      crea    
        //     No          Si      actualiza
        //     Si                  actualiza                       

        // vericamos que no se inserte 2 veces
        $equal = $this->isEqualModel($request_model);

        // dd($equal);
        if (!$equal) {
            //dd($status); 
            // verificamos uno similar
            $similar = $this->getSimilarModel($request_model);

            if ($similar) {
                // creo un nuevo registro
                $model = $similar;

                if (isset($request_model->rd_public_url)) {
                    $model->rd_public_url = $request_model->rd_public_url;
                }
                if (isset($request_model->scoring_profile)) {
                    $model->scoring_profile = $request_model->scoring_profile;
                }
                if (isset($request_model->scoring_interest)) {
                    $model->scoring_interest = $request_model->scoring_interest;
                }

                if (isset($request_model->email)) {
                    $model->contact_email = $similar->email;
                    $model->email = $request_model->email;
                }

                if (isset($request_model->count_empanadas)) {
                    $model->count_empanadas = $request_model->count_empanadas;
                }

                if (isset($request_model->maker)) {
                    $model->maker = $request_model->maker;
                }

                if (isset($request_model->inquiry_product_id)) {
                    $model->inquiry_product_id = $request_model->inquiry_product_id;
                }

                if (isset($request_model->campaign_name)) {
                    $model->campaign_name = $request_model->campaign_name;
                }

                if (!str_contains($request_model->notes, 'actualizado')) {
                    $model->notes = trim($request_model->notes . ' actualizado');
                } else {
                    $model->notes = $request_model->notes;
                }


                if ($model->product_id != 15)
                    $model->status_id = $request_model->status_id;

                $model->save();
                $this->updateCustomerHistory($opportunity, $model, $request_model);
            } else {
                // Verifico si es proyecto
                $request_model->user_id = $this->getRandomNextUserID();
                /*
                if(isset($request_model->maker) && ($request_model->maker == 0))
                    $request_model->user_id = null; #antes era 92 Estefanía
                else 
                    $request_model->user_id = $this->getNextUserID();
                */
                $request_model->save();

                $model = $request_model;
            }
        } else {
            $model = $equal;
        }

        //dd($similar);
        $this->storeActionAPIRD($request_model, $model->id);
        return $model;
    }


    public function saveAPICustomerRD($request)
    {
        $model = $request;
        $model->save();
        return $model;
    }







    public function updateAPICustomerRD($model)
    {

        $model->save();

        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($model);
    }

    public function storeActionAPIRD($request, $customer_id)
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
        //$model->note .= json_encode($request);
        $model->type_id = 16; // actualización 


        $model->customer_id = $customer_id;

        $model->save();

        return back();
    }

    public function updateCreateDateRD($request, $customer_id)
    {

        $customer = Customer::find($customer_id);
        $model = new Action;


        $model->note = "se actualizó la fecha de creación " . $customer->created_at;
        $model->type_id = 16; // actualización
        $model->customer_id = $customer_id;
        $model->save();


        $mytime = Carbon\Carbon::now();
        $customer->created_at = $mytime->toDateTimeString();
        //$customer->status_id = 19;
        $customer->save();


        return back();
    }




    public function saveCustomerCalculate(Request $request)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        /*
        $model = new Customer;
        $model->name = $request->name;
        $model->phone = $request->phone;
        $model->email = $request->email;
        $model->country = $request->country_name;
        $model->notes = $request->range;  */
        $this->saveAPI($request);
        return response()->json(['yes' => 'Validado correctamente']);
    }









    public function getWompiReference(Request $request)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $model = $this->saveUniqueCustomer($request);
        $reference = $this->saveReference($request, $model->id);
        return $reference->id;
    }

    public function saveUniqueCustomer(Request $request)
    {
        $model = $this->isEqual($request);
        if (!$model) {
            $model = $this->getSimilar($request);
            if (!$model) {
                $model = new Customer;
            }
        }
        $model = $this->saveAPICustomer($request, $model);
        return $model;
    }

    public function saveReference(Request $request, $cid)
    {

        $reference = new Reference;
        $reference->document_number = $request["document_number"];
        $reference->name = $request["name"];
        $reference->phone = $request["phone"];
        $reference->email = $request["email"];

        $reference->billing_city = $request["billing_city"];
        $reference->billing_address = $request["billing_address"];

        $reference->note = $request["note"];
        $reference->product_name = $request["note"];

        $model = Product::where('name', $request["note"])->first();

        $reference->product_id = $model->id;

        $reference->value = $request["value"];

        $reference->billing_country = $request["billing_country"];

        $reference->shipping_city = $request["shipping_city"];
        $reference->shipping_address = $request["shipping_address"];
        $reference->shipping_country = $request["shipping_country"];
        $reference->trm = $request["trm"];

        $reference->customer_id = $cid;
        $reference->save();
        return $reference;
    }

    public function updateStatus(Request $request)
    {
        $data = $request->json()->all();
        $transaction = $data["data"]["transaction"];
        $status = $transaction["status"];
        $id = $transaction["id"];
        $amount_in_cents = $transaction["amount_in_cents"];
        $reference = $transaction["reference"];
        $ustomer_email = $transaction["customer_email"];
        $currency = $transaction["currency"];
        $payment_method_type = $transaction["payment_method_type"];
        $redirect_url = $transaction["redirect_url"];
        $status = $transaction["status"];
        $shipping_address = $transaction["shipping_address"];
        $payment_link_id = $transaction["payment_link_id"];
        $payment_source_id = $transaction["payment_source_id"];

        $model = Reference::find($reference);
        $model->status_id = $status;
        $model->save();
    }




    public function sendToRDStation($request)
    {
        $model = new RdStation;

        if (isset($request->source_id)) {
            $customer_source = CustomerSource::find($request->source_id);
            if ($customer_source) {
                $model->setTrafficMedium($customer_source->name);
            }
        }


        $model->setName("");
        $model->setPersonalPhone("");
        $model->setEmail("");
        $model->setCountry("");
        if (isset($request->name))
            $model->setName($request->name);
        if (isset($request->phone))
            $model->setPersonalPhone($request->phone);
        if (isset($request->email))
            $model->setEmail($request->email);
        if (isset($request->country))
            $model->setCountry($request->country);

        $data = array(
            'event_type' => 'CONVERSION',
            'event_family' => 'CDP',
            'payload' =>
            array(
                'conversion_identifier' => 'Chatbot',
                'name' => $model->getName(),
                'email' => $model->getEmail(),
                'country' => $model->getCountry(),
                'personal_phone' => $model->getPersonalPhone(),
                //'job_title' => '',
                //'state' => '',
                //'city' => '',
                'mobile_phone' => $model->getPersonalPhone(),
                //'twitter' => 'twitter handler of the contact',
                //'facebook' => 'facebook name of the contact',
                //'linkedin' => 'linkedin user name of the contact',
                //'website' => 'website of the contact',
                //'company_name' => 'company name',
                //'company_site' => 'company website',
                //'company_address' => 'company address',
                //'client_tracking_id' => 'lead tracking client_id',
                'traffic_source' => 'others',
                'traffic_medium' => $model->getTrafficMedium(),
                //'traffic_campaign' => 'easter-50-off',
                //'traffic_value' => 'easter eggs',

                /*CAMPOS PERSONALIZADOS*/
                //'cf_produccion_diaria_de_empanadas' => 'Selecciona',
                //'cf_cargo_que_ocupas_dentro_de_la_empresa' => '',
                //'cf_numero_de_sedes_de_la_empresa' => '',
                //'cf_tipo_de_empresa' => '',
                'open_country' => $model->getCountry(),
                //'company' => '',
                //'cf_tipo_de_empresa_0' => '',
                //'cf_numero_de_puntos_de_venta_0' => '',
                //'cf_puesto_1' => '',
                //'cf_produccion_diaria_de_empanadas' => '',


                /*'tags' => 
            array (
              0 => 'mql',
              1 => '2019',
            ),*/
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
        $model->send($data);
    }


    public function sendToRDStationFromCRM(Request $request)
    {
        //dd($request); 
        $model = new RdStation;

        $model->setName("");
        $model->setPersonalPhone("");
        $model->setEmail("");
        $model->setCountry("");
        $model->setTrafficMedium("");


        if (isset($request->source_id)) {
            if ($request->source_id == 26) {
                $model->setTrafficMedium("Sitio Web - WhatsApp Manual");
            }
        }


        if (isset($request->name))
            $model->setName($request->name);
        if (isset($request->phone))
            $model->setPersonalPhone($request->phone);
        if (isset($request->email))
            $model->setEmail($request->email);
        if (isset($request->country))
            $model->setCountry($request->country);

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

        return response()->json(array(
            "response" => "fué enviado con éxito!",
        ));
    }








    


    private function cleanPhoneCharters($phone)
    {
        if (!$phone) return null;

        // Eliminar cualquier carácter no numérico
        $cleaned = preg_replace('/\D/', '', $phone);



        return $cleaned;
    }
    public function getSourceRD($request)
    {
        //POST RD STATION
        $json = $request->json()->all();
        $str = "";
        if (isset($json["leads"][0]["last_conversion"]["source"]))
            $str = $json["leads"][0]["last_conversion"]["source"]; // evento
        $model = CustomerSource::where('rd_source', $str)->first();
        $sid = 1;
        if ($model)
            $sid = $model->id;

        return $sid;
    }


    public function sendCampaign($campaign_id, $customer_id)
    {

        $campaign = Campaign::find($campaign_id);
        if (!$campaign) {
            return;
        }

        \Log::info('campaign=>' , [$campaign] );

        $this->defaultMessageSource = MessageSource::getDefaultMessageSource();



        if ($this->defaultMessageSource) {
            //logger('reacched');
            $this->waToolboxService = new WAToolboxService($this->defaultMessageSource);
            
        
        }

        
        $customer = Customer::find($customer_id);
        if (!$customer) {
            return;
        }

        $phone = $customer->getPhone();
        if (empty($phone)) {
            return;
        }

        // 🔍 Evitar mensajes duplicados
        $sent_texts = [];
        
        \Log::info("campaign->messages: " ,[ $campaign->messages ]);

        foreach ($campaign->messages as $message) {
            if (empty($message->text) || in_array($message->text, $sent_texts)) {
                continue;
            }

            $sent_texts[] = $message->text; // Guardar texto para evitar repetidos

            $payload = [
                'phone_number' => $phone,
                'message' => $message->text,
                'action' => 'send-message',
                'type' => 'text',
            ];

            try {
                $this->waToolboxService->sendMessageToWhatsApp($payload);
            } catch (\Exception $e) {
                \Log::error("Error enviando mensaje en sendCampaign: " . $e->getMessage());
            }
        }
    }

    public function sendToN8n($cid){
        // Define tu URL de producción del Webhook en n8n
        $webhookUrl = 'https://n8n-1-85-1.onrender.com/webhook/5977d7f0-ace7-4daf-9ead-cd6f1856fbb5'; // <-- reemplaza con tu URL real

    // Estructura el payload que deseas enviar (puedes modificarlo según lo que esperas en n8n)
    $payload = [
        'customer_id' => $cid ?? null
    ];

    // Enviar el POST al webhook de n8n
    $response = Http::post($webhookUrl, $payload);

    // Opcional: verificar respuesta
    if ($response->failed()) {
        Log::error('Error al enviar lead a n8n', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
    }

}
