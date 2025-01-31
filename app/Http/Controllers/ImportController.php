<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//~ use App\Referral;
//~ use App\Leader;
//~ use App\ReferralStatus;
use Validator;
use Storage;
use App;
use Route;
use Carbon\Carbon;
use DateTime;

use App\User;
use App\Role;


class ImportController extends Controller{


    public $name_index = 0;
    public $email_index = 1;
    public $role_index = 2;
    public $id_number_index = 3;
    public $aux_email_index = 4;
    public $final_email_index = 5;
    public $status_id_index = 6;
    
    
    //~ public $lista_no_index = 6;
    //~ public $referral_status_id_index = 7;
    //~ public $activity_index = 8;
    //~ public $municipality_index = 9;
    //~ public $birth_day_index = 10;
    //~ public $observations_index = 11;
    //~ public $email_index = 12;
    //~ public $commune_index = 13;

    public function index(){
        return view('import.index');
    }
	public function saveModel($data, $model){
		
	
        
        $i = 0;
        
		$model->name = $data[$i++];
		$model->email = $data[$i++];        
		$model->role_id = $this->findRole($data[$i++]);

        $model->id_number = $data[$i++];
        $model->aux_email = $data[$i++];
        $model->final_email = $data[$i++];
        
		$model->status_id = 1;
		$model->password = bcrypt($model->id_number);
        $model->save();
        
	}

    public function findRole($roleName){
		$name = strtolower($roleName);
		$role = Role::where('name',$name)->first();
		if(is_null($role))
			return 0;
		else
			return $role->id;
	}

    function encode_utf8($data)
    {
        if ($data === null || $data === '') {
            return $data;
        }
        if (!mb_check_encoding($data, 'UTF-8')) {
            return mb_convert_encoding($data, 'UTF-8');
        } else {
            return $data;
        }
    }

	public function import(Request $request){
        Storage::disk('local')->put('file.txt', 'Contents');
        $duplicados = array();
        
        if($request->hasFile('importerFile')){
            $path = $request->file('importerFile')->store('import');
            	
            $duplicado = false;
            if (($handle = fopen ( "laravel/storage/app/".$path, 'r' )) !== FALSE) { 
                $cont = 0;
                while ( ($data = fgetcsv ( $handle, 1000, ',' )) !== FALSE ) {
                	if($cont>0) {
                        $name = "";
                        if(isset($data[$this->name_index]) && ($data[$this->name_index]!=""))
                            $name = $data[$this->name_index];
                        if(isset($data[$this->email_index]) && ($data[$this->email_index]!=""))
                            $email = $data[$this->email_index];
                        if(isset($data[$this->role_index]) && ($data[$this->role_index]!=""))
                            $role_id = $data[$this->role_index];
                        if(isset($data[$this->id_number_index]) && ($data[$this->id_number_index]!=""))
                            $id_number = $data[$this->id_number_index];
                        if(isset($data[$this->aux_email_index]) && ($data[$this->aux_email_index]!=""))
                            $aux_email = $data[$this->aux_email_index];
                        if(isset($data[$this->final_email_index]) && ($data[$this->final_email_index]!=""))
                            $final_email = $data[$this->final_email_index];

                        $model_ver = User::where('id_number', $id_number)->get();
						
						if( $model_ver->isEmpty() ){
							$model = new User;
							
						}else{
							$duplicados[] = $id_number;
							$duplicado = true; 
                            $model = $model_ver->first();
						}
                        $this->saveModel($data, $model);
	                    
                    }
                    $cont++;
                    $duplicado = false;
                }
                fclose ( $handle );
        }else{
            exit();
            return redirect('/users')->with('error', 'No se pudo leer el archivo');

        }
        $cont--;
        	$numDuplicados = count($duplicados);

        	if($numDuplicados){
        		
                $duplicados = implode(", ", $duplicados);
                return redirect('/users')->with('status', 'Insertados/Actualizados <strong>'.($cont-$numDuplicados).'</strong> fueron insertados con exito. Cedulas duplicadas: '.$duplicados);
            }
        }else{
            echo "sin archivo";
        }
        return back();
    }

    function strToDate($date){
        $tempDate = explode('/', $date);
        $dateStr = $tempDate[2]."-".$tempDate[1]."-".$tempDate[0];

        return $dateStr;
    }

    function validateDate($date){

        $valid = false;
        $tempDate = explode('/', $date);
        if(count($tempDate)==3){
        
            $format = 'Y-m-d';
            $dateStr = $this->strToDate($date);
            $date = DateTime::createFromFormat($format, $dateStr);
            $valid =  $date && ($date->format($format) === $dateStr);
        }
    
        return $valid;
    }
}
