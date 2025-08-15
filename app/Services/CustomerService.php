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
        $searchTerm = $request->search;
        $dates = $this->getDates($request);

        
        $query = Customer::query()
            ->leftJoin('customer_statuses', 'customers.status_id', '=', 'customer_statuses.id');


        $query->where(function ($query) use ($stage_id, $dates, $request, $searchTerm) {
            /*
            if (!empty($stage_id) && empty($searchTerm)) {
                $query->where('customer_statuses.stage_id', $stage_id);
            }
                */

            if (!empty($request->from_date)) {
                $column = ($request->created_updated === "created") ? 'created_at' : 'updated_at';
                $query->whereBetween("customers.$column", $dates);
            } elseif (empty($searchTerm)) {
                $query->whereBetween("customers.created_at", $dates);
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
            if (isset($request->scoring_profile)  && ($request->scoring_profile != null))
                        $query->where('customers.scoring_profile', $request->scoring_profile);
            if (!empty($request->inquiry_product_id)) $query->where('customers.inquiry_product_id', $request->inquiry_product_id);

            if (!empty($searchTerm)) {
                $normalizedSearch = $this->normalizePhoneNumber($searchTerm);
                $query->where(function ($innerQuery) use ($request, $normalizedSearch, $searchTerm) {
                    

                    if ($this->looksLikePhoneNumber($searchTerm)) {
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                        $innerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(customers.contact_phone2, ' ', ''), '-', ''), '(', '') LIKE ?", ["%$normalizedSearch%"]);
                    } elseif ($this->looksLikeEmail($searchTerm)) {
                        $innerQuery->orWhere('customers.email', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.contact_email', 'like', "%{$searchTerm}%");
                    } else {
                        $innerQuery->orWhere('customers.name', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.document', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.position', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.ad_name', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.adset_name', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.campaign_name', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.business', 'like', "%{$searchTerm}%")
                            ->orWhere('customers.notes', 'like', "%{$searchTerm}%");
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
                ->orderBy(DB::raw('COALESCE(customer_statuses.weight, 999)'), 'ASC') // Ordena y deja los null al final
            
                ->get();
        } else {
            $selectColumns = ['customers.*'];
            
            $result = $query->select($selectColumns)
                ->orderBy('customers.created_at', 'DESC')
                ->when($pageSize > 0, fn($q) => $q->paginate($pageSize), fn($q) => $q->get());
        }

        $result->action = "customers";
        return $result;
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


    public function getDates($request)
    {
        if (empty($request->from_date) && empty($request->search)) {
            // Sin filtros → mostrar últimos 90 días
            $from_date = Carbon\Carbon::today()->subDays(89)->format('Y-m-d'); // Incluye hoy
            $to_date = Carbon\Carbon::today()->endOfDay();
        }    else {
            // Filtros personalizados
            $from_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->from_date ?? Carbon\Carbon::today()->subDays(5000)->format('Y-m-d'));
            $to_date = Carbon\Carbon::createFromFormat('Y-m-d', $request->to_date ?? Carbon\Carbon::today()->subDays(2)->format('Y-m-d'));
            $from_date = $from_date->format('Y-m-d');
            $to_date = $to_date->format('Y-m-d') . " 23:59:59";
        }
    
        return [$from_date, $to_date];
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