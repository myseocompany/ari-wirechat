<?php

namespace App\Models;
use DB;
use App\Models\Action;
use Illuminate\Database\Eloquent\Model;

class ViewCustomerHasActions extends Model{

	public function getCreatedAtActionMax($id){
		$model = Action::select(DB::raw('MAX(created_at) as created_at'))
		->where('creator_user_id',$id)
		->first();
		return $model->created_at;
	}
}