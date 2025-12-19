<?php

namespace App\Services;

use DB;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use App\Models\Menu;
use App\Models\CustomerStatus;
use App\Models\Customer;

use App\Models\RoleProduct;

class CustomerService {

    public const STATUS_FILTER_UNASSIGNED = 'sin_estado';


    public function filterCustomers(Request $request, $statuses, $stage_id, $countOnly = false, $pageSize = 0, bool $applyDefaultDateRange = true, bool $onlyWithTags = false)
    {
        $t0 = function_exists('hrtime') ? hrtime(true) : (int) (microtime(true) * 1e9);

        $searchTerm = $request->search;
        $dates = $this->getDates($request);
        $authUser = Auth::user();
        $forceOwnCustomers = $authUser && ! $authUser->canViewAllCustomers();

        $query = Customer::query()
            ->with(['user', 'tags', 'status']) // needed for card view (asesor) y etiquetas
            ->leftJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id');

        $filterTagIds = array_values(array_filter(
            Arr::wrap($request->tag_id),
            fn ($id) => $id !== null && $id !== ''
        ));

        $statusFilter = $request->status_id;
        $filteringUnassignedStatus = ($statusFilter === self::STATUS_FILTER_UNASSIGNED);

        $query->where(function ($query) use (
            $stage_id,
            $dates,
            $request,
            $searchTerm,
            $forceOwnCustomers,
            $authUser,
            $applyDefaultDateRange,
            $onlyWithTags,
            $filterTagIds,
            $filteringUnassignedStatus,
            $statusFilter
        ) {
            if (! is_null($stage_id)) {
                $query->where('customer_statuses.stage_id', $stage_id);
            }

            if (!empty($request->from_date)) {
                $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                $query->whereBetween("customers.$column", $dates);
            } elseif ($applyDefaultDateRange && empty($searchTerm)) {
                $query->whereBetween("customers.created_at", $dates);
            }

            if ($forceOwnCustomers && empty($searchTerm)) {
                $query->where('customers.user_id', $authUser->id);
            } elseif (! $forceOwnCustomers && !empty($request->user_id)) {
                $query->where('customers.user_id', $request->user_id === "null" ? null : $request->user_id);
            }

            if (isset($request->maker)) {
                is_numeric($request->maker)
                    ? $query->where('customers.maker', $request->maker)
                    : $query->whereNull('customers.maker');
            }

            if (!empty($request->product_id)) {
                $query->whereIn(
                    'customers.product_id',
                    $request->product_id == 1 ? [1, 6, 7, 8, 9, 10, 11] : [$request->product_id]
                );
            }

            if (!empty($request->source_id))          $query->where('customers.source_id', $request->source_id);
            if (!empty($request->country))            $query->where('customers.country', $request->country);
            if ($filteringUnassignedStatus) {
                $query->where(function ($statusQuery) {
                    $statusQuery->whereNull('customers.status_id')
                        ->orWhereNull('customer_statuses.id');
                });
            } elseif (!empty($statusFilter)) {
                $query->where('customers.status_id', $statusFilter);
            }
            if (!empty($request->scoring_interest))   $query->where('customers.scoring_interest', $request->scoring_interest);
            if (isset($request->scoring_profile) && $request->scoring_profile !== null)
                                                    $query->where('customers.scoring_profile', $request->scoring_profile);
            if (!empty($request->inquiry_product_id)) $query->where('customers.inquiry_product_id', $request->inquiry_product_id);
            if (!empty($filterTagIds)) {
                $query->whereExists(function ($sub) use ($filterTagIds) {
                    $sub->select(DB::raw(1))
                        ->from('customer_tag')
                        ->whereColumn('customer_tag.customer_id', 'customers.id')
                        ->whereIn('customer_tag.tag_id', $filterTagIds);
                });
            }
            if ($onlyWithTags) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('customer_tag')
                        ->whereColumn('customer_tag.customer_id', 'customers.id');
                });
            }

