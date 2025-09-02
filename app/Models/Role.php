<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
class Role extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'role_id'
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];


    // public function projects(){
    //     return $this->hasMany(Projects::class);
    // }
    //  public function role(){
    //     return $this->hasOne('App\Models\Rol','roles','role_id', 'id');
    // }
    public function menus() {
        return $this->belongsToMany(Menu::class, 'role_menus')
                    ->withPivot(['create', 'read', 'update', 'delete'])
                    ->withTimestamps();
    }

    public function users() {
        return $this->hasMany(User::class);
    }

}