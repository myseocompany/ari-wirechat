<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AriCrmMirrorContact extends Model
{
    protected $fillable = ['integration_id', 'aricrm_contact_id', 'customer_id', 'normalized_phone'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
