<?php

// trujillo

namespace App\Http\Controllers;

use App\Http\Requests\PreviewAudienceSegmentRequest;
use App\Http\Requests\StoreAudienceSegmentRequest;
use App\Models\Audience;
use App\Models\AudienceCustomer;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Customer;
use App\Models\CustomerSource;
use App\Models\CustomerStatus;
use App\Models\Tag;
use App\Models\User;
use App\Services\AudienceSegmentService;
use Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AudienceController extends Controller
{
    // public function __construct(){   $this->middleware('auth'); }

    public function index(Request $request)
    {
        $model = Audience::all();
        $campaigns = Campaign::all();

        return view('audiences.index', compact('model', 'campaigns'));
    }

    public function create(Request $request)
    {
        $model = $this->filterModel($request);

        return view('audiences.create', compact('model', 'request'));
    }

    public function createSegment(Request $request)
    {
        $segmentFields = $this->getSegmentFields();

        return view('audiences.segment_create', compact('segmentFields', 'request'));
    }

    public function previewSegment(
        PreviewAudienceSegmentRequest $request,
        AudienceSegmentService $segmentService
    ): JsonResponse {
        $preview = $segmentService->preview($request->segment());

        return response()->json($preview);
    }

    public function storeSegment(
        StoreAudienceSegmentRequest $request,
        AudienceSegmentService $segmentService
    ): RedirectResponse {
        $audience = new Audience;
        $audience->name = $request->input('name');
        $audience->save();

        $limit = $request->filled('max_recipients')
            ? (int) $request->input('max_recipients')
            : null;

        $customerIds = $segmentService->collectCustomerIds($request->segment(), $limit);

        if ($customerIds->isNotEmpty()) {
            foreach ($customerIds->chunk(1000) as $chunk) {
                $payload = $chunk->map(function (int $customerId) use ($audience): array {
                    return [
                        'audience_id' => $audience->id,
                        'customer_id' => $customerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->values()->all();

                AudienceCustomer::insert($payload);
            }
        }

        return redirect('/audiences/'.$audience->id.'/show')
            ->with('status', 'Audiencia creada con '.$customerIds->count().' contacto(s).');
    }

    private function getSegmentFields(): array
    {
        $statuses = CustomerStatus::orderBy('name')->get(['id', 'name']);
        $users = $this->getUsers()->map(function (User $user): array {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        });
        $sources = CustomerSource::orderBy('name')->get(['id', 'name']);
        $tags = Tag::orderBy('name')->get(['id', 'name']);

        return [
            'created_at' => [
                'label' => 'Fecha de creación',
                'type' => 'date',
                'operators' => [
                    'on' => 'es igual a',
                    'before' => 'es antes o igual a',
                    'after' => 'es después o igual a',
                    'between' => 'está entre',
                ],
            ],
            'status_id' => [
                'label' => 'Estado',
                'type' => 'select',
                'operators' => [
                    'eq' => 'es igual a',
                    'in' => 'está en',
                ],
                'options' => $this->mapSelectOptions($statuses->toArray()),
            ],
            'user_id' => [
                'label' => 'Asesor',
                'type' => 'select',
                'operators' => [
                    'eq' => 'es igual a',
                    'in' => 'está en',
                ],
                'options' => $this->mapSelectOptions($users->toArray()),
            ],
            'source_id' => [
                'label' => 'Fuente',
                'type' => 'select',
                'operators' => [
                    'eq' => 'es igual a',
                    'in' => 'está en',
                ],
                'options' => $this->mapSelectOptions($sources->toArray()),
            ],
            'tag_id' => [
                'label' => 'Etiqueta',
                'type' => 'select',
                'operators' => [
                    'eq' => 'es igual a',
                    'in' => 'está en',
                ],
                'options' => $this->mapSelectOptions($tags->toArray()),
            ],
            'name' => [
                'label' => 'Nombre del cliente',
                'type' => 'text',
                'operators' => [
                    'contains' => 'contiene',
                    'eq' => 'es igual a',
                ],
            ],
            'business' => [
                'label' => 'Empresa',
                'type' => 'text',
                'operators' => [
                    'contains' => 'contiene',
                    'eq' => 'es igual a',
                ],
            ],
            'city' => [
                'label' => 'Ciudad',
                'type' => 'text',
                'operators' => [
                    'contains' => 'contiene',
                    'eq' => 'es igual a',
                ],
            ],
            'country' => [
                'label' => 'País',
                'type' => 'text',
                'operators' => [
                    'contains' => 'contiene',
                    'eq' => 'es igual a',
                ],
            ],
            'scoring_profile' => [
                'label' => 'Perfil',
                'type' => 'select',
                'operators' => [
                    'eq' => 'es igual a',
                    'in' => 'está en',
                ],
                'options' => [
                    ['value' => 'a', 'label' => 'A (4 estrellas)'],
                    ['value' => 'b', 'label' => 'B (3 estrellas)'],
                    ['value' => 'c', 'label' => 'C (2 estrellas)'],
                    ['value' => 'd', 'label' => 'D (1 estrella)'],
                ],
            ],
            'has_whatsapp' => [
                'label' => 'Tiene WhatsApp',
                'type' => 'boolean',
                'operators' => [
                    'eq' => 'es igual a',
                ],
                'options' => [
                    ['value' => true, 'label' => 'Sí'],
                    ['value' => false, 'label' => 'No'],
                ],
            ],
        ];
    }

    private function mapSelectOptions(array $items): array
    {
        return collect($items)->map(function (array $item): array {
            return [
                'value' => (string) ($item['id'] ?? ''),
                'label' => (string) ($item['name'] ?? 'Sin nombre'),
            ];
        })->values()->all();
    }

    public function filterModel(Request $request)
    {
        // dd($request->scoring_profile);
        $dates = $this->getDates($request);

        //        $model = Customer::wherein('customers.status_id', $statuses)
        $model = Customer::leftJoin('actions', 'actions.customer_id', 'customers.id')
            ->leftJoin('action_types', 'actions.type_id', 'action_types.id')
            ->select('customers.id', 'customers.status_id', 'customers.user_id', 'customers.source_id', 'customers.created_at', 'customers.updated_at',
                'customers.name', 'customers.phone', 'customers.email', DB::raw('(count(if(outbound=0, actions.created_at, null))) / 
(now()-max(if(outbound=0, actions.created_at, null)))   as kpi'), DB::raw('max(if(outbound=0, actions.created_at, null)) as last_inbound_date'))
            ->where(
                // Búsqueda por...
                function ($query) use ($request, $dates) {
                    // if(isset($request->from_date) && ($request->from_date!=null)){

                    if ((isset($request->created_updated) && ($request->created_updated == 'updated'))) {
                        $query->whereBetween('customers.updated_at', $dates);
                    } else {
                        $query->whereBetween('customers.created_at', $dates);
                    }

                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query->where('user_id', $request->user_id);
                    }
                    if (isset($request->source_id) && ($request->source_id != null)) {
                        $query->where('source_id', $request->source_id);
                    }

                    if (isset($request->kpi) && ($request->kpi != null)) {
                        $query->where('kpi', $request->kpi);
                    }
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query->where('customers.status_id', $request->status_id);
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

                                // $innerQuery->orwhere('actions.note',"like", "%".$request->search."%");
                            }
                        );
                    }
                }
            )->groupBy('customers.id', 'customers.status_id', 'customers.email',
                'customers.name', 'customers.phone', 'customers.source_id', 'customers.user_id', 'customers.created_at', 'customers.updated_at')
            ->orderBy('customers.status_id', 'asc')
            ->orderByRaw('(count(if(outbound=0, actions.created_at, null))) / 
(now()-max(if(outbound=0, actions.created_at, null))) DESC')
            ->orderBy('customers.created_at', 'desc')
            ->get();

        return $model;
    }

    public function getDates($request)
    {
        $to_date = Carbon\Carbon::today()->subDays(0); // ayer
        $from_date = Carbon\Carbon::today()->subDays(0);

        if (isset($request->from_date) && ($request->from_date != null)) {

            $from_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date);
            $to_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date);
        }

        $to_date = $to_date->format('Y-m-d').' 23:59:59';
        $from_date = $from_date->format('Y-m-d');
        $data = [$from_date, $to_date];

        // dd($data);
        return $data;
    }

    public function store(Request $request)
    {
        $model = new Audience;

        $model->name = $request->name;
        $model->save();

        return redirect('/audiences/'.$model->id.'/customers');
    }

    public function getUsers()
    {
        return User::orderBy('name')
            ->where('users.status_id', 1)
            ->get();
    }

    public function createCustomers($id, Request $request)
    {

        $audience = Audience::find($id);

        $model = $this->filterModel($request);

        $str = '';
        foreach ($request->all() as $key => $value) {
            $str .= $key.'='.$value.'&';
        }
        $str = substr($str, 0, -1);
        $str = str_replace('&all=true', '', $str);

        $model->queryString = $str;

        $customer_options = CustomerStatus::all();
        $users = $this->getUsers();
        $sources = CustomerSource::all();

        return view('audiences.create_customers', compact('audience', 'id', 'model', 'request', 'customer_options', 'users', 'sources'));
    }

    public function storeCustomers($id, Request $request)
    {
        // si los tiene todos seleccionados
        // dd($request->customer_id);
        // guardo todo los registros
        $data = [];
        foreach ($request->customer_id as $key => $value) {
            $model = AudienceCustomer::where('customer_id', $value)
                ->where('audience_id', $id)
                ->first();
            if (! $model) {
                $data[] = ['audience_id' => $id, 'customer_id' => $value];
            }
        }

        AudienceCustomer::insert($data);

        return redirect('/audiences/'.$id.'/customers');

    }

    public function destroyCustomer($aid, $cid)
    {
        $model = AudienceCustomer::where('customer_id', $cid)
            ->where('audience_id', $aid)
            ->first();
        $model->delete();

        return redirect('/audiences/'.$aid.'/customers');
    }

    public function show($id, Request $request)
    {
        $dates = $this->getDates($request);
        $audience = Audience::find($id);
        $model = AudienceCustomer::select('audience_customer.customer_id as id', 'phone', 'phone2', 'name', 'country', 'city')
            // ->where('status_id', '=', 1)
            ->join('customers', 'customers.id', 'audience_customer.customer_id')
            ->where('audience_customer.audience_id', $id)
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query->where('customers.status_id', $request->status_id);
                    }
                    /*
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


                    if(isset($request->scoring_profile)  && ($request->scoring_profile!=null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);

                        if(isset($request->search)){
                            $query = $query->where(
                                function ($innerQuery) use ($request){
                                    $innerQuery->orwhere('customers.name',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.email',   "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.document',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.position',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.business',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.phone',   "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.phone2',   "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.notes',   "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.city',    "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.country', "like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.bought_products',"like", "%".$request->search."%");
                                    //$innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.contact_name',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.contact_phone2',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.contact_email',"like", "%".$request->search."%");
                                    $innerQuery->orwhere('customers.contact_position',"like", "%".$request->search."%");

                                    //$innerQuery->orwhere('actions.note',"like", "%".$request->search."%");
                                }
                            );
                        }
                    */
                }
            )

            ->orderBy('customers.created_at', 'DESC')
            ->whereNull('sended_at')
            ->paginate(10);

        $customer_options = CustomerStatus::all();
        $users = $this->getUsers();
        $sources = CustomerSource::all();
        $request->audience_id = $id;

        $statuses = $this->getStatuses($request, 1);

        $customersGroup = $this->countFilterCustomers($request, $statuses);

        return view('audiences.show', compact('model', 'audience', 'id', 'request', 'customer_options', 'users', 'sources', 'customersGroup'));

    }

    public function getStatuses(Request $request, $step)
    {
        $statuses = '';
        if (isset($request->from_date) || ($request->from_date != '')) {
            $statuses = $this->getAllStatusID($step);
        } else {
            $statuses = $this->getStatusID($request, $step);
        }

        return $statuses;

    }

    public function getAllStatusID($stage_id)
    {

        $res = [];
        $model = CustomerStatus::where('stage_id', $stage_id)->orderBy('weight', 'ASC')->get();
        // $model = CustomerStatus::all();

        foreach ($model as $item) {
            $res[] = $item->id;
        }

        return $res;
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

            $model = CustomerStatus::where('stage_id', $stage_id)
                ->orderBy('weight', 'ASC')
                ->get();
            // $model = CustomerStatus::all();

            foreach ($model as $item) {
                $res[] = $item->id;
            }

        }

        return $res;
    }

    public function countFilterCustomers($request, $statuses)
    {

        // $customersGroup = Customer::all();

        $customersGroup = Customer::wherein('customers.status_id', $statuses)
            ->rightJoin('audience_customer', 'audience_customer.customer_id', '=', 'customers.id')
            ->join('customer_statuses', 'customer_statuses.id', 'customers.status_id')
            ->where('audience_customer.audience_id', $request->audience_id)
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

    public function whatsapp($id, $cid)
    {
        $campaign = Campaign::find($cid);
        $campaignMessage = CampaignMessage::where('campaign_id', $cid)->get();
        $audience = Audience::find($id);
        $model = Customer::select('customers.id as id', 'phone', 'phone2', 'name')
            // ->where('status_id', '=', 1)
            ->where('audience_id', $id)
            ->join('audience_customer', 'customers.id', 'audience_customer.customer_id')
            ->orderBy('customers.created_at', 'DESC')
            ->whereNull('sended_at')
            ->paginate(50);
        // dd($model);

        // var_dump($model);

        return view('audiences.whatsapp', compact('model', 'audience', 'campaign', 'campaignMessage'));

    }

    public function showRpa($id, $cid)
    {
        $campaignMessage = CampaignMessage::where('campaign_id', $cid)->get();
        $campaign = Campaign::find($cid);
        $audience = Audience::find($id);
        $model = Customer::select('customers.id as id', 'phone', 'phone2', 'name')
            // ->where('status_id', '=', 1)
            ->where('audience_id', $id)
            ->join('audience_customer', 'customers.id', 'audience_customer.customer_id')
            ->orderBy('customers.created_at', 'DESC')
            ->whereNull('sended_at')
            ->get();

        return view('audiences.show_rpa', compact('model','audience','campaign', 'campaignMessage'));
    }
}
