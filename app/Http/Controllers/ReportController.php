<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\ActionType;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {

        // Esto lo cambio Amed
        $statuses = CustomerStatus::orderBy('weight', 'ASC')->get();
        $actions = ActionType::all();

        // Usuarios que tuvieron customers en el rango seleccionado
        $dates = $this->getDates($request);
        $customerQuery = Customer::whereBetween('updated_at', $dates);

        $userIdsWithCustomers = (clone $customerQuery)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $users = User::where('status_id', 1)
            ->whereIn('id', $userIdsWithCustomers)
            ->get();

        // Agregar fila de “Sin asignar” si hay customers sin user_id
        $hasUnassigned = (clone $customerQuery)->whereNull('user_id')->exists();
        if ($hasUnassigned) {
            $unassigned = new User;
            $unassigned->id = null;
            $unassigned->name = 'Sin asignar';
            $users->push($unassigned);
        }

        $dateRange = [
            'from' => $dates[0],
            'to' => substr($dates[1], 0, 10),
        ];
        $filterLabel = $this->getFilterLabel($request);

        return view('reports.users', compact('statuses', 'actions', 'users', 'request', 'dateRange', 'filterLabel'));
    }

    private function getFilterLabel(Request $request): string
    {
        $map = [
            '0' => 'Hoy',
            '-1' => 'Ayer',
            'thisweek' => 'Esta semana',
            'lastweek' => 'Semana pasada',
            'lastmonth' => 'Mes pasado',
            'currentmonth' => 'Mes en curso',
            '-7' => 'Últimos 7 días',
            '-30' => 'Últimos 30 días',
        ];

        if (! empty($request->filter) && isset($map[$request->filter])) {
            return $map[$request->filter];
        }

        if (! empty($request->from_date) && ! empty($request->to_date)) {
            return 'Rango personalizado';
        }

        return 'Personalizado';
    }

    public function getStartAndEndDate($week, $year)
    {

        $time = strtotime("1 January $year", time());
        $day = date('w', $time);
        $time += ((7 * $week) - $day) * 24 * 3600;
        $return[0] = date('Y-m-d', $time);
        $time += 6 * 24 * 3600;
        $return[1] = date('Y-m-d', $time);

        // dd($return);
        return $return;
    }

    public function index2()
    {
        // /, week(updated_at) as week esto lo cambio Amed
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

        foreach ($tasks as $item) {
            $data->addRow([
                $this->getStartAndEndDate($item->week, 2017)[0], intval($item->pr),
            ]);
        }

        \Lava::AreaChart('data', $data, [
            'title' => 'Data Growth',
            'legend' => [
                'position' => 'in',
            ],
        ]);

        return view('reports.index');
    }

    public function index()
    {
        $model = \DB::table('tasks')
            ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
            ->where('status_id', '=', 3)
            ->groupBy('year', 'week')
            ->get();
        $users = Customer::select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
            ->where('status_id', '=', 3)
            ->groupBy('year', 'week', 'user_id')
            ->get();
        foreach ($users as $item) {

            $item->name = User::getName($item->user_id);
        }

        return view('reports.index', compact('model', 'users'));

    }

    public function weeksByTeam(Request $request)
    {
        $model = \DB::table('tasks')
            ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
            ->where(function ($query) {
                $query = $query->where('status_id', '=', 6);
                $query = $query->orwhere('status_id', '=', 56);
                $query = $query->orwhere('status_id', '=', 3);

            })
            ->where(function ($query) use ($request) {
                if (isset($request->project_id)) {
                    $query = $query->where('tasks.project_id', '=', $request->project_id);
                }
                if (isset($request->user_id) && ($request->user_id != null)) {
                    $query = $query->where('tasks.user_id', '=', $request->user_id);

                }

            })
            ->groupBy('year', 'week', 'user_id')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->get();
        $graph = Customer::all();
        if (isset($request->user_id)) {
            $graph = \DB::table('tasks')
                ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                ->where(function ($query) {
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);

                })
                ->where(function ($query) use ($request) {
                    if (isset($request->project_id)) {
                        $query = $query->where('tasks.project_id', '=', $request->project_id);
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('tasks.user_id', '=', $request->user_id);
                    }

                })
                ->groupBy('year', 'week', 'user_id')
                ->get();
        } elseif (isset($request->project_id)) {
            $graph = \DB::table('tasks')
                ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                ->where(function ($query) {
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);

                })
                ->where(function ($query) use ($request) {
                    if (isset($request->project_id)) {
                        $query = $query->where('tasks.project_id', '=', $request->project_id);
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('tasks.user_id', '=', $request->user_id);
                    }

                })
                ->groupBy('year', 'week', 'project_id')
                ->get();
        } else {
            $graph = \DB::table('tasks')
                ->select(DB::raw('year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
                ->where(function ($query) {
                    $query = $query->where('status_id', '=', 6);
                    $query = $query->orwhere('status_id', '=', 56);
                    $query = $query->orwhere('status_id', '=', 3);

                })
                ->where(function ($query) use ($request) {
                    if (isset($request->project_id)) {
                        $query = $query->where('tasks.project_id', '=', $request->project_id);
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('tasks.user_id', '=', $request->user_id);
                    }

                })
                ->groupBy('year', 'week')
                ->get();
        }

        $controller = $this;
        $user_id = '';
        if (isset($request->user_id)) {
            $user_id = $request->user_id;
        }

        return view('reports.weeks_by_team', compact('model', 'graph', 'controller', 'user_id'));

    }

    public function weeksByUser()
    {
        $model = Customer::select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
            ->where(function ($query) {
                $query = $query->orwhere('status_id', '=', 6);
                $query = $query->orwhere('status_id', '=', 56);
                $query = $query->orwhere('status_id', '=', 3);

            })
            ->groupBy('year', 'week', 'user_id')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->get();
        foreach ($model as $item) {

            $item->name = User::getName($item->user_id);
        }
        $graph = $model;

        $controller = $this;

        return view('reports.weeks_by_user', compact('model', 'graph', 'controller'));

    }

    public function userCustomerStatus(Request $request)
    {
        // obtengo los usuarios activos
        $dates_array = $this->getDates($request);

        $users = User::where('status_id', '=', 1)
                    // ->where('role_id', 1)
            ->where('include_reports', 1)
            ->get();

        $data = [];
        $customer_statuses = CustomerStatus::where('stage_id', 1)->orderBy('weight', 'ASC')->get();

        $date_at = $request->created_updated === 'updated' ? 'customers.updated_at' : 'customers.created_at';

        foreach ($users as $user) {
            $user_data = [];

            foreach ($customer_statuses as $status) {
                $model = DB::table('customers')
                    ->where(function ($query) use ($request, $dates_array, $date_at) {
                        if (isset($request->from_date) && $request->from_date != '') {
                            $query->whereBetween($date_at, $dates_array);
                        }
                    })
                    ->where('user_id', $user->id)
                    ->where('status_id', $status->id)
                    ->count('customers.id');
                $user_data[] = $model;
                // dd($from_date);
            }

            $data[] = $user_data;

        }

        $controller = $this;

        return view('reports.user_status', compact(
            'controller', 'request', 'users', 'data', 'customer_statuses'));

    }

    public function getTimeArray($dates_array)
    {

        $from = $dates_array[0];
        $to = $dates_array[1];
        $from = Carbon::createFromFormat('Y-m-d', $from);
        $to = Carbon::createFromFormat('Y-m-d H:i:s', $to);

        $condition = '';
        if ($from->diffInMonths($to) > 0) {
            $condition = 'months';
            $span = $from->diffInMonths($to);
        } elseif ($from->diffInWeeks($to) > 0) {
            $condition = 'weeks';
            $span = $from->diffInWeeks($to);
        } elseif ($from->diffInDays($to) > 0) {
            $condition = 'days';
            $span = $from->diffInDays($to);
        }
        // dd($span);

        $time_array = [];

        for ($i = 0; $i < $span; $i++) {
            if ($condition == 'months') {
                $time_array[] = [$from->format('Y-m-d'), $from->addMonths(1)->format('Y-m-d')];
            } elseif ($condition == 'weeks') {
                $time_array[] = [$from->format('Y-m-d'), $from->addWeeks(1)->format('Y-m-d')];
            } elseif ($condition == 'days') {
                $time_array[] = [$from->format('Y-m-d'), $from->addDays(1)->format('Y-m-d')];
            }

        }

        return $time_array;
    }

    public function getUsersFromDates($date_array)
    {
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

    public function customersTime(Request $request)
    {
        // obtengo los usuarios activos
        $dates_array = $this->getDatesMontly($request);

        $customer_statuses = Customer::distinct()->select('status_id')
            ->whereBetween('created_at', $dates_array)
            ->get();

        $time_array = $this->getTimeArray($dates_array);

        // dd($customer_statuses);

        $customer_statuses = $this->elocuentToArrayStatus($customer_statuses);

        $users = $this->getUsersFromDates($dates_array);

        $data = [];
        $customer_statuses = CustomerStatus::where('stage_id', 1)->orderBy('weight', 'ASC')->get();

        // dd($time_array);
        foreach ($time_array as $time) {
            $user_data = [];

            foreach ($customer_statuses as $status) {
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
            'controller', 'request', 'users', 'time_array', 'data', 'customer_statuses'));

    }

    public function elocuentToArray($model)
    {
        $array = [];
        foreach ($model as $item) {
            $array[] = $item->type_id;

        }

        return $array;
    }

    public function elocuentToArray2($model)
    {
        $array = [];
        foreach ($model as $item) {
            $array[] = $item->creator_user_id;

        }

        return $array;
    }

    public function elocuentToArrayStatus($model)
    {
        $array = [];
        foreach ($model as $item) {
            $array[] = $item->status_id;

        }

        return $array;
    }

    public function getDates($request)
    {
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(7);

        if (isset($request->from_date) && ($request->from_date != null)) {
            $to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date.' 00:00:00');
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date.' 00:00:00');
        }

        $date_array =
            [$from_date->format('Y-m-d'), $to_date->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s')];

        return $date_array;
    }

    public function getDatesMontly($request)
    {
        $to_date = Carbon::today()->subMonths(0); // ayer
        $from_date = Carbon::today()->subMonths(3);

        if (isset($request->from_date) && ($request->from_date != null)) {
            $to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date.' 00:00:00');
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date.' 00:00:00');
        }

        $date_array =
            [$from_date->format('Y-m-d'), $to_date->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s')];

        return $date_array;
    }

    public function userCustomerActions(Request $request)
    {
        // obtengo los usuarios activos
        $date_array = $this->getDates($request);

        $users_id = Action::distinct()->select('creator_user_id')
            ->whereBetween('created_at', $date_array)
            ->whereNotNull('creator_user_id')
            ->get();
        $users_id = $this->elocuentToArray2($users_id);

        if (isset($request->user_id)) {
            $users = User::where('status_id', '=', 1)
                ->where('id', $request->user_id)
                ->get();
        } else {
            $users = User::where('status_id', '=', 1)
                ->whereIn('id', $users_id)
                ->get();
        }

        $data = [];

        $types_id = Action::distinct()->select('type_id')
            ->whereBetween('created_at', $date_array)
            ->whereNotNull('creator_user_id')
            ->get();

        if (isset($request->action_types_id)) {
            $types_id = $this->elocuentToArray($types_id);
            $action_types = ActionType::where('id', $request->action_types_id)
                ->get();
        } else {
            $action_types = ActionType::whereIn('id', $types_id)
                ->get();
        }

        $actions_types = ActionType::whereIn('id', $types_id)
            ->get();

        foreach ($users as $user) {
            $user_data = [];

            foreach ($action_types as $type) {
                $model = DB::table('actions')
                    ->whereBetween('created_at', $date_array)
                    ->where('creator_user_id', $user->id)
                    ->where('type_id', $type->id)
                    ->count('id');
                $user_data[] = $model;
                // dd($from_date);
            }

            $data[] = $user_data;

        }

        $controller = $this;

        return view('reports.user_action', compact(
            'controller', 'request', 'users', 'data', 'date_array', 'action_types'));

    }

    public function daysByUser(Request $request)
    {
        // obtengo los usuarios activos
        $users = User::where('status_id', '=', 1)
            ->whereIn('role_id', [1, 2])
            ->get();
        $days = 1;
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(1);

        if (isset($request->from_date) && ($request->from_date != null)) {
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date);
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date.' 00:00:00');
        }

        $days = $from_date->diffInDays($to_date);

        if ($days < 1) {
            $days = 1;
        }

        $days_array = [];

        for ($i = 0; $i < $days; $i++) {
            $from = $from_date;
            $from->addDays(1);

            $days_array[] = [
                $from->format('Y-m-d'),
                $from->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s')];
        }

        $status_array = [3, 6, 56, 57];
        $data = [];

        foreach ($users as $user) {
            $user_data = [];

            for ($i = 0; $i < $days; $i++) {
                $tasks = DB::table('tasks')
                    ->whereIn('status_id', $status_array)
                    ->whereBetween('due_date', $days_array[$i])
                    ->where('user_id', $user->id)
                    ->sum('points');
                $user_data[] = $tasks;
            }
            $data[] = $user_data;

        }

        // dd($weeks_array[0]);

        $controller = $this;

        return view('reports.days_by_user', compact(
            'controller', 'request', 'users', 'days', 'from_date', 'to_date', 'data', 'days_array'));

    }

    // Manuel 2018-10-31
    public function projectsCustomerByStatuses(Request $request)
    {
        $user_id = 1;
        if (isset($request->user_id)) {
            $user_id = $request->user_id;

        }
        $projects = Project::where('status_id', '=', '3')->orderBy('name', 'asc')->get();
        $task_statuses = CustomerStatus::all();
        $users = User::all();
        $model = Customer::select(DB::raw('user_id, year(due_date) as year, week(due_date) as week ,  sum(points) as sum_points'))
            ->where('status_id', '=', 3)
            ->groupBy('year', 'week', 'user_id')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->get();
        foreach ($model as $item) {

            $item->name = User::getName($item->user_id);
        }
        $graph = $model;

        $controller = $this;

        return view('reports.projects', compact('model', 'request', 'graph', 'controller', 'users', 'projects', 'task_statuses'));

    }

    // INFORME DE SEGUIMIENTOS
    public function RFM_CustomersFollowups()
    {
        $model = \DB::table('view_customers_followup_by_weeks')
            ->where('year', '<>', '')
            ->whereNotNull('year')
            ->where('year', '<>', 0)
            ->orderBy('year', 'DESC')
            ->orderBy('week', 'DESC')
            ->get();

        return view('reports.views.ViewCustomersFollowups', compact('model'));
    }

    public function customersByMessageCount(Request $request): View
    {
        $fromDate = null;
        $toDate = null;

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $request->string('from_date'))->startOfDay();
            $toDate = Carbon::createFromFormat('Y-m-d', $request->string('to_date'))->endOfDay();
        }

        $customerMorph = (new Customer)->getMorphClass();
        $conversationIdsQuery = DB::table('wire_participants as wp')
            ->select('wp.conversation_id')
            ->whereColumn('wp.participantable_id', 'customers.id')
            ->where('wp.participantable_type', $customerMorph);

        $messagesCountQuery = DB::table('wire_messages')
            ->selectRaw('count(*)')
            ->whereIn('wire_messages.conversation_id', $conversationIdsQuery);

        $lastMessageAtQuery = DB::table('wire_messages')
            ->selectRaw('max(wire_messages.created_at)')
            ->whereIn('wire_messages.conversation_id', $conversationIdsQuery);

        $messagesExistQuery = DB::table('wire_messages')
            ->selectRaw('1')
            ->whereIn('wire_messages.conversation_id', $conversationIdsQuery);

        if ($fromDate && $toDate) {
            $messagesCountQuery->whereBetween('wire_messages.created_at', [$fromDate, $toDate]);
            $lastMessageAtQuery->whereBetween('wire_messages.created_at', [$fromDate, $toDate]);
            $messagesExistQuery->whereBetween('wire_messages.created_at', [$fromDate, $toDate]);
        }

        $model = Customer::query()
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'users.name as user_name',
                'customer_statuses.name as status_name',
                'customer_statuses.color as status_color',
                DB::raw("(select group_concat(case when nullif(trim(wm.body), '') is null then concat('[', coalesce(wm.type, 'mensaje'), ']') else wm.body end order by wm.created_at desc separator '\n') from (select wire_messages.body, wire_messages.type, wire_messages.created_at from wire_messages where wire_messages.sendable_id = customers.id order by wire_messages.created_at desc limit 5) as wm) as last_messages_body")
            )
            ->selectSub($messagesCountQuery, 'messages_count')
            ->selectSub($lastMessageAtQuery, 'last_message_at')
            ->leftJoin('users', 'users.id', '=', 'customers.user_id')
            ->leftJoin('customer_statuses', 'customer_statuses.id', '=', 'customers.status_id')
            ->whereExists($messagesExistQuery)
            ->orderByDesc('messages_count')
            ->orderByDesc('last_message_at')
            ->get();

        return view('reports.views.customers_by_message_count', compact('model', 'fromDate', 'toDate', 'request'));
    }

    public function scrollActive(Request $request)
    {
        if ($request->ajax()) {
            $model = \DB::table('view_customer_has_actions')
                ->where('id', '>', $request->id)
                ->whereNotNull('user_name')
                ->where(function ($query) use ($request) {
                    if (isset($request->user_id)) {
                        $query = $query->where('user_id', '=', $request->user_id);
                    }
                    $query = $query->where('creator_user_id', '=', $request->user_id);
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('user_id', '=', $request->user_id);
                        $query = $query->where('creator_user_id', '=', $request->user_id);
                    }
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        $query = $query->whereBetween('created_at_action_max', [$request->from_date, $request->to_date]);
                    }
                })
                ->orderBy('id', 'ASC')
                ->skip(0)->take(20)
                ->get();

            if (count($model) > 0) {
                return response()->json([
                    'response' => true,
                    'model' => $model,
                ]
                );
            }

            return response()->json([
                'response' => false,
            ]
            );
        }
        abort(403);
    }

    public function scrollInactive(Request $request)
    {
        if ($request->ajax()) {
            $model = \DB::table('view_customer_0_actions')
                ->where('id', '>', $request->id)
                ->whereNotNull('user_name')
                ->where(function ($query) use ($request) {
                    if (isset($request->user_id)) {
                        $query = $query->where('user_id', '=', $request->user_id);
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query = $query->where('user_id', '=', $request->user_id);
                    }

                    if (isset($request->from_date) && ($request->from_date != null)) {
                        $query = $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
                    }

                })
                ->orderBy('id', 'ASC')
                ->skip(0)->take(20)
                ->get();
            if (count($model) > 0) {
                return response()->json([
                    'response' => true,
                    'model' => $model,
                ]
                );
            }

            return response()->json([
                'response' => false,
            ]
            );
        }
        abort(403);
    }

    public function scrollInactiveWithOutUser(Request $request)
    {
        if ($request->ajax()) {
            $model = \DB::table('view_customer_0_actions')
                ->where('id', '>', $request->id)
                ->whereNull('user_name')
                ->where(function ($query) use ($request) {
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        $query = $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
                    }
                })
                ->orderBy('id', 'ASC')
                ->skip(0)->take(20)
                ->get();
            if (count($model) > 0) {
                return response()->json([
                    'response' => true,
                    'model' => $model,
                ]
                );
            }

            return response()->json([
                'response' => false,
            ]
            );
        }
        abort(403);
    }

    /** FIN DASHBOARD SIRENA**/
    public function getCustomerStatuses($request, $sid, $limit)
    {

        $model = Customer::where('status_id', $sid)
            ->where('notes', 'like', '%'.$request.'%')
            ->orderBy('created_at', 'DESC')
            ->paginate($limit);

        return $model;
    }

    public function getCustomerHash($str)
    {

        $model = Customer::where('notes', 'like', '%'.$str.'%')
            ->orderBy('created_at', 'DESC')
            ->get();

        return $model;
    }

    public function getCustomersGroupByMaker($request)
    {
        $dates = $this->getDates($request);
        $customersGroup = Customer::leftJoin('projects', 'customers.maker', 'projects.id')
            ->where(
                // Búsqueda por...
                function ($query) use ($request, $dates) {
                    if (isset($request->from_date) && ($request->from_date != null)) {

                        if ((isset($request->created_updated) && ($request->created_updated == 'updated'))) {
                            $query->whereBetween('customers.updated_at', $dates);
                        } else {
                            $query->whereBetween('customers.created_at', $dates);
                        }
                    }
                    $query->where('notes', 'like', '%'.$request->search.'%');

                }
            )

            ->select(DB::raw('projects.name as project_name, projects.color as project_color, count(customers.id) as count'))
            ->groupBy('projects.name')
            ->groupBy('projects.color')

            ->get();

        return $customersGroup;
    }

    public function countShowUpMiamiAbrioMail2024($request)
    {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#MiamiAbrioMail2024%';
        $searchAsisteUS2024 = '%#AsisteUS2024%';

        $count = Customer::where('notes', 'like', $str)
            ->where('notes', 'like', $searchAsisteUS2024)
            ->count();

        return $count;
    }

    public function countShowUpMiami2024($request)
    {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#miami2024%';
        $searchAsisteUS2024 = '%#AsisteUS2024%';

        $count = Customer::where('notes', 'like', $str)
            ->where('notes', 'like', $searchAsisteUS2024)
            ->count();

        return $count;
    }

    public function countShowUpTipoAUS($request)
    {
        // Asumiendo que $request->search contiene '#miami2024'
        $str = '%#TipoA-US%';
        $searchAsisteUS2024 = '%#AsisteUS2024%';

        $count = Customer::where('notes', 'like', $str)
            ->where('notes', 'like', $searchAsisteUS2024)
            ->count();

        return $count;
    }

    public function getDatesInterval(&$request, $days)
    {
        $from_date = Carbon::today()->subDays($days - 1);
        $to_date = Carbon::today(); // ayer

        $request->to_date = $to_date->format('Y-m-d');
        $request->from_date = $from_date->format('Y-m-d');
    }

    public function getCustomerGroup($request)
    {

        $pid = 1;
        $statuses = $this->getStatuses($request, $pid);

        $model = $this->countFilterCustomers($request, $statuses);

        return $model;
    }

    public function getStatuses(Request $request, $step)
    {

        if (isset($request->from_date) || ($request->from_date != '')) {
            $statuses = $this->getAllStatusID();
        } else {
            $statuses = $this->getStatusID($request, $step);
        }

        return $statuses;
    }

    public function getAllStatusID()
    {

        $res = [];
        $model = CustomerStatus::orderBy('weight', 'ASC')->get();

        foreach ($model as $item) {
            $res[] = $item->id;
        }

        return $res;
    }

    public function countFilterCustomers($request, $statuses)
    {

        $dates = $this->getDates($request);

        $customersGroup = Customer::wherein('customers.status_id', $statuses)
            ->rightJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id')
            ->where(
                // Búsqueda por...

                function ($query) use ($request, $dates) {

                    if (isset($request->from_date) && ($request->from_date != null)) {

                        if ((isset($request->created_updated) && ($request->created_updated == 'updated'))) {
                            $query->whereBetween('customers.updated_at', $dates);
                        } else {
                            $query->whereBetween('customers.created_at', $dates);
                        }
                    }
                    if (isset($request->user_id) && ($request->user_id != null)) {
                        $query->where('customers.user_id', $request->user_id);
                    }
                    if (isset($request->source_id) && ($request->source_id != null)) {
                        $query->where('customers.source_id', $request->source_id);
                    }
                    if (isset($request->status_id) && ($request->status_id != null)) {
                        $query->where('customers.status_id', $request->status_id);
                    }
                    if (isset($request->scoring) && ($request->scoring != null)) {
                        $query->where('customers.scoring', $request->scoring);
                    }

                    if (isset($request->project_id) && ($request->project_id != null)) {
                        $query->where('project_id', $request->project_id);
                    }
                    if (isset($request->kpi) && ($request->kpi != null)) {
                        $query->where('kpi', $request->kpi);
                    }
                    if (isset($request->search) && ($request->search != null)) {

                        $query->where(
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
                                $innerQuery->orwhere('customers.contact_name', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_phone2', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_email', 'like', '%'.$request->search.'%');
                                $innerQuery->orwhere('customers.contact_position', 'like', '%'.$request->search.'%');

                            }
                        );
                    }
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

    public function dashboard(Request $request)
    {

        $request7 = new Request;
        $request7->search = '#TipoA';
        $this->getDatesInterval($request7, 90);
        // reporte de clientes de los ultimos 7 dias
        $model7 = $this->getCustomerGroup($request7);
        // reporte de clientes de los ultimos 30 dias
        $request30 = new Request;
        $request30->search = '#TipoA';
        $this->getDatesInterval($request30, 10000);
        $model30 = $this->getCustomerGroup($request30);

        $quote_state = 6;
        $customersQuotes20 = $this->getCustomerStatuses($request7, $quote_state, 30);

        $customersByProject7 = $this->getCustomersGroupByMaker($request7);

        $customersByProject30 = $this->getCustomersGroupByMaker($request30);

        return view('reports.dashboard', compact('model7', 'model30', 'request7', 'request30', 'customersQuotes20',
            'customersByProject7', 'customersByProject30'));
    }

    public function dashboardMiami(Request $request)
    {

        $request7 = new Request;
        $request7->search = '#miami2024';
        $this->getDatesInterval($request7, 90000);
        // reporte de clientes de los ultimos 7 dias
        $model7 = $this->getCustomerGroup($request7);

        // dd($request7);
        // reporte de clientes de los ultimos 30 dias
        $request30 = new Request;
        $request30->search = 'TipoA-US';
        $this->getDatesInterval($request30, 10000);
        $model30 = $this->getCustomerGroup($request30);

        // reporte de clientes de los ultimos 30 dias
        $request3 = new Request;
        $request3->search = 'MiamiAbrioMail2024';
        $this->getDatesInterval($request3, 10000);
        $model3 = $this->getCustomerGroup($request3);

        $quote_state = 6;
        $customersQuotes20 = $this->getCustomerHash('#AsisteUS2024');

        $customersByProject7 = $this->getCustomersGroupByMaker($request7);
        $customersByProject30 = $this->getCustomersGroupByMaker($request30);
        $customersByProject3 = $this->getCustomersGroupByMaker($request3);

        $countShowUp1 = $this->countShowUpMiami2024($request30);
        $countShowUp2 = $this->countShowUpTipoAUS($request7);
        $countShowUp3 = $this->countShowUpMiamiAbrioMail2024($request3);

        return view('reports.dashboard_miami', compact('model7', 'model30', 'model3',
            'request7', 'request30', 'request3', 'customersQuotes20',
            'customersByProject7', 'customersByProject30', 'customersByProject3',
            'countShowUp1', 'countShowUp2', 'countShowUp3'));
    }

    /**
     * AJAX: calcula faltantes para un año/mes (y usuario opcional).
     * Devuelve: { ok, missing, customers }
     */
    public function verifyMonth(Request $request)
    {
        $wonStatusId = 8;
        $year = (int) $request->query('year');
        $month = (int) $request->query('month');
        $userId = $request->query('user_id'); // "unassigned"|"3"|null

        if (! $year || ! $month) {
            return response()->json(['ok' => false, 'message' => 'Parámetros inválidos'], 422);
        }

        // Cache por 10 min (por año/mes/usuario)
        $cacheKey = 'missing-summary:'.$year.':'.$month.':'.($userId ?: 'all');
        if ($cached = Cache::get($cacheKey)) {
            return response()->json(['ok' => true] + $cached);
        }

        // Filtro usuario
        $userFilter = function ($q) use ($userId) {
            if ($userId === 'unassigned') {
                $q->where(fn ($qq) => $qq->whereNull('user_id')->orWhere('user_id', 0));
            } elseif (! empty($userId)) {
                $q->where('user_id', $userId);
            }
        };

        // Traemos solo lo necesario
        $base = Customer::where('status_id', $wonStatusId)
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where($userFilter)
            ->with(['files:id,customer_id,url'])
            ->withCount('files')
            ->having('files_count', '>', 0)
            ->orderByDesc('updated_at');

        // Cliente S3/Spaces
        $disk = Storage::disk('spaces');
        $s3 = $disk->getClient();
        $bucket = config('filesystems.disks.spaces.bucket');

        $listKeys = function (int $customerId) use ($s3, $bucket) {
            $prefix = "files/{$customerId}/";
            $keyCache = "spaces:list:{$bucket}:{$prefix}";

            return Cache::remember($keyCache, now()->addMinutes(10), function () use ($s3, $bucket, $prefix) {
                $set = [];
                $token = null;
                try {
                    do {
                        $params = ['Bucket' => $bucket, 'Prefix' => $prefix, 'MaxKeys' => 1000];
                        if ($token) {
                            $params['ContinuationToken'] = $token;
                        }
                        $res = $s3->listObjectsV2($params);
                        foreach (($res['Contents'] ?? []) as $obj) {
                            $set[$obj['Key']] = true;
                        }
                        $token = ! empty($res['IsTruncated']) ? ($res['NextContinuationToken'] ?? null) : null;
                    } while ($token);
                } catch (\Throwable $e) {
                    \Log::warning("listObjectsV2 error {$prefix}: ".$e->getMessage());

                    return [];
                }

                return $set;
            });
        };

        // Recorremos en chunks (ligero)
        $totalCustomers = 0;
        $totalMissing = 0;

        $base->chunk(60, function ($chunk) use (&$totalCustomers, &$totalMissing, $listKeys) {
            foreach ($chunk as $customer) {
                $existing = $listKeys($customer->id);
                $missing = 0;
                foreach ($customer->files as $f) {
                    $key = "files/{$customer->id}/{$f->url}";
                    if (! isset($existing[$key])) {
                        $missing++;
                    }
                }
                $totalMissing += $missing;
                $totalCustomers += 1;
            }
        });

        $payload = ['missing' => $totalMissing, 'customers' => $totalCustomers];
        Cache::put($cacheKey, $payload, now()->addMinutes(10));

        return response()->json(['ok' => true] + $payload);
    }

    public function verifyCustomer(Request $request, Customer $customer)
    {
        // Traemos los files de la relación (id, url) y salimos rápido si no hay
        $files = $customer->files()->select('id', 'url')->get();
        if ($files->isEmpty()) {
            return response()->json([
                'ok' => true,
                'missing' => 0,
                'results' => [],
            ]);
        }

        // Cliente S3/Spaces
        $disk = Storage::disk('spaces');
        $s3 = $disk->getClient();
        $bucket = config('filesystems.disks.spaces.bucket');
        $prefix = "files/{$customer->id}/";

        // Cacheamos el listado de keys bajo /files/{customer_id}/ para no hacer HeadObject uno por uno
        $keyCache = "spaces:list:{$bucket}:{$prefix}";
        $existing = Cache::remember($keyCache, now()->addMinutes(10), function () use ($s3, $bucket, $prefix) {
            $set = [];
            try {
                $token = null;
                do {
                    $params = ['Bucket' => $bucket, 'Prefix' => $prefix, 'MaxKeys' => 1000];
                    if ($token) {
                        $params['ContinuationToken'] = $token;
                    }
                    $res = $s3->listObjectsV2($params);
                    foreach (($res['Contents'] ?? []) as $obj) {
                        $set[$obj['Key']] = true;
                    }
                    $token = ! empty($res['IsTruncated']) ? ($res['NextContinuationToken'] ?? null) : null;
                } while ($token);
            } catch (\Throwable $e) {
                \Log::warning("listObjectsV2 error {$prefix}: ".$e->getMessage());
            }

            return $set; // array<string,bool>
        });

        $missing = 0;
        $results = [];

        foreach ($files as $f) {
            $key = "{$prefix}{$f->url}";
            $exists = isset($existing[$key]);

            if (! $exists) {
                $missing++;
            }

            // Href: si tus files son privados, usa una ruta que genere URL temporal;
            // si son públicos, puedes usar $disk->url($key).
            // Como ya tienes una ruta para abrir, úsala:
            $href = route('customer_files.open', $f->id);

            $results[] = [
                'id' => $f->id,
                'exists' => $exists,
                'href' => $exists ? $href : null,
            ];
        }

        return response()->json([
            'ok' => true,
            'missing' => $missing,
            'results' => $results,
        ]);
    }

    public function missingCustomerFiles(Request $request)
    {
        $wonStatusId = 8;

        // Años disponibles (clientes ganados)
        $availableYears = Customer::where('status_id', $wonStatusId)
            ->whereNotNull('updated_at')
            ->selectRaw('YEAR(updated_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        // Filtros
        $selectedYear = (int) ($request->input('year') ?: ($availableYears->first() ?: 2025));
        $selectedMonth = $request->input('month');            // null | "1".."12"
        $selectedUserId = $request->input('user_id');          // null | "unassigned" | "3"

        // Usuarios con clientes ese año (para el combo)
        $userIdsThisYear = Customer::where('status_id', $wonStatusId)
            ->whereYear('updated_at', $selectedYear)
            ->whereNotNull('user_id')
            ->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIdsThisYear)->orderBy('name')->get();

        // Meses que tienen clientes ese año (para el resumen de nivel 1)
        $months = Customer::where('status_id', $wonStatusId)
            ->whereYear('updated_at', $selectedYear)
            ->selectRaw('MONTH(updated_at) as month')
            ->groupBy('month')->orderBy('month')
            ->pluck('month')->all(); // e.g. [1,2,3...]

        // Si NO hay mes: solo renderiza el resumen por meses
        if (empty($selectedMonth)) {
            return view('reports.missing_customer_files.index', [
                'availableYears' => $availableYears,
                'selectedYear' => $selectedYear,
                'selectedMonth' => null,
                'selectedUserId' => $selectedUserId,
                'users' => $users,
                'months' => $months,
                // En este nivel NO enviamos $customers
            ]);
        }

        // Si hay mes ⇒ armamos la lista de clientes de ese mes (nivel 2)
        $customersQuery = Customer::where('status_id', $wonStatusId)
            ->whereYear('updated_at', $selectedYear)
            ->whereMonth('updated_at', (int) $selectedMonth)
            ->with(['files:id,customer_id,url'])        // solo lo necesario
            ->withCount('files')
            ->orderByDesc('updated_at');

        if ($selectedUserId === 'unassigned') {
            $customersQuery->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', 0));
        } elseif (! empty($selectedUserId)) {
            $customersQuery->where('user_id', $selectedUserId);
        }

        $customers = $customersQuery->paginate(50); // paginado

        return view('reports.missing_customer_files.index', [
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'selectedMonth' => (int) $selectedMonth,
            'selectedUserId' => $selectedUserId,
            'users' => $users,
            'months' => $months,
            'customers' => $customers, // << clave para mostrar clientes
        ]);
    }
}
