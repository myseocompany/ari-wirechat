<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Role extends Model
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'can_view_all_customers' => 'boolean',
        ];
    }

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
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menus')
            ->withPivot(['create', 'read', 'update', 'delete'])
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
