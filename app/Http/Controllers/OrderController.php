<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Models\Category;
use App\Models\Customer;
use App\Models\OrderTransaction;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Config;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Country;


use Illuminate\Support\Facades\Auth;

class OrderController extends Controller{
    

    public function index(Request $request){

        $model = $this->getModel($request);
        $group = $this->groupModel($request);
        $statuses = OrderStatus::where("status_id", "=", 1)->get();
        $payments = Payment::all();
        $users = User::all();
        
          //  dd($model);

        // Crear la vista
        $view = view('orders.index', compact('request', 'model','group', 'statuses',  'users', 'payments' ));

        // Crear la respuesta y adjuntar la cookie
        $response = response($view);

        return $response;
    }

    public function indexReport(Request $request, $sid){
        $request->status_id = $sid;
        $model = $this->getModelFromReport($request);
        $group = $this->groupModelFromReport($request);
        $statuses = OrderStatus::find($sid);
        $users = User::all();
        $categories = Category::whereNotNull('parent_id')->get();

        return view('orders.index', compact('request', 'model','group', 'statuses', 'categories', 'users' ));
    }

    public function getDates($request){
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(3000);

        if(isset($request->from_date) && ($request->from_date!=null)){
            
            
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date);
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date);
        }

        $to_date = $to_date->format('Y-m-d')." 23:59:59";
        $from_date = $from_date->format('Y-m-d');

        return array($from_date, $to_date); 
    }

    public function getModel($request){	
        $model = Order::where(
        		
                // Búsqueda por...
                function ($query) use ($request) {
                    $dates = $this->getDates($request);
                   
                    /*
                    if (isset($request->from_date) && ($request->from_date != null)) {

                        if ( (isset($request->created_updated) &&  ($request->created_updated=="updated")) ) 
                            $query->whereBetween('orders.delivery_date', $dates);
                        else
                            $query->whereBetween('orders.created_at', $dates);
                    }
                    */
                    if (isset($request->payment_id)  && ($request->payment_id != null))
                    $query = $query->where('payments.id', $request->payment_id);
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('orders.status_id', $request->status_id);
                    if (isset($request->category_id)  && ($request->category_id != null))
                        $query = $query->where('category_id', $request->category_id);
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('user_id', $request->user_id);
                        if (isset($request->notes)  && ($request->notes != null))
                        $query = $query->where('notes', $request->notes);
                    
                    if (isset($request->search)  && ($request->search != null))
                    	$query = $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery = $innerQuery->orwhere('products.name', "like", "%" . $request->search . "%");
                    
                        }
                    );
                    
                    
                
                }


            )
        	->select(DB::raw('orders.created_at, orders.status_id, user_id, orders.notes, orders.id, customer_id, delivery_date, delivery_address,  payment_id ,customer_id'))
            ->orderBy('delivery_date', 'DESC')
            ->paginate(50);
        
        return $model;
    }

    public function getModelFromReport($request){ 
        $model = Order::join('products', 'products.id', 'product_id')
            ->where(
                function ($query) use ($request) {
                    if(isset($request->week)){
                        $from_date = $this->getFromDate($request->week);
                        $to_date = $this->getToDate($request->week);
                        //dd($from_date);
                        $query->whereBetween('orders.created_at', array($from_date, $to_date));
                    }
                    if (isset($request->category_id)  && ($request->category_id != null)){
                        if($request->category_id==1){
                            $query = $query->whereIn('products.category_id', array(4,5,6));
                        }else if($request->category_id==2){
                            $query = $query->whereIn('products.category_id', array(1,2,3));
                        }
                    } 
                }
            )
            ->select(DB::raw('cast(products.name as UNSIGNED) as int_name, name, orders.status_id, user_id, category_id, orders.id, product_id,  customer_id'))
            ->whereIn('user_id', [3,6,7,9,10])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        return $model;
    }

    public function getFromDate($week){
        $from_date = \Carbon\Carbon::create(2021, 1, 1, 0, 0, 0, 'America/Bogota');
        $from_date = $from_date->addWeek($week-1)->addDays(3);
        return $from_date->format('Y-m-d');
    }
    public function getToDate($week){
        $to_date = \Carbon\Carbon::create(2021, 1, 1, 0, 0, 0, 'America/Bogota');
        $to_date = $to_date->addWeek($week-1)->addDays(3);
        $to_date = $to_date->addDays(6);
        return $to_date->format('Y-m-d');
    }


    public function groupModel($request){   
        $model = OrderStatus::leftJoin('orders','order_statuses.id', 'orders.status_id')
            ->where('order_statuses.status_id', 1)
            ->where(
                
                // Búsqueda por...
                function ($query) use ($request) {
                    /*
                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('orders.status_id', $request->status_id);

                    
                    */
                    if (isset($request->category_id)  && ($request->category_id != null))
                        $query = $query->where('products.category_id', $request->category_id);
                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('user_id', $request->user_id);
                    
                    if (isset($request->search)  && ($request->search != null))
                        $query = $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery = $innerQuery->orwhere('orders.name', "like", "%" . $request->search . "%");
                    
                        }
                    );
                    
                
                }
                

            )
            ->select(DB::raw('order_statuses.id as status_id, order_statuses.name, order_statuses.color, count(orders.id) as count'))
            ->groupBy('order_statuses.id')
            ->groupBy('order_statuses.name')
            ->groupBy('order_statuses.color')
            ->groupBy('weight')
            ->orderBy('weight', 'ASC')
            ->get();
        
        
        return $model;
    }

    public function groupModelFromReport($request){   
        $model = Order::rightJoin('product_statuses','product_statuses.id', 'status_id')
            ->join('products', 'products.id', 'product_id')
            ->where(
                
                // Búsqueda por...
                function ($query) use ($request) {
                    if(isset($request->week)){
                        $from_date = $this->getFromDate($request->week);
                        $to_date = $this->getToDate($request->week);
                        //dd($from_date);
                        $query->whereBetween('orders.created_at', array($from_date, $to_date));
                    }else 
                    if (isset($request->from_date) && ($request->from_date != null)) {
                        $query->whereBetween('orders.updated_at', array($request->from_date, $request->to_date));
                    }else{
                        $query->whereBetween('orders.created_at', array($request->from_date, $request->to_date));
                    }


                    if (isset($request->status_id)  && ($request->status_id != null))
                        $query = $query->where('orders.status_id', $request->status_id);

                    if (isset($request->category_id)  && ($request->category_id != null))
                        if($request->category_id==1){
                            $query = $query->whereIn('products.category_id', array(4,5,6));
                        }else if($request->category_id==2){
                            $query = $query->whereIn('products.category_id', array(1,2,3));
                        }
                        


                    if (isset($request->user_id)  && ($request->user_id != null))
                        $query = $query->where('user_id', $request->user_id);
                    
                    if (isset($request->search)  && ($request->search != null))
                        $query = $query->where(
                        function ($innerQuery) use ($request) {
                            $innerQuery = $innerQuery->orwhere('orders.name', "like", "%" . $request->search . "%");
                    
                        }
                    );
                
                }

            )
            ->select(DB::raw('product_statuses.id as status_id, product_statuses.name, product_statuses.color, count(orders.id) as count'))
            ->whereIn('user_id', [3,6,7,9,10])
            ->groupBy('product_statuses.id')
            ->groupBy('product_statuses.name')
            ->groupBy('product_statuses.color')
            ->groupBy('weight')
            ->orderBy('weight', 'ASC')
            ->get();
        
        return $model;
    }


    public function show($id){
        $model = Order::find($id);
        $statuses = OrderStatus::all();
        $products = Product::where("status_id", 1)->get();
        $product_types = ProductType::all();
        $referal = User::where('role_id', 3)->get();
        $users = User::where('role_id', 1)->get();

        return view('orders.show', compact('model', 'referal', 'users','statuses', 'products', 'product_types'));
    }

    public function quote($id){
        $model = Order::find($id);
        $parking = Product::find($model->parking_id);
        //dd($parking);
        $debits = OrderTransaction::where('order_id', $id)
            ->where('debit',  '>', 0)
            ->get();

        $credits = OrderTransaction::where('order_id', $id)
            ->where('credit', '>', 0)
            ->get();
        
        $transactions = OrderTransaction::where('order_id', $id)
            ->orderby('date', 'asc')
            ->get();

        $products = Product::all();
        $product_types = ProductType::all();        


        return view('orders.quote', compact('model', 'debits', 'credits', 'transactions', 'products', 'product_types', 'parking'));
    }


    public function editquote($id){
        $model = Order::find($id);
        //dd($model);
        if(isset($model->customer_id)){
            $customerId = $model->customer_id;
            $customer = Customer::find($customerId);
        }
        

        //$statuses = OrderStatus::all();
        //$types = OrderType::all();
        $categories = Category::orderBy("weight")->get();
        //$finish_status = [];
        $customers = Customer::orderBy('name', 'ASC')->get();
        $products = Product::whereIn('type_id', [1, 22, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 47, 48, 49, 50, 51, 52, 53])->where('status_id', '=', 1)->get();
        $deposits = Product::whereIn('type_id', [2,9,8, 43,45,46])
            ->join('product_types', 'product_types.id', '=','products.type_id')
            ->where('status_id', '=', 1)
            ->orderBy('product_types.name', 'ASC')
            ->select(DB::raw('products.name, products.price, products.id, products.category_id, products.type_id'))
            ->get();
        $parkings = Product::whereIn('type_id', [3,5,6,11,12,13,15,39,40,41])
            ->join('product_types', 'product_types.id', '=','products.type_id')
            ->where('status_id', '=', 1)
            ->orderBy('product_types.name', 'ASC')
            ->select(DB::raw('products.name, products.price, products.id, products.category_id, products.type_id'))
            ->get();
        //$customers = Customer::where('status_id','=', 8)->orderBy('name', 'ASC')->get();
        return view('quotes.edit', compact(/*'statuses', */'categories',   'customers', 'products', 'parkings', 'deposits', 'customer', 'model'));
    }

    public function updatequote($oid, Request $request)
    {

        $product= Product::find($request->product_id);
        $product->location = $request->location;
        $product->save();


        $sid = $request->status_id;
        $model = Order::find($oid);
        $model->status_id = $sid;
        $model->user_id = Auth::id();

        $model->subsidy = $request->subsidy;
        $model->subsidy_status = $request->subsidy_status;
        $model->subsidy_date = $request->subsidy_date;
        $model->balance_subsidy = $request->balance_subsidy;
        $model->initial_installment_subsidy = $request->initial_installment_subsidy;
        $model->initial_installment_percent = $request->initial_installment_percent;
        
        $model->initial_installment_subsidy = $request->initial_installment_subsidy;
        
        
        $model->down_payment = $request->down_payment;
        $model->savings_value = $request->savings;

        // precios digitados
        $model->price_black_work = $request->price_black_work;
        $model->price_semi_finished = $request->price_semi_finished;
        $model->price_fully_finished = $request->price_fully_finished;
        $model->deposit_price = $request->deposit_price;
        $model->parking_price = $request->parking_price;


        
        
        //Credit.
        $model->credit = $request->credit;
        $model->credit_status = $request->credit_status;
        $model->credit_value = $request->credit_value;

        //Otros
        $model->real_state_note = $request->real_state_note;
        $model->releases = $request->releases;
        $model->fiduciary_commission = $request->fiduciary_commission;

        //Titular
        $model->customer_id = $request->customer_id;
        //dd($request->parking_id);
        // product
        if(is_numeric($request->product_id) && ($request->product_id!="")){
            if($sid==2){
                $this->changeStatusProduct($model->product_id, 1);
                $this->changeStatusProduct($request->product_id, 3);
            }
            $model->product_id = $request->product_id;
            
        }else{
            if($sid==2){
                $this->changeStatusProduct($model->product_id, 1);
            }
            $model->product_id = null;
        }
        $model->initial_installment = $request->initial_installment;
        $model->finishes_value = $request->finishes_value;
        $model->discount_interest = $request->discount_interest;

        
        //Adicionales
        if(is_numeric($request->parking_id) && ($request->parking_id!="")){
            if($sid==2){
            
            $this->changeStatusProduct($model->parking_id, 1);
            $this->changeStatusProduct($request->parking_id, 3);
            }
            $model->parking_id = $request->parking_id;
        }else{
            if($sid==2){
            
            $this->changeStatusProduct($model->parking_id, 1);
            }
            $model->parking_id = null;
        }
        if(is_numeric($request->deposit_id) && ($request->deposit_id!="")){
            if($sid==2){
            $this->changeStatusProduct($model->deposit_id, 1);
            $this->changeStatusProduct($request->deposit_id, 3);
            }
            $model->deposit_id = $request->deposit_id;
        }else{
            if($sid==2){
            $this->changeStatusProduct($model->deposit_id, 1);
            }
            $model->deposit_id = null;
        }
        
        $model->notes = $request->notes;
        $model->save();
        //dd($model);
        
        $path = '/orders/sid/'.$model->status_id;
        //dd($path);
        return redirect($path);

        //return $request->all();
 
    }



    public function store_payment_plan($id, Request $request){

        $model = new OrderTransaction;
        $model->date = $request->date;
        $model->description = $request->description;
        $model->internal_id = $request->internal_id;
        $model->debit = $request->debit;

        $model->save();

        return redirect('/orders/'.$id.'/show');
    }

    public function store_payment($id, Request $request){

        $model = new OrderTransaction;
        $model->date = $request->date;
        $model->description = $request->description;
        $model->internal_id = $request->internal_id;
        $model->credit = $request->credit;

        $model->save();

        return redirect('/orders/'.$id.'/show');
    }


    public function edit($id){

   
        $model = Order::find($id);
        
        
        $products = Product::where("status_id", 1)->get();
        $statuses = OrderStatus::all();
        $user =  Auth::id();
        $users = User::where('status_id', 1)->get();
       
        return view('orders.edit', compact( 'model',  'users', 'user',  'products', 'statuses'));
        
    }

   

   

    public function store_pagos(Request $request){
        return $request->all();
    }

    public function getProductTypes($parent_id){

    }
    public function create($cid){

        $customer = Customer::find($cid);
        $products = Product::all();
        $model = new Order;
        $model->customer = $customer;
        $statuses = OrderStatus::all();
        $users = User::where('status_id', 1)
            ->orderBy('name', 'ASC')
            ->get();
        $user_id = Auth::id();
        $countries = Country::all();
        //quotes.create
       return view("orders.create", compact('customer', 'products','model','statuses', 'users', 'user_id','countries'));
    }



    public function showQuote($order_id){

        $order = Order::find($order_id);
        $user_id = $order->user_id;
        $orderProduct = OrderProduct::where('order_id',$order_id)->get();
        $count = OrderProduct::where('order_id',$order_id)->count();
        $user = User::find($user_id);
        $customer_id = $order->customer_id;
        $customer = Customer::find($customer_id);

       return view('orders.quote', compact('order', 'orderProduct','user','customer','count'));
    }


    public function updateCustomer(Request $request){

        
        $customer = Customer::updateOrCreate(
            ['id' => $request->customer_id], // Cláusula where para buscar por documento
            [
                // Datos personales
                'name' => $request->name,
                'document' => $request->document,
                'position' => $request->position,
                'area_code' => $request->area_code,
                'phone' => $request->phone,
                'contact_phone2' => $request->contact_phone2, 
                'phone2' => $request->phone2, 
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'department' => $request->department,
    
                // Datos empresariales
                'business' => $request->business,
                'business_document' => $request->business_document,
                'business_phone' => $request->business_phone,
                'business_area_code' => $request->business_area_code,
                'business_address' => $request->business_address,
                'business_city' => $request->business_city,
                'business_email' => $request->business_email,
                
                
    
                // Otros campos
                // 'campo_adicional' => $request->campo_correspondiente
            ]
        );
    }



    public function store(Request $request){
        
        $this->updateCustomer($request);
        
        $model = new Order;
        
        $model->delivery_phone = $request->delivery_phone;
        

        $model->delivery_address = $request->delivery_address;
        $model->delivery_city = $request->delivery_city;
        $model->delivery_country = $request->delivery_country;
        $model->delivery_postal_code = $request->delivery_postal_code;
        $model->notes = $request->notes;
        $model->updated_user_id = Auth::id();

        $model->customer_id = $request->customer_id;
        $model->status_id = $request->status_id;

        $model->user_id = $request->user_id;
        $model->referal_user_id = $request->referal_user_id;
        
        $model->save();
        

        
        return redirect('/orders/' . $model->id . '/add/product');
 
    }




    public function changeStatusProduct($pid, $sid){
        $model = Product::find($pid);
        if($model){
            $model->status_id = $sid;

            $model->save();
        }
    }

    public function update($id, Request $request){
        $this->updateCustomer($request);
        
        $model = Order::find($id);

        if(isset($request->payment_id) && $request->payment_id !="")
          $model->payment_id = $request->payment_id;


        if(isset($request->delivery_name) && $request->delivery_name !="")
          $model->delivery_name = $request->delivery_name;
          //$model->delivery_document = $request->delivery_document;

        if(isset($request->delivery_phone) && $request->delivery_phone !="")
          $model->delivery_phone = $request->delivery_phone;

        if(isset($request->delivery_date) && $request->delivery_date !="")
          $model->delivery_date = $request->delivery_date;

        if(isset($request->delivery_address) && $request->delivery_address !="")
          $model->delivery_address = $request->delivery_address;

        if(isset($request->delivery_email) && $request->delivery_email !="")
          $model->delivery_email = $request->delivery_email;
        
        if(isset($request->notes) && $request->notes !="")  
          $model->notes = $request->notes;
          
          
        if(isset($request->customer_id) && $request->customer_id !="")
          $model->customer_id = $request->customer_id;
         
        if(isset($request->status_id) && $request->status_id !="") 
          $model->status_id = $request->status_id;

        if(isset($request->delivery_city) && $request->delivery_city !="") 
          $model->delivery_city = $request->delivery_city;

        if(isset($request->delivery_country) && $request->delivery_country !="") 
          $model->delivery_country = $request->delivery_country;

        if(isset($request->user_id) && $request->user_id !="")
          $model->user_id = $request->user_id;

        if(isset($request->delivery_postal_code) && $request->delivery_postal_code !="")
          $model->delivery_postal_code = $request->delivery_postal_code;

        if(isset($request->referal_user_id) && $request->referal_user_id !="")
          $model->referal_user_id = $request->referal_user_id;

        if(isset($request->description) && $request->description !="")
          $model->description = $request->description;
        
        $model->updated_user_id = Auth::id();

        $model->save();

        
        return redirect('/orders/'.$model->id.'/show');
    }

        public function searchCustomer(Request $request){
        $model = Customer::
            select('*')
            ->where(
                // Búsqueda por...
             function ($query) use ($request) {
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
                            $innerQuery->orwhere('customers.contact_name',"like", "%".$request->search."%");
                            $innerQuery->orwhere('customers.contact_phone2',"like", "%".$request->search."%");
                            $innerQuery->orwhere('customers.contact_email',"like", "%".$request->search."%");
                            $innerQuery->orwhere('customers.contact_position',"like", "%".$request->search."%");

                        }
                    );
                }    
            }
        )
            ->first();
        //dd($model);
         //return json_decode($model);

        //$model2 = Customer::find(1);
        //dd($model2);
        //return $model2->toJson();

         $array = array();
         $array[0] = $model;
         //dd($array);
         return $array; 
     
        }


