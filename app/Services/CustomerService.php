<?php

namespace App\Services;

use DB;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\CustomerStatus;
use App\Models\Customer;

use App\Models\RoleProduct;

class CustomerService {
    public function filterCustomers(Request $request, $statuses, $stage_id, $countOnly = false, $pageSize = 0)
    {
        $status_array = CustomerStatus::where('stage_id', $stage_id)->pluck('id')->toArray();
        $dates = $this->getDates($request);

        $query = Customer::query();

        // Solo haces el join si se necesita para agrupar o mostrar status
        if ($countOnly) {
            $query->leftJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id');
        }

        $query->where(function ($query) use ($stage_id, $dates, $request) {
            if (!empty($stage_id) && empty($request->search)) {
                $query->where('customer_statuses.stage_id', $stage_id);
            }

            if (!empty($request->from_date)) {
                $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                $query->whereBetween("customers.$column", $dates);
            }

            if (!empty($request->user_id)) {
                $query->where('customers.user_id', $request->user_id === "null" ? null : $request->user_id);
            }

            if (isset($request->maker)) {
                is_numeric($request->maker) ? 
                    $query->where('customers.maker', $request->maker) : 
                    $query->whereNull('customers.maker');
            }

            if (!empty($request->product_id)) {
                $query->whereIn(
                    'customers.product_id', 
                    $request->product_id == 1 ? [1, 6, 7, 8, 9, 10, 11] : [$request->product_id]
                );
            }

            if (!empty($request->source_id)) $query->where('customers.source_id', $request->source_id);
            if (!empty($request->country)) $query->where('customers.country', $request->country);
            if (!empty($request->status_id)) $query->where('customers.status_id', $request->status_id);
            if (!empty($request->scoring_interest)) $query->where('customers.scoring_interest', $request->scoring_interest);
            if (!empty($request->inquiry_product_id)) $query->where('customers.inquiry_product_id', $request->inquiry_product_id);

            if (!empty($request->search)) {
                $normalizedSearch = $this->normalizePhoneNumber($request->search);
                $query->where(function ($innerQuery) use ($request, $normalizedSearch) {
                    

                    if ($this->looksLikePhoneNumber($request->search)) {
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    } elseif ($this->looksLikeEmail($request->search)) {
                        $innerQuery->orWhere('customers.email', 'like', "%{$request->search}%")
                            ->orWhere('customers.contact_email', 'like', "%{$request->search}%");
                    } else {
                        $innerQuery->orWhere('customers.name', 'like', "%{$request->search}%")
                            ->orWhere('customers.document', 'like', "%{$request->search}%")
                            ->orWhere('customers.position', 'like', "%{$request->search}%")
                            ->orWhere('customers.business', 'like', "%{$request->search}%")
                            ->orWhere('customers.notes', 'like', "%{$request->search}%");
                    }
                });
            }
        });

        // RESULTADO
        if ($countOnly) {
            $result = $query->select(
                    DB::raw('count(distinct(customers.id)) as count'),
                    'customers.status_id',
                    DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name'),
                    DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color'),
                )
                ->groupBy('customers.status_id', 'customer_statuses.name', 'customer_statuses.color')
                ->orderBy('customer_statuses.weight', 'ASC')
                ->get();
        } else {
            $selectColumns = ['customers.*'];
            if ($pageSize > 0 || $request->wants_status_info) {
                $selectColumns[] = DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name');
                $selectColumns[] = DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color');
            }

            $result = $query->select($selectColumns)
                ->orderBy('customers.created_at', 'DESC')
                ->when($pageSize > 0, fn($q) => $q->paginate($pageSize), fn($q) => $q->get());
        }

        $result->action = "customers/phase/" . $stage_id;
        return $result;
    }


