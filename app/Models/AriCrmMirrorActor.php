<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AriCrmMirrorActor extends Model
{
    protected $fillable = ['integration_id', 'actor_type', 'aricrm_actor_id', 'name'];
}
