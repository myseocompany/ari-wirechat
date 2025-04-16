<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model{

	public function CustomerMetaData(){
        return $this->belongsToMany('App\Models\CustomerMetaData','campaign_customer_meta_data','campaign_id','customer_meta_data_id'); //
    }

    public function customer_meta_data(){
        return $this->belongsToMany('App\Models\CustomerMetaData','campaign_customer_meta_data','campaign_id','customer_meta_data_id'); //
    }

    
    function messages(){
        return $this->hasMany('App\Models\CampaignMessage', 'campaign_id', 'id');
    } 


	
}