<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Order;
use App\OrderStatus;
use App\User;
use App\Category;
use App\Customer;
use App\OrderTransaction;
use App\Product;
use App\ProductType;

class OrderTransactionController extends Controller{
	function destroy($id){
		$model = OrderTransaction::find($id);
		if($model){
			$oid = $model->order_id;

			$model->delete();
 		
        	return redirect('/orders/'.$oid.'/show');
        }
	}

	public function store(Request $request){
        
        $model = new OrderTransaction;
        $model->order_id = $request->order_id;
        $model->date = $request->date;
        $model->internal_id = $request->internal_id;
        $model->description = $request->description;
        
        if($request->is_debit==1)
            $model->debit = $request->value;
        else
            $model->credit = $request->value;

        
        $model->save();

        return redirect('/orders/'. $request->order_id.'/show')->withInput($request->all());
    }


     public function edit($id){


        $model = OrderTransaction::find($id);

        return view('orders.transactions.edit', compact('model'));
    }

    public function update($id, Request $request){

        $model = OrderTransaction::find($id);
		$model->date = $request->date;
        $model->internal_id = $request->internal_id;
        $model->description = $request->description;
        
        if($request->is_debit==1)
            $model->debit = $request->value;
        else
            $model->credit = $request->value;
        
        $model->save();

        return redirect('/orders/'. $model->order_id.'/show')->withInput($request->all());
    }
}