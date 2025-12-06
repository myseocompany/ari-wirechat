<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = [
        'slug',
        'customer_id',
        'quiz_meta_id',
        'name',
        'stage',
        'final_score',
        'completed_at',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
