<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{

	function customers(){
        return $this->belongsToMany('App\Models\Customer');
    }
}