<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class Product extends Model
{
    // use Searchable;
    //
    function customer(){
    	return $this->belongsTo('App\Models\Customer');
    }

    
}
