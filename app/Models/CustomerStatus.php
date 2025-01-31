<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class CustomerStatus extends Model
{
    // use Searchable;
    //
    function customer(){
    	return $this->belongsTo('App\Models\Customer');
    }

    // function employee_status(){
    // 	return $this->belongsTo('App\Models\EmployeeStatus');
    // }


    // function employee_files(){
    // 	return $this->hasMany('App\Models\EmployeeFile');
    // }

    // public function searchableAs(){
    //     return 'employee_id';
    // }

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
      public static function getColor($id){
        $str = "";
        if(!is_null($id))
            $str = CustomerStatus::find($id)->color;  
        return $str;
    }
    public static function getName($id){
        $str = "";
        if(!is_null($id))
            $str = CustomerStatus::find($id)->name;  
        return $str;
    }
}