    public function filterCustomers_2025_03(Request $request, $statuses, $stage_id, $countOnly = false, $pageSize = 0)
    {
        $status_array = CustomerStatus::where('stage_id', $stage_id)->pluck('id')->toArray();
        $dates = $this->getDates($request);

        $query = Customer::leftJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id')
            ->where(function ($query) use ($stage_id, $dates, $request) {
                if (!empty($stage_id) && empty($request->search))
                    $query->where('customer_statuses.stage_id', $stage_id);

                if (!empty($request->from_date)) {
                    $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                    $query->whereBetween("customers.$column", $dates);
                }

                if (!empty($request->user_id)) {
                    $query->where('customers.user_id', $request->user_id === "null" ? null : $request->user_id);
                }

                if (isset($request->maker)) {
                    is_numeric($request->maker) ? 
                        $query->where('customers.maker', $request->maker) : 
                        $query->whereNull('customers.maker');
                }
                

                if (!empty($request->product_id)) {
                    $query->whereIn(
                        'customers.product_id', 
                        $request->product_id == 1 ? [1, 6, 7, 8, 9, 10, 11] : [$request->product_id]
                    );
                }

                if (!empty($request->source_id)) $query->where('customers.source_id', $request->source_id);
                if (!empty($request->country)) $query->where('customers.country', $request->country);
                if (!empty($request->status_id)) $query->where('customers.status_id', $request->status_id);
                if (!empty($request->scoring_interest)) $query->where('customers.scoring_interest', $request->scoring_interest);
                if (!empty($request->inquiry_product_id)) $query->where('customers.inquiry_product_id', $request->inquiry_product_id);

                if (!empty($request->search)) {
                    $normalizedSearch = $this->normalizePhoneNumber($request->search);
                    $query->where(function ($innerQuery) use ($request, $normalizedSearch) {
                        $innerQuery->orWhere('customers.name', 'like', "%{$request->search}%")
                            ->orWhere('customers.email', 'like', "%{$request->search}%")
                            ->orWhere('customers.document', 'like', "%{$request->search}%")
                            ->orWhere('customers.position', 'like', "%{$request->search}%")
                            ->orWhere('customers.business', 'like', "%{$request->search}%")
                            ->orWhere('customers.notes', 'like', "%{$request->search}%")
                            ;

                        if ($this->looksLikePhoneNumber($request->search)) {
                            $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        }
                    });
                }
            });

        if ($countOnly) {
            $query = $query->select(
                DB::raw('count(distinct(customers.id)) as count'),
                'customers.status_id',
                DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name'),
                DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color'),
                
            )
            ->groupBy('customers.status_id', 'customer_statuses.name', 'customer_statuses.color') // Asegurar compatibilidad en MySQL
            ->orderBy('customer_statuses.weight', 'ASC')
            ->get();
        } else {
            
            if($pageSize>0){
                $query = $query->select(
                    'customers.*',
                    DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name'),
                    DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color') // Color por defecto
                )->orderBy('customers.created_at', 'DESC')
                ->paginate($pageSize);
            }else{
                $query = $query->select(
                    'customers.*',
                    DB::raw('COALESCE(customer_statuses.name, "Sin Estado") as status_name'),
                    DB::raw('COALESCE(customer_statuses.color, "#000000") as status_color') // Color por defecto
                )->orderBy('customers.created_at', 'DESC')
                ->get();
            }
            
        }

        $query->action = "customers/phase/" . $stage_id;
        return $query;
    }

