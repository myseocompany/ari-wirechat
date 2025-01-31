<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;

class CustomerHistory extends Model
{
    
    //
    public function getDateInput($date){
    	return date('Y-m-d',strtotime($date));
    }

    public function projec(){
    	return $this->belongsTo(CustomerHistory::class);
    }
    public function status(){
        return $this->belongsTo('App\Models\CustomerStatus');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    function updated_user(){
        return $this->belongsTo('App\Models\User', 'updated_user_id', 'id');
    }
    
    public function isTimestamp($string)

	{
	    //dd($string->timestamp);
	    try {
	        new DateTime('@' . $string->timestamp);
	    } catch(Exception $e) {
	        return false;
	    }
	    return true;
	}

    public function saveFromModel(Model $model)
    {

        $customer = new CustomerHistory;
        $customer->customer_id = $model->id;
        $customer->name = $model->name;
        $customer->document = $model->document;
        $customer->position = $model->position;
        $customer->business = $model->business;
        $customer->phone = $model->phone;
        $customer->phone2 = $model->phone2;
        $customer->email = $model->email;
        $customer->notes = $model->notes;
        $customer->address = $model->address;
        $customer->city = $model->city;
        $customer->country = $model->country;
        $customer->department = $model->department;
        $customer->bought_products = $model->bought_products;
        $customer->status_id = $model->status_id;
        $customer->user_id = $model->user_id;
        $customer->source_id = $model->source_id;
        $customer->technical_visit = $model->technical_visit;
        $customer->updated_user_id = $model->updated_user_id;
        // dd($model->created_at->timestamp);
        //$customer->updated_at = time();
         if($model->created_at->timestamp!= -62169984000)
         $customer->created_at = $model->created_at;
        if($model->updated_at->timestamp!= -62169984000)
            $customer->updated_at = $model->updated_at;



      
        $customer->save();
        
    }

    public function getStatusName(){
        $str="Estado Vacio";
        if(isset($this->status))
            $str = $this->status->name;
        return $str;        
    }
    public function getStatusColor(){
        $str="#00FF00";
        if(isset($this->status))
            $str = $this->status->color;
        return $str;        
    }
}
