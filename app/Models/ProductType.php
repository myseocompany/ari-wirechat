<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductType extends Model{

	public static function getChilds($id){
		$model = ProductType::where('parent_id', $id)->get();
		$res = Array();

		foreach ($model as $item) {
			$res[] = $item->id;
		}
		return $res;
	}

	public static function getId($id){
		$type_id = 1;
		if(!is_null($id))
			$type_id = $id;

		return $type_id;
	}

	public static function getName($id){
		$type_id = ProductType::getId($id);
		$model = ProductType::find($type_id);
		$res = "";

		if($model)
			$res = $model->name;

		return $res;
	}
}