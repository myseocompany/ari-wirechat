<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $table = 'request_logs';
    protected $fillable = ['request', 'action', 'user_id', 'phone', 'facebook_id', 'email'];
}
