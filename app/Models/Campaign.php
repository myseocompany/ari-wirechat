<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model{

    protected $fillable = [
        'name',
        'audience_id',
        'description',
        'filters',
        'max_recipients',
        'whatsapp_account_id',
        'whatsapp_template_id',
        'template_name',
        'template_language',
        'header_type',
        'header_media_url',
        'wait_seconds',
        'action_note',
        'settings',
    ];

    protected $casts = [
        'filters' => 'array',
        'settings' => 'array',
    ];

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
