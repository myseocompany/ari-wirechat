<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model{


	public function user(){
        return $this->belongsTo('App\Models\Customer', 'id', 'user_id');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer', 'id', 'user_id');
    }

	public function status(){
        return $this->belongsTo('App\Models\EmailQueueStatus', 'status_id', 'id');
    }

}