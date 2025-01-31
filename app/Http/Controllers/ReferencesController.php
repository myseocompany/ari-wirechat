<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use DB;
use App\Reference;
use App\Customer;
use App\Product;
use App\Socrata;

class ReferencesController extends Controller{

    /*
    public function __construct(){
        $this->middleware('auth');
    }
    */

    public function show(){
        
        $references = Reference::paginate(10);
        //dd($references);
        return view('references.index', compact('references'));
    }


    public function create($id){
        $customer = Customer::find($id);
        $references = Reference::all();
        $products = Product::where('active', 1)
                            ->where('colombia_price', '>', 0)->get();

        $socrata = new Socrata;
        return view('references.create', compact('customer','references', 'products', 'socrata'));
    }

    public function store(Request $request){
        $reference = new Reference;
        $reference->document_number = $request["document_number"];
        $reference->name = $request["name_customer"];
        $reference->phone = $request["phone"];
        $reference->email = $request["email"];

        $reference->trm = $request["trm"];
        $reference->billing_city = $request["billing_city"];
        $reference->billing_address = $request["billing_address"];

        $reference->note = $request["note"];
        $reference->product_name = $request["note"];
        $reference->value = ($request["order_product_value"]*100);
        
        $reference->billing_country = $request["billing_country"];
        
        $reference->shipping_city = $request["shipping_city"];
        $reference->shipping_address = $request["shipping_address"];
        $reference->shipping_country = $request["shipping_country"];
    
        
        $reference->customer_id = $request->customer_id;
        $reference->product_id = $request->product;
        $reference->save();;
        return redirect('/customers/'.$reference->customer_id.'/show');
    }

    public function destroy($id) {
        $model = Reference::find($id);
        if ($model->delete()) {
            return redirect('/customers/'.$model->customer_id.'/show')->with('statustwo', 'La referencia <strong>'.$model->name.'</strong> fué eliminado con éxito!'); 
        }
    }

    public function edit($id){
        $model = Reference::find($id);
        $customer = Customer::find($id);
        $references = Reference::all();
        $products = Product::where('active', 1)
                            ->where('colombia_price', '>', 0)->get();

        $socrata = new Socrata;
        return view('references.edit', compact('model','customer','references', 'products', 'socrata'));
    }

    public function update($id, Request $request){
        $reference = Reference::find($id);
        $reference->document_number = $request["document_number"];
        $reference->name = $request["name_customer"];
        $reference->phone = $request["phone"];
        $reference->email = $request["email"];

        $reference->billing_city = $request["billing_city"];
        $reference->billing_address = $request["billing_address"];

        $reference->note = $request["note"];
        $reference->product_name = $request["note"];
        $reference->value = ($request["order_product_value"]*100);
        
        $reference->billing_country = $request["billing_country"];
        
        $reference->shipping_city = $request["shipping_city"];
        $reference->shipping_address = $request["shipping_address"];
        $reference->shipping_country = $request["shipping_country"];
    
        
        $reference->customer_id = $request->customer_id;
        $reference->product_id = $request->product;
        $reference->save();
        return redirect('/customers/'.$reference->customer_id.'/show');
    }


    public function getLink($id){
        $model = Reference::find($id);
        return view('references.link', compact('model'));
    }
}