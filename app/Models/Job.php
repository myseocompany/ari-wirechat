<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class Job extends Model
{
    
    public function get_available_at(){
        return date("Y-m-d H:i:s", $this->available_at);
    }

    public function get_payload(){
        $obj = json_decode($this->payload);
        dd($obj->data->command); 
    }
}