    public function filterModelPhase(Request $request, $statuses, $stage_id)
    {
        $status_array = array();
        $statuses = CustomerStatus::where('stage_id', $stage_id)->get();

        foreach ($statuses as $item)
            $status_array[] = $item;
        $dates = $this->getDates($request);
        
        $dates = $this->getDates($request);
        
        $model = Customer::leftJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id')
            ->where(function ($query) use ( $stage_id, $dates, $request) {
                // filtra por fase si no está el campo search activado
                if ((isset($stage_id)  && ($stage_id != null)) && (!(isset($request->search) && (($request->search != "")))))
                    $query->where('customer_statuses.stage_id', $stage_id);
                // Filtrar por fase si no hay búsqueda de texto
           
                
                if (!empty($request->from_date)) {
                    $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                    
                    $query->whereBetween("customers.$column", $dates);
                }

                // Filtro de user_id
                if (!empty($request->user_id)) {
                    $query->where('customers.user_id', $request->user_id === "null" ? null : $request->user_id);
                }

                if (isset($request->maker)) {
                    is_numeric($request->maker) ? 
                        $query->where('customers.maker', $request->maker) : 
                        $query->whereNull('customers.maker');
                }

                // Filtro de producto
                if (!empty($request->product_id)) {
                    $query->whereIn(
                        'customers.product_id', 
                        $request->product_id == 1 ? [1, 6, 7, 8, 9, 10, 11] : [$request->product_id]
                    );
                } else {
                    //$query->whereIn('customers.product_id', $role_product_array);
                }

                

                // Filtros adicionales de acuerdo con la entrada
                if (!empty($request->source_id)) $query->where('customers.source_id', $request->source_id);
                if (!empty($request->country)) $query->where('customers.country', $request->country);
                if (!empty($request->status_id)) $query->where('customers.status_id', $request->status_id);
                if (!empty($request->scoring_interest)) $query->where('customers.scoring_interest', $request->scoring_interest);
                if (!empty($request->inquiry_product_id)) $query->where('customers.inquiry_product_id', $request->inquiry_product_id);

                if (isset($request->search)) {
                    $normalizedSearch = $this->normalizePhoneNumber($request->search);
                        
                    $query->where(function ($innerQuery) use ($request, $normalizedSearch) {
                        $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");
                        //dd($this->looksLikePhoneNumber($request->search));
                        if ($this->looksLikePhoneNumber($request->search)) {
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        }
                        $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.ad_name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.adset_name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.campaign_name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                    });
                }

            })
            ->select(
                'customers.id',
                //'customer_statuses.id AS status_id', 'customer_statuses.color AS color', 'customer_statuses.name as status_name', 
                'customers.product_id',
                'customers.user_id',
                'customers.maker',
                'customers.created_at',
                'customers.updated_at',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.country',
                'notes',
                'source_id',
                'scoring_interest',
                'scoring_profile',
                'customers.document',
                'customers.phone2',
                'customers.department',
                'customers.bought_products',
                'customers.contact_name',
                'customers.contact_email',
                'customers.position',
                'customers.contact_phone2',
                'customers.contact_position',
                'customers.business',
                'customers.updated_user_id',
                'customers.count_empanadas',
                'customers.empanadas_size',
                'customers.number_venues',
                'customers.campaign_name',
                'customers.adset_name',
                'customers.ad_name',
                'customers.status_id',
                'customers.image_url'
                
            )
            ->orderBy('customers.created_at', 'DESC')
            ->paginate(60);
        return $model;
    }

    public function getModelPhase(Request $request, $statuses, $phase_id)
    {
        $model = $this->filterModelPhase($request, $statuses, $phase_id);
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
        $model->action = "customers/phase/" . $phase_id;
        return $model;
    }

    // Funcion para borrar
    /*
    public function filterModelFull(Request $request, $statuses)
    {
        $model = Customer::leftJoin('users', 'users.id', 'customers.user_id')
            ->select(
                'users.name as user_name',
                'customers.id',
                'customers.status_id',
                'customers.product_id',
                'customers.user_id',
                'customers.created_at',
                'customers.updated_at',
                'customers.name',
                'customers.phone',
                'customers.phone2',
                'customers.country',
                'customers.email',
                'customers.rd_public_url',
                'customers.image_url'
            )
            ->where(
                // Búsqueda por...
                function ($query) use ($request) {
                    
                    $dates = $this->getDates($request);
                    
                    if (isset($request->from_date) && ($request->from_date!="")) {
                        $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                        $query = $query->whereBetween("customers.$column", $dates);
                        
                    }
                    
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('customers.user_id', $request->user_id);
                    if (isset($request->maker)  && ($request->maker != null))
                        $query = $query->where('customers.maker', $request->maker);
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->country)  && ($request->country != null))
                        $query = $query->where('customers.country', $request->country);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
                    if (isset($request->inquiry_product_id)  && ($request->inquiry_product_id != null))
                        $query->where('customers.inquiry_product_id', $request->inquiry_product_id);
                    if (isset($request->search)) {
                        $query->where(function ($innerQuery) use ($request) {
                            $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.phone',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.phone2',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.ad_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.adset_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.campaign_name', "like", "%" . $request->search . "%");
                        });
                    }
                }
            )
            ->orderBy('customers.status_id', 'asc')
            ->orderBy('customers.created_at', 'desc')
            ->get();
               
        return $model;
    }
    */

