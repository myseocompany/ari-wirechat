<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMetaData extends Model{
	protected $table = "customer_meta_datas";

	// Formulario POE: residencia UE/Reino Unido y número EORI (ver migración 2026_10_29_120000)
	public const POE_RESIDENCE_GROUP_ID = 4000;
	public const POE_EU_RESIDENT_QUESTION_ID = 4001;
	public const POE_EORI_QUESTION_ID = 4002;
	public const POE_EU_RESIDENT_YES = 'Sí';
	public const POE_EORI_NO = 'No, aún no cuento con número EORI.';

	public static function getOptions($id){
		return CustomerMetaData::where('parent_id', $id)->get();
	}


	
    function CustomerMetaDataChildren(){
     	
     	return $this->hasMany('App\Models\CustomerMetaData','parent_id')->orderBy('weight');;
     	//return CustomerMetaData::where('parent_id',$this->id)->get();

     }
	 function isMultiple(){
		$res=true;
		if($this->type_id==1 || $this->type_id==4){
			$res=false;
		}
		return $res;
	}

	function getInputType(){
		

		$str="";
		switch ($this->type_id) {
			case 1:
				$str="text";
				break;
			case 2:
				$str="radio";
				break;
			case 3:
				$str="checkbox";
				break;
			case 4:
				$str="textarea";
					 
				break;
			case 5:
				   $str="file";
				   break;
			default:
				# code...
				break;
		}
		return $str;
	}
	public function getAnswer($pid, $project_meta_data){
	   $model = CustomerMeta::where("project_id", $pid)
		   ->where("meta_data_id", $project_meta_data->id)->first();
	   
	   $str = "";
	   $parent = CustomerMeta::where("id", $project_meta_data->parent_id)->first();

	   if($model)
		   $str = $model->value;
	   /*
	   if($parent->type_id == 3 )
		   $str = "x";
	   */	
	   
	   return  $str;
				 
	}
}