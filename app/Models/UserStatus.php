<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;

class UserStatus extends Model
{
    public function user(){
        return $this->hasMany(User::class);
    }
}