public function orderProduct($id){
 $model = Product::find($id);
 $array = array();
 $array[0] = $model;

 return $array; 
}


public function SaveOrder(Request $request){
        //dd($request);
        //Crear orden
        $order = new Order;
        $order->status_id = 1;//cotizacion
        $order->user_id = $request->user_id;
        $order->customer_id = $request->customer_id;

        if (str_contains($request->total, 'COP')) { //determina si una cadena contiene una subcadena determinada
            $total = substr($request->total, 4);
            $total = intval($total);
            $iva = substr($request->iva, 4);
            $iva = intval($iva);
            $subtotal = substr($request->subtotal, 4);
            $subtotal = intval($subtotal);

            $order->subtotal = $subtotal;
            $order->iva = $iva;
            $order->total = $total;
        }else{
             if (str_contains($request->total, 'US')) {
                $total = substr($request->total, 3);
                $total = intval($total);
                $iva = substr($request->iva, 3);
                $iva = intval($iva);
                $subtotal = substr($request->subtotal, 3);
                $subtotal = intval($subtotal);
                
                $order->subtotal = $subtotal;
                $order->iva = $iva;
                $order->total = $total;
             }
        }
        
        $order->save();

        $cantidad = intval($request->global);

    $i = 1;
    while ($i <= $cantidad) {
        $product_id ="product_id_".$i;
        $count ="count_".$i;
        $model = new OrderProduct; 
        $model->order_id = $request->order_id;  
        $model->product_id = $request->$product_id; 
        $model->count = $request->$count; 
        $model->save();
        $i++;
    }
    return redirect('/orders/show/sid/'.$order->id);
}


