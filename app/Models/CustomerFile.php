<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFile extends Model
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }
}
