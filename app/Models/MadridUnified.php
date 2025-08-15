<?php 

// app/Models/MadridUnified.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MadridUnified extends Model {
    protected $table = 'v_madrid2025_unified';
    public $timestamps = false;
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
}