public function storeProduct(Request $request){
    $product = Product::where('name', $request->product)->first();

    if($product){
        $model = new OrderProduct;
        $model->product_id = $product->id;
        

        $model->order_id = $request->order_id;
        $model->price = $request->price;
        $model->quantity = $request->quantity;
        $model->discount =$request->dscto;
        $model->description =$request->description;
        
        $model->total = ((100 - $request->dscto)/100) * $request->price * $request->quantity;
        

        $model->save();
    }

    return redirect('/orders/' . $request->order_id . '/add/product');
   

}


    public function addProducts($oid){

    
        $model = Order::find($oid);
        //$payments = Payment::all();

        $products = Product::where("status_id", 1)->get();
        $statuses = OrderStatus::all();
        $users = User::where('role_id', 1)->get();
        $referal= User::where('role_id', 3)->get();

    return view('orders.add_product', compact(  'model', 'users', 'referal', 'products', 'statuses'));
    }

    public function destroy($id)
    {
        $model = OrderProduct::find($id);
        $model->delete();

       return redirect()->back()->with('statustwo', 'El Producto fué eliminado con éxito de la orden!');

    }

    public function showProforma($oid)
    {
        $model = Order::find($oid);

        return view('orders.quote', compact('model'));

    }
    
   public function showProformaCO($oid)
    {
    $model = Order::find($oid);

    return view('orders.quote_co', compact('model'));

   
    }

}