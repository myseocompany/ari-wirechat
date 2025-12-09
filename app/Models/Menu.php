<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model{

    public static function getUserMenu($user){

        $model = Menu::select('menus.id','menus.name', 'menus.url')
            ->leftJoin ("role_menus", "menus.id", "role_menus.menu_id")
            ->leftJoin("roles", "roles.id", "role_menus.role_id")
            ->where("roles.id", $user->role_id)
            ->whereNotIn('menus.name', ['Posventa', 'Logistica', 'Logística'])
            ->whereNotIn('menus.url', ['/customers/phase/3', '/customers/phase/4'])
            ->orderBy('weight', 'ASC')
            ->get();

        return $model;
    }

    public function hasChildren(){
        $model = Menu::where('parent_id', $this->id)->get();
        
        if ($model->count())
            return true;
        else    
            return false;
        
    }

    public function getChildren(){
        $model = Menu::where('parent_id', $this->id)->get();
        
        return $model;

    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_menus')
                    ->withPivot(['create', 'read', 'update', 'delete'])
                    ->withTimestamps();
    }
	
}
