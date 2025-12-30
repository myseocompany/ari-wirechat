<?php

// *****  mqe
// *****  ultimo cambio 2025_04_04

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\ActionType;
use App\Models\Audience;
use App\Models\AudienceCustomer;
use App\Models\CampaignMessage;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\CustomerHistory;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use App\Models\CustomerSource;
use App\Models\CustomerStatus;
use App\Models\CustomerStatusPhase;
use App\Models\Email;
use App\Models\Log;
use App\Models\Product;
use App\Models\RdStation;
use App\Models\Reference;
use App\Models\Tag;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\MetaConversionsService;
use App\Services\WhatsAppService;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Str;
use Mail;
use Namu\WireChat\Enums\ConversationType;
use Namu\WireChat\Enums\ParticipantRole;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

class CustomerController extends Controller
{
    protected $attributes = ['status_name'];

    protected $appends = ['status_name'];

    protected $status_name;

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    private function ensureCanAccessCustomer(Customer $customer): void
    {
        if (! $customer->hasFullAccess(Auth::user())) {
            abort(403);
        }
    }

    private function normalizeUserId($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value === 'null' || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function index(Request $request)
    {
        $menu = $this->customerService->getUserMenu(Auth::user());

        $defaultUserFilter = ! $request->has('user_id') && ! $request->filled('search');

        if ($defaultUserFilter) {
            $request->merge(['user_id' => Auth::id()]);
        }

        $statuses = CustomerStatus::where('status_id', 1)
            ->orderBy('weight', 'asc')
            ->get();

        // dd($statuses);
        $query = $request->input('search');

        $model = $this->customerService->filterCustomers($request, $statuses, null, false, 10);
        $childGroups = $this->customerService->filterCustomers($request, $statuses, null, true);
        $parentStatuses = CustomerStatus::query()
            ->whereNull('parent_id')
            ->orderBy('weight')
            ->get();
        $customersGroup = $this->customerService->groupStatusesByParent($childGroups, $parentStatuses);

        $usingDefaultDateRange = ! $request->filled('from_date')
            && ! $request->filled('to_date')
            && ! $request->filled('search')
            && ! $request->boolean('no_date');

        if ($defaultUserFilter && $usingDefaultDateRange && method_exists($model, 'total') && $model->total() === 0) {
            $model = $this->customerService->filterCustomers($request, $statuses, null, false, 10, false);
            $childGroups = $this->customerService->filterCustomers($request, $statuses, null, true, 0, false);
            $customersGroup = $this->customerService->groupStatusesByParent($childGroups, $parentStatuses);
        }

        if (! empty($request->search) && method_exists($model, 'total') && $model->total() === 1) {
            $onlyCustomer = $model->first();
            if ($onlyCustomer) {
                return redirect()->route('customers.show', $onlyCustomer->id);
            }
        }

        $customer = $model[0] ?? null;
        if ($request->filled('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
            $this->ensureCanAccessCustomer($customer);
        }

        $id = $customer?->id ?? 0;
        $allTags = Tag::orderBy('name')->get();
        if ($customer) {
            $customer->load('tags');
        }

        $childStatusIds = $childGroups->pluck('status_table_id')->filter()->unique()->values();
        $childStatusMeta = $childStatusIds->isNotEmpty()
            ? CustomerStatus::query()
                ->whereIn('id', $childStatusIds)
                ->get(['id', 'parent_id', 'weight'])
                ->keyBy('id')
            : collect();
        $statusGroups = $childGroups->map(function ($childGroup) use ($childStatusMeta) {
            $statusId = $childGroup->status_table_id ?? null;
            $meta = $statusId ? $childStatusMeta->get($statusId) : null;

            return (object) [
                'count' => (int) ($childGroup->count ?? 0),
                'status_id' => $statusId,
                'status_name' => $childGroup->status_name,
                'status_color' => $childGroup->status_color,
                'parent_id' => $meta?->parent_id,
                'weight' => $meta?->weight ?? 9999,
            ];
        })->sortBy('weight')->values();

        return view('customers.index', [
            'request' => $request,
            'menu' => $menu,
            'users' => $this->getUsers(),
            'statuses' => $statuses,
            'model' => $model,
            'customersGroup' => $customersGroup,
            'parent_statuses' => $parentStatuses,
            'statusGroups' => $statusGroups,
            'pending_actions' => $this->getPendingActions(),
            'products' => Product::all(),
            'sources' => CustomerSource::all(),
            'inquiry_products' => Product::all(),
            'scoring_interest' => $this->getInterestOptions($request),
            'scoring_profile' => $this->getProfileOptions($request),
            'customer' => $customer,
            'action_options' => ActionType::orderBy('weigth')->get(),
            'email_options' => Email::where('type_id', 1)->where('active', 1)->get(),
            'statuses_options' => $statuses,
            'country_options' => Country::all(),
            'tags' => $allTags,
            'allTags' => $allTags,
        ]);
    }

    public function indexPhase($pid, Request $request)
    {
        return redirect()->route('customers.index');
    }

    public function customersByStage($sid, Request $request)
    {
        return redirect()->route('customers.index');
    }

    public function leads(Request $request)
    {
        return redirect()->route('customers.index');
    }

    public function getPendingActions()
    {
        $model = Action::whereNotNull('due_date')
            ->whereNull('delivery_date')
            ->where('due_date', '>=', now())
            ->where('creator_user_id', '=', Auth::id())
            ->get();

        return $model;
    }

    public function getInterestOptions(Request $request)
    {
        return DB::table('customers')
            ->select('scoring_interest')
            ->whereNotNull('scoring_interest')
            ->distinct()
            ->orderBy('scoring_interest', 'DESC')
            ->get();
    }

    public function getProfileOptions(Request $request)
    {
        $model = Customer::select(DB::raw('distinct scoring_profile'))
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        if (isset($request->created_updated) && ($request->created_updated == 'updated')) {
                            $query = $query->whereBetween('customers.updated_at', [$request->from_date, $request->to_date]);
                        }
                        if (isset($request->created_updated) && ($request->created_updated == 'created')) {
                            $query = $query->whereBetween('customers.created_at', [$request->from_date, $request->to_date]);
                        }
                    }
                    if (isset($request->product_id) && ($request->product_id != null)) {
                        if ($request->product_id == 1) {
                            $query = $query->whereIn('customers.product_id', [1, 6, 7, 8, 9, 10, 11]);
                        } else {
                            $query = $query->where('customers.product_id', $request->product_id);
                        }
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('customers.user_id', $request->user_id);
                    }
                    if (isset($request->source_id) && ($request->source_id != null)) {
                        $query = $query->where('customers.source_id', $request->source_id);
                    }
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query = $query->where('customers.status_id', $request->status_id);
                    }
                    if (isset($request->scoring_interest) && ($request->scoring_interest != null)) {
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    }
                    if (isset($request->scoring_profile) && ($request->scoring_profile != null)) {
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    }
                    if (isset($request->search)) {
                        $query = $query->where(
                            function ($innerQuery) use ($request) {
                                $innerQuery->orwhere('customers.name', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.email', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.document', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.position', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.business', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.phone', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.phone2', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.notes', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.city', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.country', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.bought_products', 'like', '%'.$request->search.'%');
                                // $innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_phone2', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_email', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_position', 'like', '%'.$request->search.'%');
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

    public function dragleads(Request $request)
    {
        $pid = substr($request->path(), -1);
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
            // dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::findOrFail($request->customer_id);
            $this->ensureCanAccessCustomer($customer);
            $id = $request->customer_id;
        }
        // dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby('created_at', 'DESC')->get();
        $action_options = ActionType::where('status_id', 1)
            ->orderBy('weigth')
            ->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $pid)->orderBy('weight', 'ASC')->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $messages = CampaignMessage::where('campaign_id', 11)->get();
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby('created_at', 'DESC')->get();
        }

        return view('customers.newIndex', compact('model', 'request', 'messages', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu'));
    }

    public function getSources()
    {
        $model = CustomerSource::orderBy('name')->get();

        return $model;
    }

    // Función para normalizar números de teléfono
    public function normalizePhoneNumber($phoneNumber)
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }

    protected function phoneNormalizationExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return sprintf("REGEXP_REPLACE(%s, '[^0-9]', '', 'g')", $column);
        }

        return sprintf("REGEXP_REPLACE(%s, '[^0-9]', '')", $column);
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
        if ($model->perPage() > $model->total()) {
            $model->getActualRows = $model->total();
        }
        foreach ($model as $items) {
            if (isset($items->status_id)) {
                $status = CustomerStatus::find($items->status_id);
                if (isset($status)) {
                    $items->status_name = $status->name;
                }
            }
        }
        $model->action = $action;

        return $model;
    }

