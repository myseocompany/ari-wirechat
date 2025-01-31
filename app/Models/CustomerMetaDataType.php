<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMetaDataType extends Model{
	public static function getOptions($id){
		return CustomerMetaDataType::where('parent_id', $id)->get();
	}
}