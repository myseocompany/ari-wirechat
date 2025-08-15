<?php
// app/Models/EventMadridKpis.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EventMadridKpis extends Model {
    protected $table = 'v_event_madrid2025_kpis';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = [];
}