    public function filterModel(Request $request, $statuses)
    {
        //        $model = Customer::wherein('customers.status_id', $statuses)
        $dates = $this->getDates($request);
        $model = Customer::leftjoin('customer_statuses', 'customer_statuses.id', 'customers.status_id')
            ->select(
                'customers.maker',
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
                'customers.rd_public_url',
                'notes',
                'source_id',
                'scoring_interest',
                'scoring_profile',
                'customers.document',
                'customers.phone2',
                'customers.department',
                'customers.bought_products',
                'customers.contact_name',
                'customers.contact_email',
                'customers.position',
                'customers.contact_phone2',
                'customers.contact_position',
                'customers.business',
                'customers.updated_user_id',
                'customers.count_empanadas',
                'customers.empanadas_size',
                'customers.number_venues',
                'customers.image_url'
            )
            ->where(
                // Búsqueda por...
                function ($query) use ($request, $dates) {
                    if (isset($request->from_date) && $request->from_date) {
                        $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                        $query = $query->whereBetween("customers.$column", $dates);
                    }
                    if (isset($request->product_id)  && ($request->product_id != null)) {
                        if ($request->product_id == 1)
                            $query = $query->whereIn('customers.product_id', array(1, 6, 7, 8, 9, 10, 11));
                        else
                            $query = $query->where('customers.product_id', $request->product_id);
                    }
                    if (isset($request->user_id)  && ($request->user_id != null)) {
                        if ($request->user_id != "null")
                            $query = $query->where('customers.user_id', $request->user_id);
                        else
                            $query = $query->whereNull('customers.user_id');
                    }
                    if (isset($request->maker)  && ($request->maker != null)) {
                        if ($request->maker != "null") {
                            $query->where('customers.maker', $request->maker);
                        } else
                            $query->whereNull('customers.maker');
                    }
                    if (isset($request->source_id)  && ($request->source_id != null))
                        $query = $query->where('customers.source_id', $request->source_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('customers.status_id', $request->status_id);
                    if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                        $query->where('customers.scoring_interest', $request->scoring_interest);
                    if (isset($request->country)  && ($request->country != null))
                        $query->where('customers.country', $request->country);
                    if (isset($request->search)) {
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
                                //$innerQuery->orwhere('customers.status_temp',"like", "%".$request->search."%");
                                $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_phone2', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                                $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                                //$innerQuery->orwhere('actions.note',"like", "%".$request->search."%");
                            }
                        );
                    }
                }
            )
            ->select(
                'customers.maker',
                'customers.id',
                'customers.status_id',
                'customers.email',
                'customers.rd_public_url',
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
                'scoring_profile',
                'customers.document',
                'customers.phone2',
                'customers.department',
                'customers.bought_products',
                'customers.contact_name',
                'customers.contact_email',
                'customers.position',
                'customers.contact_phone2',
                'customers.contact_position',
                'customers.business',
                'customers.updated_user_id',
                'customers.count_empanadas',
                'customers.empanadas_size',
                'customers.number_venues',
                'customers.image_url'
            )
            ->orderBy('customer_statuses.weight', 'ASC')
            ->orderBy('customers.created_at', 'DESC')
            //->havingRaw('(count(if(outbound=0, actions.created_at, null)))','is not null')
            ->paginate(10);
        return $model;
    }
    
    // funcion descontinuada
    public function countFilterCustomers($request,  $statuses, $stage_id)
    {
        $status_array = array();
        foreach ($statuses as $item)
            $status_array[] = $item;
        $dates = $this->getDates($request);
        $customersGroup = Customer::leftJoin("customer_statuses", 'customers.status_id', 'customer_statuses.id')
            ->where(function ($query) use ($status_array, $stage_id, $request, $dates) {
                // filtra por fase si no está el campo search activado
                /*
                if ((isset($stage_id)  && ($stage_id != null)) && (!(isset($request->search) && (($request->search != "")))))
                    $query->where('customer_statuses.stage_id', $stage_id);
                
                if (isset($request->audience_id)  && ($request->audience_id != null)) {
                    $query->where('audience_customer.audience_id', $request->audience_id);
                }
                    */
                $column = 'updated_at';
                if (isset($request->from_date) && $request->from_date) {
                    if ($request->created_updated === "created") {
                        $column = 'created_at';
                    }
                    $query->whereBetween("customers.$column", $dates);
                }
                if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                    $query->where('customers.scoring_interest', $request->scoring_interest);
                if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                    $query->where('customers.scoring_profile', $request->scoring_profile);
                if (isset($request->country)  && ($request->country != null))
                    $query->where('customers.country', $request->country);
                if (isset($request->maker) && $request->maker !== null) {
                    // Verifica si maker es un número
                    if (is_numeric($request->maker)) {
                        $query->where('customers.maker', $request->maker);
                    } else {
                        $query->whereNull('customers.maker');
                    }
                }

                if (isset($request->inquiry_product_id)  && ($request->inquiry_product_id != null))
                    $query->where('customers.inquiry_product_id', $request->inquiry_product_id);
                if (isset($request->user_id)  && ($request->user_id != null)) {
                    if ($request->user_id == "null")
                        $query->whereNull('customers.user_id');
                    else
                        $query->where('customers.user_id', $request->user_id);
                }
                if (isset($request->source_id)  && ($request->source_id != null))
                    $query->where('customers.source_id', $request->source_id);
                if (isset($request->status_id)  && ($request->status_id != null))
                    $query->where('customers.status_id', $request->status_id);
                if (isset($request->product_id)  && ($request->product_id != null))
                    $query->where('customers.product_id', $request->product_id);
                if (isset($request->audience_id)  && ($request->audience_id != null)) {
                    $query->where('audience_customer.audience_id', $request->audience_id);
                }
                if (isset($request->search)) {
                    $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");

                            $normalizedSearch = $this->normalizePhoneNumber($request->search);
                            if ($this->looksLikePhoneNumber($request->search)) {
                                $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                                $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                                $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            }
                            $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                           
                            $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                            
                            $innerQuery->orwhere('customers.ad_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.adset_name', "like", "%" . $request->search . "%");
                            $innerQuery->orwhere('customers.campaign_name', "like", "%" . $request->search . "%");
                        }
                    );
                }
            })
            ->select(DB::raw('count(distinct(customers.id)) as count, customer_statuses.name as name, customer_statuses.color as color, customers.status_id as id'))
            ->orderBy('customer_statuses.weight', 'ASC')
            ->groupBy('customers.status_id')
            ->get();
        
        return $customersGroup;
    }
    public function countFilterCustomersJoin($request,  $statuses, $phase_id)
    {
        $statuses = CustomerStatus::all();
        $status_array = array();
        foreach ($statuses as $item)
            $status_array[] = $item;
        $dates = $this->getDates($request);
        $customersGroup = Customer::where(function ($join) use ($status_array, $phase_id, $request, $dates) {
            if (isset($request->audience_id)  && ($request->audience_id != null)) {
                $join->on("customer_statuses.id", "customers.status_id")->where('audience_customer.audience_id', $request->audience_id);
            }
            $column = 'updated_at';
            if (isset($request->from_date) && $request->from_date) {
                if ($request->created_updated === "created") {
                    $column = 'created_at';
                }
                $join->on("customer_statuses.id", "customers.status_id")->whereBetween("customers.$column", $dates);
            }
            if (isset($request->scoring_interest)  && ($request->scoring_interest != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.scoring_interest', $request->scoring_interest);
            if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.scoring_profile', $request->scoring_profile);
            if (isset($request->country)  && ($request->country != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.country', $request->country);
            if (isset($request->maker)  && ($request->maker != null)) {
                if ($request->maker != "null") {
                    $join->on("customer_statuses.id", "customers.status_id")->where('customers.maker', $request->maker);
                } else
                    $join->on("customer_statuses.id", "customers.status_id")->whereNull('customers.maker');
            }
            if (isset($request->user_id)  && ($request->user_id != null)) {
                if ($request->user_id == "null")
                    $join->on("customer_statuses.id", "customers.status_id")->whereNull('customers.user_id');
                else
                    $join->on("customer_statuses.id", "customers.status_id")->where('customers.user_id', $request->user_id);
            }
            if (isset($request->source_id)  && ($request->source_id != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.source_id', $request->source_id);
            if (isset($request->status_id)  && ($request->status_id != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.status_id', $request->status_id);
            if (isset($request->product_id)  && ($request->product_id != null))
                $join->on("customer_statuses.id", "customers.status_id")->where('customers.product_id', $request->product_id);
            if (isset($request->audience_id)  && ($request->audience_id != null)) {
                $join->on("customer_statuses.id", "customers.status_id")->where('audience_customer.audience_id', $request->audience_id);
            }
            if (isset($request->search)) {
                $join->on("customer_statuses.id", "customers.status_id")->where(
                    function ($innerQuery) use ($request) {
                        $innerQuery->orwhere('customers.name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.email',   "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.document', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.position', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.business', "like", "%" . $request->search . "%");

                        $normalizedSearch = $this->normalizePhoneNumber($request->search);
                        if ($this->looksLikePhoneNumber($request->search)) {
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                            $innerQuery->orwhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        }
                        $innerQuery->orwhere('customers.notes',   "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.city',    "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.country', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.bought_products', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_name', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_email', "like", "%" . $request->search . "%");
                        $innerQuery->orwhere('customers.contact_position', "like", "%" . $request->search . "%");
                    }
                );
            }
            if ($phase_id != null) {
                $join->on("customer_statuses.id", "customers.status_id")->whereIn('customers.status_id', $status_array);
            }
        })
            ->select(DB::raw('customers.status_id as status_id, count(distinct(customers.id)) as count'))
            ->groupBy('customers.status_id', 'customer_statuses.weight')
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

    

    public function getUserMenu($user)
    {
        $menu = Menu::select('menus.name', 'menus.url')
            ->leftJoin("role_menus", "menus.id", "role_menus.menu_id")
            ->leftJoin("roles", "roles.id", "role_menus.role_id")
            ->where("roles.id", $user->role_id)
            ->get();
        return $menu;
    }
    public function getCustomerWithParent($pid)
    {

        $parent = CustomerStatus::select(DB::raw('customer_statuses.*'))
            //->whereNotNull('customer_statuses.next_id')
            ->join('customer_statuses AS next', 'next.id', '=', 'customer_statuses.next_id')
            ->where('next.stage_id', '=', $pid)
            ->orderBy('customer_statuses.weight', 'ASC');
        $model = CustomerStatus::where('stage_id', '=', $pid)
            ->union($parent)
            ->orderBy('weight', 'ASC')
            ->get();
        //    dd($parent);
        return $model;
    }

    public function getDates($request)
    {
        $to_date = Carbon\Carbon::today()->subDays(2); // ayer
        $from_date = Carbon\Carbon::today()->subDays(5000);
        if (isset($request->from_date) && ($request->from_date != null)) {
            $from_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date);
            $to_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date);
        }
        $to_date = $to_date->format('Y-m-d') . " 23:59:59";
        $from_date = $from_date->format('Y-m-d');
        return array($from_date, $to_date);
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