    public function getUsers()
    {
        return User::orderBy('name')
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
                        if (isset($request->created_updated) && ($request->created_updated == 'updated')) {
                            $query = $query->whereBetween('customers.updated_at', [$request->from_date, $request->to_date]);
                        }
                        if (isset($request->created_updated) && ($request->created_updated == 'created')) {
                            $query = $query->whereBetween('customers.created_at', [$request->from_date, $request->to_date]);
                        }
                    }
                    if (isset($request->product_id) && ($request->product_id != null)) {
                        if ($request->product_id == 1) {
                            $query = $query->whereIn('customers.product_id', [1, 6, 7, 8, 9, 10, 11]);
                        } else {
                            $query = $query->where('customers.product_id', $request->product_id);
                        }
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('customers.user_id', $request->user_id);
                    }
                    if (isset($request->source_id) && ($request->source_id != null)) {
                        $query = $query->where('customers.source_id', $request->source_id);
                    }
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query = $query->where('customers.status_id', $request->status_id);
                    }
                    if (isset($request->scoring_interest) && ($request->scoring_interest != null)) {
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    }
                    if (isset($request->scoring_profile) && ($request->scoring_profile != null)) {
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    }
                    if (isset($request->search)) {
                        $query = $query->where(
                            function ($innerQuery) use ($request) {
                                $innerQuery->orwhere('customers.name', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.email', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.document', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.position', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.business', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.phone', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.phone2', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.notes', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.city', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.country', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.bought_products', 'like', '%'.$request->search.'%');
                                // $innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_phone2', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_email', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_position', 'like', '%'.$request->search.'%');
                            }
                        );
                    }
                    if (isset($request->actions_number)) {
                        $query->havingRaw('lead07 = '.$request->actions_number);
                        $query->where('outbound', '1');
                    }
                    // PENDIENTE
                    if (isset($request->week)) {
                        $query->where('view_customers_followups.week', $request->week);
                        $query->where('view_customers_followups.year', $request->year);
                        $query->whereIn('customers.status_id', [1, 28]);
                        // $dates = $this->getWeek($request->week);
                        // $query->whereBetween('view_customers_followups.created_at', $dates);
                        // $query->whereBetween('created_at', array($request->from_date, $request->to_date));
                    }
                    if (isset($request->lead)) {
                        $lead = $this->getLead($request->lead);
                        $sql = 'view_customers_followups.'.$lead;
                        if (isset($request->with) && ($request->with == 1)) {
                            $query->where($sql, '<>', 0);
                        } elseif (isset($request->with) && ($request->with == 0)) {
                            $query->where($sql, '=', 0);
                        }
                        // $query->select(DB::raw("sum(if(".$lead."<>0,1,0))>0 as lean_time"));
                        // $query->where('outbound', '1');
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
            // ->havingRaw('(count(if(outbound=0, actions.created_at, null)))','is not null')
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

        return [$from_date, $to_date];
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
                        if (isset($request->user_id) && ($request->user_id != null)) {
                            $query = $query->whereBetween('customers.updated_at', [$request->from_date, $request->to_date]);
                        } else {
                            $query = $query->whereBetween('customers.created_at', [$request->from_date, $request->to_date]);
                        }
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('customers.user_id', $request->user_id);
                    }
                    if (isset($request->source_id) && ($request->source_id != null)) {
                        $query = $query->where('customers.source_id', $request->source_id);
                    }
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query = $query->where('customers.status_id', $request->status_id);
                    }
                    if (isset($request->scoring_interest) && ($request->scoring_interest != null)) {
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    }
                    if (isset($request->scoring_profile) && ($request->scoring_profile != null)) {
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    }
                    if (isset($request->search)) {
                        $query = $query->orwhere('customers.name', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.email', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.document', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.business', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.position', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.phone', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.phone2', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.notes', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.city', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.country', 'like', '%'.$request->search.'%');
                        $query = $query->orwhere('customers.bought_products', 'like', '%'.$request->search.'%');
                        // $query = $query->orwhere('customers.status_temp',"like", "%".$request->search."%");
                        $query = $query->orwhere('customers.contact_name', 'like', '%'.$request->search.'%');
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
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->whereBetween('customers.updated_at', [$request->from_date, $request->to_date]);
                    } else {
                        $query = $query->whereBetween('customers.created_at', [$request->from_date, $request->to_date]);
                    }
                }
                if (isset($request->user_id) && ($request->user_id != null)) {
                    $query = $query->where('customers.user_id', $request->user_id);
                }
                if (isset($request->source_id) && ($request->source_id != null)) {
                    $query = $query->where('customers.source_id', $request->source_id);
                }
                if (isset($request->status_id) && ($request->status_id != null)) {
                    $query = $query->where('customers.status_id', $request->status_id);
                }
                if (isset($request->search)) {
                    $query = $query->orwhere('customers.name', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.email', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.document', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.business', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.position', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.phone', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.phone2', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.notes', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.city', 'like', '%'.$request->search.'%');
                    $query = $query->orwhere('customers.country', 'like', '%'.'Colombia'.'%');
                    $query = $query->orwhere('customers.bought_products', 'like', '%'.$request->search.'%');
                    // $query = $query->orwhere('customers.status_temp',"like", "%".$request->search."%");
                    $query = $query->orwhere('customers.contact_name', 'like', '%'.$request->search.'%');
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
            ->where('stage_id', $stage_id)
            ->orderBy('stage_id', 'ASC')
            ->orderBy('weight', 'ASC')
            ->get();
        $customer_sources = CustomerSource::orderBy('name')->get();
        $products = Product::all();
        $authUser = Auth::user();
        $canAssignCustomers = $authUser?->canAssignCustomers() ?? false;
        $defaultAssignedUserId = $canAssignCustomers ? null : $authUser?->id;

        return view('customers.create', compact('products', 'customers_statuses', 'users', 'customer_sources', 'canAssignCustomers', 'defaultAssignedUserId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $phoneValue = trim((string) $request->phone);
        $normalizedPhone = $this->normalizePhoneNumber($phoneValue);
        $hasPhone = $phoneValue !== '' || $normalizedPhone !== '';

        $duplicateQuery = Customer::query();
        $hasConditions = false;

        if ($request->filled('email')) {
            $duplicateQuery->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('contact_email', $request->email);
            });
            $hasConditions = true;
        }

        if ($hasPhone) {
            $controller = $this;
            $phoneConditions = function ($query) use ($phoneValue, $normalizedPhone, $controller) {
                $columns = ['phone', 'phone2', 'contact_phone2'];
                $firstColumn = true;

                foreach ($columns as $column) {
                    $expression = $controller->phoneNormalizationExpression($column);

                    $columnConditions = function ($inner) use ($column, $expression, $phoneValue, $normalizedPhone) {
                        $hasInnerCondition = false;

                        if ($phoneValue !== '') {
                            $inner->where($column, $phoneValue);
                            $hasInnerCondition = true;
                        }

                        if ($normalizedPhone !== '') {
                            if ($hasInnerCondition) {
                                $inner->orWhereRaw("$expression = ?", [$normalizedPhone]);
                            } else {
                                $inner->whereRaw("$expression = ?", [$normalizedPhone]);
                                $hasInnerCondition = true;
                            }
                        }

                        if (! $hasInnerCondition) {
                            $inner->whereRaw('0 = 1');
                        }
                    };

                    if ($firstColumn) {
                        $query->where($columnConditions);
                        $firstColumn = false;
                    } else {
                        $query->orWhere($columnConditions);
                    }
                }
            };

            if ($hasConditions) {
                $duplicateQuery->orWhere($phoneConditions);
            } else {
                $duplicateQuery->where($phoneConditions);
                $hasConditions = true;
            }
        }

        $duplicateCustomers = $hasConditions
            ? $duplicateQuery
                ->orderBy('id', 'desc')
                ->get(['id', 'name', 'email', 'phone', 'phone2', 'contact_phone2'])
            : collect();

        if ($duplicateCustomers->isNotEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'duplicate_message' => 'Ya existe un cliente con el mismo correo o teléfono.',
                    'duplicate_customers' => $duplicateCustomers
                        ->map(function ($customer) {
                            return $customer->only(['id', 'name', 'email', 'phone', 'phone2', 'contact_phone2']);
                        })
                        ->values()
                        ->all(),
                ]);
        }

        $authUser = Auth::user();
        $canAssignCustomers = $authUser?->canAssignCustomers() ?? false;
        $requestedUserId = $this->normalizeUserId($request->input('user_id'));
        $assignedUserId = $canAssignCustomers ? $requestedUserId : ($authUser?->id ?? null);

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
        $model->user_id = $assignedUserId;
        $model->source_id = $request->source_id;
        $model->technical_visit = $request->technical_visit;
        // datos de contacto
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

            // $this->sendWelcomeMail($model);
            return redirect("/customers/{$model->id}/show")
                ->with('status', '🔥 El Cliente <strong>'.$model->name.'</strong> fué añadido con éxito!');
        }
    }

    public function sendWelcomeMail($customer)
    {
        $email_id = 46;
        $email = Email::find($email_id);
        $count = Email::sendUserEmailWelcome($customer->id, $email->subject, $email->view, $email->id);
        $this->storeEmailAction($email, $customer, 'Correo automático de notificación');

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
        $model = Customer::findOrFail($id);
        $model->loadMissing(['user', 'status']);
        $fullAccess = $model->hasFullAccess(Auth::user());

        if (! $fullAccess) {
            return view('customers.readonly', [
                'model' => $model,
            ]);
        }

        $model->load('tags');
        $action_options = ActionType::orderby('weigth')->get();
        $actions = Action::where('customer_id', '=', $id)
            ->orderby('created_at', 'DESC')
            ->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)
            ->orderby('updated_at', 'DESC')
            ->get();

        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('status_id', 1)
            ->orderBy('stage_id', 'ASC')
            ->orderBy('weight', 'ASC')->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $quizMetaId = 2000;
        $quizQuestionMetaIds = [2001, 2002, 2003, 2004, 2005, 2006, 2007];
        $calculatorMetaRootIds = [3000, 30000];
        $calculatorParentId = 30000;
        $defaultCalculatorQuestionMetaIds = [30009, 30010, 30011, 30012, 30013, 30014, 30015, 30016, 30017, 30018, 30019];
        $calculatorQuestionMetaIds = CustomerMetaData::where('parent_id', $calculatorParentId)->pluck('id')->toArray();
        if (empty($calculatorQuestionMetaIds)) {
            $calculatorQuestionMetaIds = $defaultCalculatorQuestionMetaIds;
        }
        $quizMetaIds = array_merge([$quizMetaId], $calculatorMetaRootIds, $quizQuestionMetaIds, $calculatorQuestionMetaIds);
        $audiences = Audience::all();
        $meta_data = CustomerMetaData::all();
        $metas = CustomerMetaData::leftJoin('customer_metas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
            ->select('customer_meta_datas.value as name', 'customer_metas.value', 'customer_metas.created_at', 'customer_meta_datas.type_id', 'customer_meta_datas.parent_id')
            ->where('customer_id', '=', $id)
            ->whereNotIn('customer_meta_datas.id', $quizMetaIds)
            ->get();
        $weighted = 0;
        $test = CustomerMeta::leftJoin('customer_meta_datas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
            ->select(DB::raw('ROUND(SUM(customer_metas.value)/COUNT(customer_metas.meta_data_id)) AS average'))
            ->where('customer_metas.customer_id', '=', $id)
            ->where('customer_meta_datas.type_id', '=', 1)
            ->get();
        // Quiz Escalable: resumen y respuestas
        $quizSummary = CustomerMeta::where('customer_id', $id)
            ->where('meta_data_id', $quizMetaId)
            ->orderByDesc('created_at')
            ->first();
        $quizAnswers = CustomerMeta::where('customer_id', $id)
            ->whereIn('meta_data_id', $quizQuestionMetaIds)
            ->orderByDesc('created_at')
            ->get();
        $quizQuestions = CustomerMetaData::with('CustomerMetaDataChildren')
            ->whereIn('id', $quizQuestionMetaIds)
            ->get()
            ->keyBy('id');
        // Calculadora: resumen y respuestas más recientes
        $calculatorSummary = CustomerMeta::where('customer_id', $id)
            ->whereIn('meta_data_id', $calculatorMetaRootIds)
            ->orderByDesc('created_at')
            ->first();

        // dd($calculatorSummary);
        $calculatorSummaryData = $calculatorSummary ? (json_decode($calculatorSummary->value, true) ?: []) : null;
        $calculatorAnswersRaw = CustomerMeta::leftJoin('customer_meta_datas', 'customer_meta_datas.id', '=', 'customer_metas.meta_data_id')
            ->where('customer_metas.customer_id', $id)
            ->where(function ($query) use ($calculatorQuestionMetaIds, $calculatorParentId) {
                $query->whereIn('customer_metas.meta_data_id', $calculatorQuestionMetaIds)
                    ->orWhere('customer_meta_datas.parent_id', $calculatorParentId);
            })
            ->orderByDesc('customer_metas.created_at')
            ->get(['customer_metas.*', 'customer_meta_datas.value as question_value', 'customer_meta_datas.parent_id']);

        $calculatorAnswers = $calculatorAnswersRaw
            ->groupBy('meta_data_id')
            ->map(function ($group) {
                return $group->first();
            })
            ->sortBy('meta_data_id')
            ->values();

        $calculatorQuestions = CustomerMetaData::whereIn('id', $calculatorQuestionMetaIds)->get()->keyBy('id');
        $allTags = Tag::orderBy('name')->get();
        $welcomeTemplate = $this->getWelcomeTemplateName();
        $welcomeNote = $this->getWelcomeTemplateNote($welcomeTemplate);
        $welcomeAlreadySent = Action::where('customer_id', $id)
            ->where('note', $welcomeNote)
            ->exists();
        $historyOwnerMap = $histories->values()->mapWithKeys(function ($history, int $index) use ($histories) {
            $next = $histories->get($index + 1);

            return [
                $history->id => [
                    'next_owner_id' => $next?->user_id,
                    'next_owner_name' => $next?->user?->name ?? 'Sin asignar',
                ],
            ];
        });
        $chatMessages = Message::withoutGlobalScope(\Namu\WireChat\Models\Scopes\WithoutRemovedMessages::class)
            ->with(['sendable', 'conversation.group'])
            ->where('sendable_id', $model->id)
            ->where('sendable_type', $model->getMorphClass())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        Logger::info('Customer show WireChat messages', [
            'customer_id' => $model->id,
            'message_count' => $chatMessages->count(),
            'message_ids' => $chatMessages->pluck('id')->all(),
        ]);

        return view('customers.show', compact(
            'model',
            'histories',
            'test',
            'meta_data',
            'metas',
            'audiences',
            'actions',
            'action_options',
            'email_options',
            'statuses_options',
            'actual',
            'today',
            'quizSummary',
            'quizAnswers',
            'quizQuestions',
            'calculatorSummary',
            'calculatorSummaryData',
            'calculatorAnswers',
            'calculatorQuestions',
            'allTags',
            'welcomeAlreadySent',
            'historyOwnerMap',
            'chatMessages'
        ));
    }

    private function resolveMetaEventName(Customer $customer): string
    {
        if ((int) $customer->status_id === 1) {
            return 'raw_lead';
        }

        $statusName = CustomerStatus::getName($customer->status_id) ?: 'lead';
        $slug = Str::slug($statusName, '_');

        return $slug !== '' ? $slug : 'lead';
    }

    private function resolveMetaCustomData(Customer $customer): array
    {
        return array_filter([
            'lead_event_source' => 'ARI CRM',
            'campaign_name' => $customer->campaign_name,
        ]);
    }

    public function previewMetaPayload(Customer $customer, MetaConversionsService $metaConversionsService)
    {
        $this->ensureCanAccessCustomer($customer);

        $eventName = $this->resolveMetaEventName($customer);
        $eventTime = optional($customer->updated_at)->timestamp ?? now()->timestamp;
        $customData = $this->resolveMetaCustomData($customer);

        $payload = $metaConversionsService->buildRequestPayload(
            $customer,
            $eventName,
            $eventTime,
            $customData
        );

        return response()->json([
            'ok' => true,
            'endpoint' => $metaConversionsService->getEndpoint(),
            'payload' => $payload,
        ]);
    }

    public function sendMetaEvent(Customer $customer, MetaConversionsService $metaConversionsService)
    {
        $this->ensureCanAccessCustomer($customer);

        if (! $metaConversionsService->isEnabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'MetaConversionsService no está configurado.',
            ], 422);
        }

        $eventName = $this->resolveMetaEventName($customer);
        $eventTime = optional($customer->updated_at)->timestamp ?? now()->timestamp;
        $customData = $this->resolveMetaCustomData($customer);

        $payload = $metaConversionsService->buildRequestPayload(
            $customer,
            $eventName,
            $eventTime,
            $customData
        );

        try {
            $response = $metaConversionsService->sendLeadEvent(
                $customer,
                $eventName,
                $eventTime,
                $customData
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'endpoint' => $metaConversionsService->getEndpoint(),
                'payload' => $payload,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'endpoint' => $metaConversionsService->getEndpoint(),
            'payload' => $payload,
            'server_response' => $response,
        ]);
    }

    public function sendWelcomeTemplate(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $this->ensureCanAccessCustomer($customer);
        $welcomeTemplate = $this->getWelcomeTemplateName();
        $welcomeTemplateLanguage = $this->getWelcomeTemplateLanguage();
        $welcomeNote = $this->getWelcomeTemplateNote($welcomeTemplate);

        $alreadySent = Action::where('customer_id', $customerId)
            ->where('note', $welcomeNote)
            ->exists();

        if ($alreadySent) {
            return back()->with('statusone', "Este cliente ya recibió la campaña {$welcomeTemplate}.");
        }

        $customerName = trim($customer->name ?? '') ?: 'allí';
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                ],
            ],
        ];

        try {
            app(WhatsAppService::class)->sendTemplateToCustomer(
                $customer,
                $welcomeTemplate,
                $components,
                $welcomeTemplateLanguage
            );

            Action::create([
                'customer_id' => $customerId,
                'type_id' => 105,
                'note' => $welcomeNote,
                'creator_user_id' => Auth::id() ?? 0,
            ]);

            Logger::info('Welcome template sent from CRM', [
                'customer_id' => $customerId,
                'user_id' => Auth::id(),
            ]);

            return back()->with('status', 'Mensaje de bienvenida enviado.');
        } catch (\Throwable $e) {
            Logger::error('Failed to send welcome template from CRM', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('statustwo', 'No se pudo enviar el mensaje. Inténtalo más tarde.');
        }
    }

    private function getWelcomeTemplateName(): string
    {
        return (string) config('whatsapp.welcome_template', 'drip_01');
    }

    private function getWelcomeTemplateNote(string $template): string
    {
        return "Se envió la campaña WhatsApp {$template} (bot).";
    }

    private function getWelcomeTemplateLanguage(): string
    {
        return (string) config('whatsapp.welcome_template_language', 'en_US');
    }

    public function updateNotes(Request $request, $customerId)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::findOrFail($customerId);
        $this->ensureCanAccessCustomer($customer);
        $customer->notes = $data['notes'] ?? '';
        if (Auth::id()) {
            $customer->updated_user_id = Auth::id();
        }
        $customer->save();

        return response()->json([
            'ok' => true,
            'notes' => $customer->notes,
            'updated_at' => $customer->updated_at,
        ]);
    }

    public function showAction($id, $Aid)
    {
        $actionProgramed = Action::find($Aid);
        $model = Customer::findOrFail($id);
        $this->ensureCanAccessCustomer($model);
        $allTags = Tag::orderBy('name')->get();
        $actions = Action::where('customer_id', '=', $id)->orderby('created_at', 'DESC')->get();
        $action_options = ActionType::orderBy('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::all();
        $statuses_options = CustomerStatus::orderBy('weight', 'ASC')->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $quizSummary = null;
        $quizAnswers = collect();
        $quizQuestions = collect();

        return view('customers.show', compact(
            'model',
            'histories',
            'actions',
            'action_options',
            'email_options',
            'statuses_options',
            'actual',
            'today',
            'actionProgramed',
            'quizSummary',
            'quizAnswers',
            'quizQuestions',
            'allTags'
        ));
    }

    public function showHistory($id)
    {
        $model = CustomerHistory::find($id);
        $allTags = collect();
        $actions = Action::where('customer_id', '=', $id)->orderby('created_at', 'DESC')->get();
        $action_options = ActionType::orderBy('weigth')->get();
        $histories = null;
        $email_options = Email::all();
        $statuses_options = CustomerStatus::orderBy('weight', 'ASC')->get();
        $actual = false;

        return view('customers.show', compact('model', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'allTags'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $stage_id = session('stage_id', 1);
        $model = Customer::findOrFail($id);
        $this->ensureCanAccessCustomer($model);
        $customer_statuses = CustomerStatus::orderBy('stage_id', 'ASC')
            ->where('stage_id', $stage_id)
            ->where('status_id', '1')
            ->orderBy('weight', 'ASC')
            ->get();
        $customer_sources = CustomerSource::all();
        $users = User::where('status_id', 1)
            ->orderBy('name', 'ASC')
            ->get();
        $users = $this->appendAssignedUser($users, $model->user);
        $scoring_profile = $this->getProfileOptionsOrder();
        $products = Product::all();
        $authUser = Auth::user();
        $canAssignCustomers = $authUser?->canAssignCustomers() ?? false;
        $lockedAssignedUserId = $model->user_id;

        return view('customers.edit', compact('products', 'model', 'customer_statuses', 'users', 'customer_sources', 'scoring_profile', 'canAssignCustomers', 'lockedAssignedUserId'));
    }

    protected function appendAssignedUser(Collection $users, ?User $assignedUser): Collection
    {
        if (! $assignedUser) {
            return $users;
        }

        if (! $users->contains('id', $assignedUser->id)) {
            $users->push($assignedUser);
        }

        return $users;
    }

    public function assignMe($id)
    {
        $model = Customer::find($id);
        if (is_null($model->user_id) || $model->user_id == 0) {
            $user = Auth::id();
            $model->user_id = $user;
            if (Auth::id()) {
                $model->updated_user_id = Auth::id();
            }
            $model->save();
        }

        return back();
    }

    public function updateAjax(Request $request)
    {
        $authUser = Auth::user();
        if (! $authUser || ! $authUser->canAssignCustomers()) {
            abort(403);
        }

        $model = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($model);
        $model->user_id = $this->normalizeUserId($request->input('user_id'));
        $model->save();

        return $model->id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = Customer::findOrFail($id);
        $this->ensureCanAccessCustomer($model);
        $authUser = Auth::user();
        $canAssignCustomers = $authUser?->canAssignCustomers() ?? false;
        $currentUserId = $model->user_id !== null ? (int) $model->user_id : null;
        $requestedUserId = $request->has('user_id')
            ? $this->normalizeUserId($request->input('user_id'))
            : $currentUserId;
        $lockedUserId = $currentUserId;

        if (! $canAssignCustomers && $request->filled('user_id') && $requestedUserId !== $lockedUserId) {
            abort(403);
        }

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
        $model->user_id = $canAssignCustomers ? $requestedUserId : $currentUserId;
        $model->source_id = $request->source_id;
        $model->status_id = $request->status_id;
        // Agregamos el producto en edicion de prospecto
        $model->product_id = $request->product_id;
        // datos de contacto
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
        if (Auth::id()) {
            $model->updated_user_id = Auth::id();
        }
        if ($model->save()) {
            return redirect('customers/'.$model->id.'/show')
                ->with('statusone', 'El Cliente <strong>'.$model->name.'</strong> fué modificado con éxito!');
        }
    }

    public function updateAjaxStatus(Request $request)
    {
        $model = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($model);
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
                if (count($request->status_id)) {
                    $query = $query->where('customers.status_id', '=', $request->status_id);
                }
            }
        )
            ->select(DB::raw('customers.*'))
            ->orderBy('customers.status_id', 'asc', 'created_at', 'asc')
            ->paginate(20);
    }

    public function getStatusID($request, $stage_id)
    {
        $url = $request->fullurl();
        $paramenters = explode('&', $url);
        $res = [];
        foreach ($paramenters as $key => $value) {
            if (strpos($value, 'status_id') !== false && (str_replace('status_id=', '', $value) != 0)) {
                $res[] = str_replace('status_id=', '', $value);
            }
        }
        if (! count($res)) {
            $model = $this->customerService->getCustomerWithParent($stage_id);
            // $model = CustomerStatus::all();
            foreach ($model as $item) {
                $res[] = $item->id;
            }
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
        // $model = Email::find(1);
        $customer = Customer::find($cui);
        $subjet = 'Bro';
        // dd($customer);
        /*
    Mail::raw($model->body, function ($message) use ($customer, $subjet){
        $message->from('noresponder@mqe.com.co', 'Maquiempanadas');
        $message->to($customer->email, $customer->user_name)->subject($subjet);
    });
*/
        $emailcontent = [
            'subject' => 'Gracias por contactarme',
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $cui,
        ];
        // dd($emailcontent);
        // Mail::send('emails.brochure', $emailcontent, function ($message) use ($customer){
        //         $message->subject('MQE');
        //         $message->to('nicolas@myseocompany.co');
        //     });
    }

    public function getAllStatusID($stage_id)
    {
        $res = [];
        $model = $this->customerService->getCustomerWithParent($stage_id);
        // $model = CustomerStatus::all();
        foreach ($model as $item) {
            $res[] = $item->id;
        }

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
        $model = Customer::findOrFail($id);
        $this->ensureCanAccessCustomer($model);
        if ($model->delete()) {
            return redirect('customers')->with('statustwo', 'El Cliente <strong>'.$model->name.'</strong> fué eliminado con éxito!');
        }
    }

    public function saveAPICustomer($request)
    {
        // dd($request);
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
        $model->notes = $request->notes.' '.$request->email;
        if (isset($request->count_empanadas)) {
            $model->count_empanadas = $request->count_empanadas;
        }
        $model->bought_products = $request->product;
        $model->cid = $request->cid;
        $model->src = $request->src;

        if (isset($request->status) && ($request->status == 'Escuela')) {
            $model->status_id = 22;
        } else {
            $model->status_id = 1;
        }
        $model->source_id = 0;
        if (isset($request->campaign) && ($request->campaign == 'Facebook')) {
            $model->source_id = 17;
        } elseif (isset($request->campaign) && ($request->campaign == 'NewJersey')) {
            $model->source_id = 19;
        } elseif (isset($request->campaign) && ($request->campaign == 'USA')) {
            $model->source_id = 16;
        } elseif (isset($request->campaign) && ($request->campaign == '500')) {
            $model->source_id = 15;
        } elseif (isset($request->campaign) && ($request->campaign == 'Facebook New Jersey')) {
            $model->source_id = 22;
        } elseif (isset($request->campaign) && ($request->campaign == 'Leads Black Friday 2018')) {
            $model->source_id = 24;
        } elseif (isset($request->campaign) && ($request->campaign == 'Landing Desmechadora')) {
            $model->source_id = 28;
        } elseif (isset($request->campaign) && ($request->campaign == 'Landing Bogota')) {
            $model->source_id = 30;
        } elseif (isset($request->campaign) && ($request->campaign == 'Landing Promo Navideña')) {
            $model->source_id = 32;
        } elseif (isset($request->platform) && ($request->platform == 'fb')) {
            $model->source_id = 17;
        } elseif (isset($request->platform) && ($request->platform == 'ig')) {
            $model->source_id = 31;
        }
        $model->save();

        return $model;
    }

    public function isEqual($request)
    {
        $model = Customer::where(
            // Búsqueda por...
            function ($query) use ($request) {
                if (isset($request->user_id) && ($request->user_id != null)) {
                    $query = $query->where('user_id', $request->user_id);
                }
                if (isset($request->source_id) && ($request->source_id != null)) {
                    $query = $query->where('source_id', $request->source_id);
                }
                if (isset($request->status_id) && ($request->status_id != null)) {
                    $query = $query->where('status_id', $request->status_id);
                }
                if (isset($request->business) && ($request->business != null)) {
                    $query = $query->where('business', $request->business);
                }
                if (isset($request->phone) && ($request->phone != null)) {
                    $query = $query->where('phone', $request->phone);
                }
                if (isset($request->email) && ($request->email != null)) {
                    $query = $query->whereRaw('lower(email) = lower("'.$request->email.'")');
                }
                if (isset($request->phone2) && ($request->phone2 != null)) {
                    $query = $query->where('phone2', $request->phone2);
                }
                if (isset($request->notes) && ($request->notes != null)) {
                    $query = $query->where('notes', $request->notes);
                }
                if (isset($request->city) && ($request->city != null)) {
                    $query = $query->where('city', $request->city);
                }
                if (isset($request->country) && ($request->country != null)) {
                    $query = $query->where('country', $request->country);
                }
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
                if (isset($request->phone) && ($request->phone != null) && ($request->phone != 'NA')) {
                    $query->orwhere('phone', $request->phone);
                }
                if (isset($request->phone) && ($request->phone != null) && ($request->phone != 'NA')) {
                    $query->orwhere('phone2', $request->phone);
                }
                if (isset($request->phone2) && ($request->phone2 != null) && ($request->phone != 'NA')) {
                    $query->orwhere('phone', $request->phone2);
                }
                if (isset($request->phone2) && ($request->phone2 != null) && ($request->phone != 'NA')) {
                    $query->orwhere('phone2', $request->phone2);
                }
                if (isset($request->email) && ($request->email != null)) {
                    // $query->orWhereRaw('lower(email) = lower("'.$request->email.'")');
                    $query->orWhere('email', strtolower($request->email));
                }
                // $query->orWhereRaw('email', strtolower($request->email));
            }
        )
            ->get();

        // dd($model);
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
                if (isset($source)) {
                    return redirect('https://maquiempanadas.com/es/'.$source->redirect_url);
                } else {
                    return redirect('https://maquiempanadas.com/es/gracias-web');
                }
            }
            // este cliente ya existe. Se agrega una nueva nota
            else {
                $model = $similar[0];
                $this->storeActionAPI($request, $model->id);
                $this->updateCreateDate($request, $model->id);

                return redirect('https://maquiempanadas.com/es/gracias-web');
                // return redirect('https://maquiempanadas.com/es/gracias-web/');
                // echo "similard";
            }
        } else {
        }
    }

    public function storeActionAPI(Request $request, $customer_id)
    {
        $model = new Action;
        $str = '';
        if (isset($request->phone)) {
            $str .= ' telefono1:'.$request->phone;
        }
        if (isset($request->phone2)) {
            $str .= ' telefono2:'.$request->phone2;
        }
        if (isset($request->email)) {
            $str .= ' email:'.$request->email;
        }
        if (isset($request->city)) {
            $str .= ' ciudad:'.$request->city;
        }
        if (isset($request->country)) {
            $str .= ' pais:'.$request->country;
        }
        if (isset($request->name)) {
            $str .= ' Nombre:'.$request->name;
        }
        $model->note = $request->notes.$str;
        $model->type_id = 16; // actualización
        $model->customer_id = $customer_id;
        $model->save();

        return back();
    }

    public function updateCreateDate(Request $request, $customer_id)
    {
        $customer = Customer::find($customer_id);
        $model = new Action;
        $model->note = 'se actualizó la fecha de creación '.$customer->created_at;
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
        // dd($request);
        $date_programed = Carbon\Carbon::parse($request->date_programed);
        $today = Carbon\Carbon::now();
        // dd($request);
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        if (is_null($request->type_id)) {
            return back()->with('statustwo', 'El Cliente <strong>'.$customer->name.'</strong> no fue modificado!');
        }
        $model = new Action;
        if (isset($request->ActionProgrameId)) {
            $ActionProgramed = Action::find($request->ActionProgrameId);
            $ActionProgramed->delivery_date = $today;
            $ActionProgramed->save();
            $model->note = $request->note.'//'.$ActionProgramed->note;
        } else {
            $model->note = $request->note;
        }
        $model->type_id = $request->type_id;
        $model->creator_user_id = Auth::id();
        $model->customer_owner_id = $customer->user_id;
        $model->customer_createad_at = $customer->created_at;
        $model->customer_updated_at = $customer->updated_at;
        $model->customer_id = $request->customer_id;
        if ($request->filled('date_programed')) {
            $model->due_date = $date_programed;
        }

        $model->save();
        if (! is_null($request->status_id)) {
            $cHistory = new CustomerHistory;
            $cHistory->saveFromModel($customer);
            $customer->status_id = $request->status_id;
            if (Auth::id()) {
                $customer->updated_user_id = Auth::id();
            }
            $customer->save();
        }
        if (! is_null($request->file)) {
            $file = $request->file('file');
            $path = $file->getClientOriginalName();
            $destinationPath = 'public/files/'.$request->customer_id;
            $file->move($destinationPath, $path);
            $model = new CustomerFile;
            $model->customer_id = $request->customer_id;
            $model->url = $path;
            $model->save();

            return back();
        }

        return redirect()->to(url()->previous())
            ->with('statusone', 'El Cliente <strong>'.$customer->name.'</strong> fué modificado con éxito!');
    }

    public function saleAction(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $model = new Action;
        $model->type_id = 27;
        $model->sale_date = $request->sale_date;
        $model->sale_amount = $request->sale_amount;
        $model->customer_id = $customer->id;
        $model->creator_user_id = Auth::id();
        if ($request->machine == 'on') {
            $model->note = 'Venta de máquina';
        } else {
            $model->note = 'No es una máquina';
        }
        $model->save();

        return redirect()->back();
    }

    public function opportunityAction(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $action = new Action;
        $action->object_id = $request->id;
        $action->type_id = 28;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $customer->id;
        $action->save();

        return redirect()->back();
    }

    public function poorlyRatedAction(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $action = new Action;
        $action->object_id = $request->id;
        $action->type_id = 32;
        $action->creator_user_id = Auth::id();
        $action->customer_id = $customer->id;
        $action->save();

        /*$customer = Customer::find($request->customer_id);
    $customer->status_id = 53;
    $customer->save();*/
        return redirect()->back();
    }

    public function pqrAction(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $model = new Action;
        $model->type_id = 29;
        $model->created_at = $request->created_at;
        $model->note = $request->note;
        $model->customer_id = $customer->id;
        $model->creator_user_id = Auth::id();
        $model->save();
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($customer);
        $customer->status_id = 29;
        if (Auth::id()) {
            $customer->updated_user_id = Auth::id();
        }
        $customer->save();

        return redirect()->back();
    }

    public function spareAction(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $model = new Action;
        $model->type_id = 30;
        $model->delivery_date = $request->delivery_date;
        $model->note = $request->note;
        $model->customer_id = $customer->id;
        $model->creator_user_id = Auth::id();
        $model->save();
        $cHistory = new CustomerHistory;
        $cHistory->saveFromModel($customer);
        $customer->status_id = 46;
        if (Auth::id()) {
            $customer->updated_user_id = Auth::id();
        }
        $customer->save();

        return redirect()->back();
    }

    public function enviarCorreo()
    {
        $destinatario = 'mateogiraldo420@gmail.com';
        $mensaje = 'Este es el contenido del correo';

        // Mail::to($destinatario)->send(new DemoEmail($mensaje));
        return 'Correo enviado correctamente';
    }

    public function storeMail(Request $request)
    {
        $this->enviarCorreo();
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $email = Email::find($request->email_id);
        $emailcontent = [
            'subject' => $email->subject,
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $customer->id,
            'email_id' => $email->id,
            'customer_mail' => $customer->email,
            'name' => $customer->name,
        ];
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
            dd('direccion invalida');
        }
        /*
Mail::send($email->view, $emailcontent, function ($message) use ($customer, $email){
$message->subject($email->subject);
$message->to("mateogiraldo420@gmail.com");
});*/
        // Action::saveAction($customer->id,$email->id, 2);
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
        // $model = $this->customerService->filterModelFull($request, $statuses);

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
        // $model = $this->customerService->filterModelFull($request, $statuses);
        $model = $this->customerService->filterCustomers($request, $statuses, null, false, 50000000);
        $customersGroup = $this->customerService->filterCustomers($request, $statuses, null, true);

        // $model = $this->customerService->filterCustomers($request, $statuses, 1);
        // $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, 1);
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
        // dd($request->suggestions);
        $customer = Customer::find($id);
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->business = $request->business;
        $customer->position = $request->position;
        $customer->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->number_employees = $request->number_employees;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = $request->empanadas;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 78;
        $meta_data->value = $request->quality;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 79;
        $meta_data->value = $request->confort;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 80;
        $meta_data->value = $request->security;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 81;
        $meta_data->value = $request->delivery_time;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 82;
        $meta_data->value = $request->atention;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 83;
        $meta_data->value = $request->responsive_time_personal;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 84;
        $meta_data->value = $request->atention_technical_support;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 85;
        $meta_data->value = $request->quality_technical_support;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 86;
        $meta_data->value = $request->satisfaction_level;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 87;
        $meta_data->value = $request->recommendation;
        $meta_data->save();
        $meta_data = new CustomerMetaData;
        $meta_data->customer_id = $id;
        $meta_data->customer_meta_data_type_id = 88;
        $meta_data->value = $request->suggestions;
        $meta_data->save();
        redirect('http://mqe.myseotest.com.co/contact');
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
        $model->setName('');
        $model->setPersonalPhone('');
        $model->setEmail('');
        $model->setCountry('');
        $model->setTrafficMedium('');
        // dd($customer);
        if (isset($customer->source_id)) {
            $customer_source = CustomerSource::find($customer->source_id);
            if ($customer_source) {
                $model->setTrafficMedium($customer_source->name);
            }
        }
        if (isset($customer->name)) {
            $model->setName($customer->name);
        }
        if (isset($customer->phone)) {
            $model->setPersonalPhone($customer->phone);
        }
        if (isset($customer->email)) {
            $model->setEmail($customer->email);
        }
        if (isset($customer->country)) {
            $model->setCountry($customer->country);
        }
        $data = [
            'event_type' => 'CONVERSION',
            'event_family' => 'CDP',
            'payload' => [
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
                'legal_bases' => [
                    0 => [
                        'category' => 'communications',
                        'type' => 'consent',
                        'status' => 'granted',
                    ],
                ],
            ],
        ];
        $model->sendFromCrm($data);
    }

    public function daily(Request $request)
    {
        $menu = $this->customerService->getUserMenu(Auth::user(1));
        $pid = 1;
        session(['stage_id' => $pid]);
        $users = $this->getUsers();
        $pid = 1;
        $customer_options = CustomerStatus::where('stage_id', $pid)->orderBy('weight', 'ASC')->get();
        $statuses = $this->getStatuses($request, $pid);
        $model = $this->customerService->getModelPhase($request, $statuses, $pid, 10, false, true);
        $actionRelations = [
            'nextPendingAction' => function ($query) {
                $query->select(['id', 'customer_id', 'type_id', 'note', 'due_date', 'delivery_date', 'created_at', 'object_id']);
            },
            'lastRelevantAction' => function ($query) {
                $query->select(['id', 'customer_id', 'type_id', 'note', 'due_date', 'delivery_date', 'created_at', 'object_id']);
            },
        ];
        if ($model instanceof LengthAwarePaginator || $model instanceof Paginator) {
            $model->getCollection()->load($actionRelations);
        } elseif ($model) {
            $model->load($actionRelations);
        }
        $customersGroup = $this->customerService->countFilterCustomers($request, $statuses, $pid, false, true);
        $pending_actions = $this->getPendingActions();
        $phase = CustomerStatusPhase::find($pid);
        $sources = CustomerSource::all();
        $products = Product::all();
        $scoring_interest = $this->getInterestOptions($request);
        $scoring_profile = $this->getProfileOptions($request);
        $allTags = Tag::orderBy('name')->get();
        $customer = null;
        $searchResults = null;
        $id = 0;
        if ($model && isset($model[0])) {
            // dd($model);
            $customer = $model[0];
            $id = $customer->id;
        }
        if (isset($request->customer_id)) {
            $customer = Customer::findOrFail($request->customer_id);
            $this->ensureCanAccessCustomer($customer);
            $id = $request->customer_id;
        }
        if ($request->filled('search')) {
            $searchResults = $this->customerService->filterCustomers($request, $statuses, null, false, 15, false, false);
        }
        // dd($model->scoring_profile);
        $actions = Action::where('customer_id', '=', $id)->orderby('created_at', 'DESC')->get();
        $action_options = ActionType::orderby('weigth')->get();
        $histories = CustomerHistory::where('customer_id', '=', $id)->get();
        $email_options = Email::where('type_id', '=', 1)->where('active', '=', '1')->get();
        $statuses_options = CustomerStatus::where('stage_id', $pid)->orderBy('weight', 'ASC')->get();
        $actual = true;
        $today = Carbon\Carbon::now();
        $audiences = Audience::all();
        $model->action = 'reports/views/daily_customers_followup';
        $references = null;
        if ($customer != null) {
            $references = Reference::where('customer_id', '=', $customer->id)->orderby('created_at', 'DESC')->get();
        }
        $authUser = Auth::user();
        $canAssignCustomers = $authUser?->canAssignCustomers() ?? false;

        return view('customers.daily', compact('model', 'request', 'customer_options', 'customersGroup', 'users', 'sources', 'pending_actions', 'products', 'statuses', 'scoring_interest', 'scoring_profile', 'customer', 'histories', 'actions', 'action_options', 'email_options', 'statuses_options', 'actual', 'today', 'audiences', 'references', 'phase', 'menu', 'canAssignCustomers', 'allTags', 'searchResults'));
    }

    public function startConversationFromCRM2(Request $request)
    {

        // dd($request->all());
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        // $customerUser = $customer->getChatUser(); // el User equivalente al Customer
        $waUser = User::find(1); // Usuario con WA Toolbox activo

        $adminUser = User::find(Auth::id());
        // Usuario logueado en el CRM

        // Participantes
        $participants = collect([$waUser, $adminUser, $customer]);

        // Crear conversación o usar existente
        $conversation = $waUser->createConversationWith($customer);

        $existingConversation = $conversation::withParticipants($participants)->first();

        if ($existingConversation) {
            $conversation = $existingConversation;
        } else {
            // Crear conversación grupal
            $conversation = $waUser->createGroup(
                name: 'Chat con '.$customer->name,
                description: 'Conversación iniciada desde CRM'
            );

            // Agregar participantes
            $conversation->addParticipant($adminUser);
            $conversation->addParticipant($customer);
        }

        // Enviar mensaje como WAUser
        $message = $waUser->sendMessageTo($customer, $request->mensaje);

        // Redirigir al chat en una nueva pestaña
        $chatUrl = url("/chats/{$conversation->id}");

        /*
        return response()->json([
            'success' => true,
            'chat_url' => $chatUrl,
            'conversation_id' => $conversation->id,
        ]);
*/
        //        return redirect($chatUrl);

    }

    public function startConversationFromCRM(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $this->ensureCanAccessCustomer($customer);
        $waUser = User::find(1); // Usuario "robot" o herramienta WA Toolbox
        $adminUser = User::find(auth()->id()); // Usuario actual logueado

        // Buscar si ya existe un grupo con estos 3 participantes

        // Buscar conversaciones grupales existentes del waUser
        $conversation = Conversation::where('type', ConversationType::GROUP)
            ->whereHas('participants', function ($q) use ($waUser) {
                $q->where('participantable_id', $waUser->id)
                    ->where('participantable_type', $waUser->getMorphClass());
            })
            ->whereHas('participants', function ($q) use ($adminUser) {
                $q->where('participantable_id', $adminUser->id)
                    ->where('participantable_type', $adminUser->getMorphClass());
            })
            ->whereHas('participants', function ($q) use ($customer) {
                $q->where('participantable_id', $customer->id)
                    ->where('participantable_type', $customer->getMorphClass());
            })
            ->first();

        // Si no existe, se crea
        if (! $conversation) {
            $conversation = $waUser->createGroup(
                name: 'Chat con '.$customer->name,
                description: 'Conversación iniciada desde CRM'
            );

            // Añadir participantes
            $conversation->addParticipant($adminUser, ParticipantRole::ADMIN);
            $conversation->addParticipant($customer, ParticipantRole::PARTICIPANT);
            logger('No se encontró la conversación en grupo');
        } else {
            logger('Si se encontró la conversación en grupo');
        }

        // Enviar mensaje inicial
        $waUser->sendMessageTo($conversation, $request->mensaje);

        // Redirigir al chat
        return redirect("/chats/{$conversation->id}");
    }
}
