<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Category;

class OrderProduct extends Model{

	protected $table = 'order_products';
    
    function actions(){
        return $this->hasMany('App\Models\Action');
    }

    function products(){
    	return $this->hasMany('App\Models\Product');	
    }

    function product(){
    	return $this->belongsTo('App\Models\Product');	
    }

    function order(){
    	return $this->belongsTo('App\Models\Order');
    }
}