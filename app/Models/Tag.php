<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
    ];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_tag')->withTimestamps();
    }
}
