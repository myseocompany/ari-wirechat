<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model{

    public static function getUserMenu($user){

        $model = Menu::select('menus.id','menus.name', 'menus.url')
            ->leftJoin ("role_menus", "menus.id", "role_menus.menu_id")
            ->leftJoin("roles", "roles.id", "role_menus.role_id")
            ->where("roles.id", $user->role_id)
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
	
}