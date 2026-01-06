<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeletedCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'deleted_by_user_id',
        'payload',
        'deleted_at',
    ];

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'deleted_at' => 'datetime',
        ];
    }
}