            if (!empty($searchTerm)) {
                $query->where(function ($innerQuery) use ($searchTerm) {

                    $digits = preg_replace('/\D/', '', (string)$searchTerm);
                    $looksPhone = $digits !== '' && preg_match('/^\d{5,}$/', $digits);

                    if ($looksPhone) {
                        if ($looksPhone && strlen($digits) >= 4) {
                            $innerQuery->orWhere('customers.phone_last9', 'like', "%$digits%")
                                ->orWhere('customers.phone2_last9', 'like', "%$digits%")
                                ->orWhere('customers.contact_phone2_last9', 'like', "%$digits%")
                                ->orWhere('customers.phone', 'like', "%$digits%")
                                ->orWhere('customers.phone2', 'like', "%$digits%")
                                ->orWhere('customers.contact_phone2', 'like', "%$digits%");
                        }
                        if (strlen($digits) >= 9) {
                            $last9 = substr($digits, -9);
                            $innerQuery->orWhere('customers.phone_last9', $last9)
                                    ->orWhere('customers.phone2_last9', $last9)
                                    ->orWhere('customers.contact_phone2_last9', $last9);
                        } else {
                            $innerQuery->orWhere('customers.phone',          'like', $digits.'%')
                                    ->orWhere('customers.phone2',         'like', $digits.'%')
                                    ->orWhere('customers.contact_phone2', 'like', $digits.'%');
                        }
                    } elseif (filter_var($searchTerm, FILTER_VALIDATE_EMAIL) !== false) {
                        $innerQuery->orWhere('customers.email',         'like', "%{$searchTerm}%")
                                ->orWhere('customers.contact_email', 'like', "%{$searchTerm}%");
                    } else {
                        $like = "%{$searchTerm}%";
                        $innerQuery->orWhere('customers.name',          'like', $like)
                                ->orWhere('customers.document',      'like', $like)
                                ->orWhere('customers.city',          'like', $like)
                                ->orWhere('customers.department',    'like', $like)
                                ->orWhere('customers.ad_name',       'like', $like)
                                ->orWhere('customers.adset_name',    'like', $like)
                                ->orWhere('customers.campaign_name', 'like', $like)
                                ->orWhere('customers.business',      'like', $like)
                                ->orWhere('customers.notes',         'like', $like);
                    }
                });
            }
        });

        if ($request->filled('has_quote')) {
            if ($request->has_quote === '1') {
                $query->whereHas('orders', function ($q) {
                    $q->whereNull('invoice_id');
                });
            } elseif ($request->has_quote === '0') {
                $query->whereDoesntHave('orders', function ($q) {
                    $q->whereNull('invoice_id');
                });
            }
        }

        // Ordenamiento personalizado
        $sort = $request->get('sort');
        $allowedSorts = ['recent', 'last_action', 'advisor', 'status'];
        $sortKey = in_array($sort, $allowedSorts, true) ? $sort : 'status';

        if ($sortKey === 'last_action') {
            $query->leftJoin(DB::raw('(SELECT customer_id, MAX(created_at) AS last_action_at FROM actions GROUP BY customer_id) AS last_actions'), 'last_actions.customer_id', '=', 'customers.id')
                ->orderBy('last_actions.last_action_at', 'DESC');
        } elseif ($sortKey === 'advisor') {
            $query->leftJoin('users as advisor_sort', 'advisor_sort.id', '=', 'customers.user_id')
                ->orderBy('advisor_sort.name', 'ASC');
        } elseif ($sortKey === 'recent') {
            $query->orderBy('customers.created_at', 'DESC');
        } elseif ($sortKey === 'status') {
            $query->orderByRaw('COALESCE(customer_statuses.weight, 9999) ASC')
                ->orderBy('customers.created_at', 'DESC');
        }

        // Ejecutar y medir
        if ($countOnly) {
            // Evitar ORDER BY con columnas no agregadas en modo only_full_group_by
            $query->getQuery()->orders = null;
            $result = $query->select(
                    DB::raw('count(distinct(customers.id)) as count'),
                    'customers.status_id',
                    DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name'),
                    DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color'),
                    DB::raw('customer_statuses.id as status_table_id')
                )
                ->groupBy('customers.status_id', 'customer_statuses.name', 'customer_statuses.color', 'customer_statuses.id')
                ->orderBy(DB::raw('COALESCE(customer_statuses.weight, 999)'), 'ASC')
                ->get();
        } else {
            $selectColumns = ['customers.*'];
            $result = $query->select($selectColumns)
                ->orderBy('customers.created_at', 'DESC')
                ->when($pageSize > 0, fn($q) => $q->paginate($pageSize), fn($q) => $q->get());
        }

        $t1 = function_exists('hrtime') ? hrtime(true) : (int) (microtime(true) * 1e9);
        $elapsedMs = ($t1 - $t0) / 1e6; // ns → ms

        // ⚠️ Añadir propiedades dinámicas en objetos paginator puede emitir deprecations en PHP 8.2.
        // Si ya añadías "action", seguimos igual por compatibilidad:
        $result->action = "customers";
        $result->benchmark_ms = round($elapsedMs, 2);

        $annotateAccess = function ($item) use ($authUser) {
            if (method_exists($item, 'hasFullAccess')) {
                $item->limited_access = ! $item->hasFullAccess($authUser);
            }
            return $item;
        };

        if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator || $result instanceof \Illuminate\Pagination\Paginator) {
            $result->getCollection()->transform($annotateAccess);
        } else {
            $result = $result->map($annotateAccess);
        }

        return $result;
    }
    
    
 
    

    public function getUserMenu($user)
    {
        $menu = Menu::select('menus.name', 'menus.url')
            ->leftJoin("role_menus", "menus.id", "role_menus.menu_id")
            ->leftJoin("roles", "roles.id", "role_menus.role_id")
            ->where("roles.id", $user->role_id)
            ->whereNotIn('menus.name', ['Posventa', 'Logistica', 'Logística'])
            ->whereNotIn('menus.url', ['/customers/phase/3', '/customers/phase/4'])
            ->get();
        return $menu;
    }


    public function getDates($request)
    {
        if (empty($request->from_date) && empty($request->to_date) && empty($request->search)) {
            $from = Carbon\Carbon::today()
                ->subDay()
                ->setTime(17, 0, 0)
                ->format('Y-m-d H:i:s');
            $to = Carbon\Carbon::today()->endOfDay()->format('Y-m-d H:i:s');
            return [$from, $to];
        }

        $from = $request->from_date
            ? Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay()
            : Carbon\Carbon::createFromFormat('Y-m-d', '1900-01-01')->startOfDay();

        $to = $request->to_date
            ? Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay()
            : Carbon\Carbon::today()->endOfDay();

        return [$from->format('Y-m-d'), $to->format('Y-m-d H:i:s')];
    }

    

    public function getLead($lead)
    {
        $str = "";
        switch ($lead) {
            case "7":
                $str = "lead07";
                break;
            case "14":
                $str = "lead14";
                break;
            case "21":
                $str = "lead21";
                break;
            case "28":
                $str = "lead28";
                break;
            case "total":
                $str = "has_actions";
                break;
            default:
                $str = "lead";
                break;
        }
        return $str;
    }

    public function getRoleProducts()
    {
        $role_products_elocuent = RoleProduct::where('role_id', '=', Auth::user()->role_id)->get();
        foreach ($role_products_elocuent as $item) {
            $role_product_array[] = $item->product_id;
        }
        if (isset($role_product_array) && $role_product_array != "")
            return $role_product_array;
        else
            $role_product_array = "";
        return $role_product_array;
    }

    public function getModelPhase(Request $request, $statuses, $stageId, int $pageSize = 10, bool $applyDefaultDateRange = true, bool $onlyWithTags = false)
    {
        $model = $this->filterCustomers($request, $statuses, $stageId, false, $pageSize, $applyDefaultDateRange, $onlyWithTags);

        foreach ($model as $item) {
            if (isset($item->status_id)) {
                $status = CustomerStatus::find($item->status_id);
                $item->status_name = $status->name ?? null;
            }
        }

        if ($model instanceof LengthAwarePaginator || $model instanceof Paginator) {
            $model->getActualRows = min($model->currentPage() * $model->perPage(), $model->total());
        } else {
            $model->getActualRows = $model->count();
        }

        $model->action = $request->path();

        return $model;
    }

    public function countFilterCustomers(Request $request, $statuses, $stageId, bool $applyDefaultDateRange = true, bool $onlyWithTags = false)
    {
        return $this->filterCustomers($request, $statuses, $stageId, true, 0, $applyDefaultDateRange, $onlyWithTags);
    }

    public function filterModel(Request $request, $statuses)
    {
        return $this->getModelPhase($request, $statuses, null);
    }

    function looksLikePhoneNumber($input)
    {
        return preg_match('/^\+?\d{1,4}(\s|-)?(\d{1,4}(\s|-)?){1,4}$/', $input);
    }
    // Función para normalizar números de teléfono
    function normalizePhoneNumber($phoneNumber)
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }
    function looksLikeEmail($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }

}
