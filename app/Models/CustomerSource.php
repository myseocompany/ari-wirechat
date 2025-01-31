<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class CustomerSource extends Model
{
    // use Searchable;
    //
    // function account(){
    // 	return $this->belongsTo('App\Models\Account');
    // }

    public function customer(){
    	return $this->belongsTo('App\Models\Customer');
    }

     function user(){
        return $this->belongsTo('App\Models\User');
    }


    // function employee_files(){
    // 	return $this->hasMany('App\Models\EmployeeFile');
    // }

    public function searchableAs(){
        return 'employee_id';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }
}
