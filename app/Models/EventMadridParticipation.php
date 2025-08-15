<?php 
// app/Models/EventMadridParticipation.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EventMadridParticipation extends Model {
    protected $table = 'v_event_madrid2025_participation';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'customer_id';
    protected $guarded = [];
}
