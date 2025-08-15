<?php 

// app/Models/MadridLead.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MadridLead extends Model
{
    protected $table = 'v_madrid2025_es_consolidated';
    public $timestamps = false;

    // Si quieres filtros rápidos por estado:
    public function scopeConRsvp($q)    { return $q->whereNotNull('last_rsvp_at'); }
    public function scopeAsistieron($q) { return $q->whereNotNull('last_attended_at'); }
    public function scopeNoShow($q)     { return $q->whereNotNull('last_noshow_at'); }
}
    