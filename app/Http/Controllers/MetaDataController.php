<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Mail;

use App\Models\Customer;
use App\Models\Campaign;
use App\Models\CustomerStatus;
use App\Models\User;
use App\Models\CustomerSource;
use App\Models\CustomerHistory;
// use App\Models\Account;
// use App\Models\EmployeeStatus;
// use App\Models\Mail;
use App\Models\EmailBrochure;
use App\Models\Action;
Use App\Models\Audience;
use App\Models\AudienceCustomer;
use App\Models\Email;
use App\Models\ActionType;
use Auth;
use Carbon;
use DateTime;
use App\Models\Product;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use App\Models\CustomerMetaDataType;

class MetaDataController extends Controller
{

    protected $attributes = ['status_name'];

    protected $appends = ['status_name'];

    protected $status_name;


    public function index(Request $request) {
        return $this->customers($request);
    }

   
   public function datapolicy(){
     return view('customers.politica_datos');
   }

    public function poll() {
        $customers = DB::table('customers')
            ->join('audience_customer', 'audience_customer.customer_id', 'customers.id')
            ->join('audiences', 'audiences.id', 'audience_customer.audience_id')
            ->select('customers.*')
            ->where('audiences.id', 6)
            ->where('customers.created_at', '>', '2020-01-01')
            ->get();
        return view('customers.metadata.1.pollAll', compact('customers'));

    }

    public function pollId($id){
        
        $customers = Customer::find($id);
        return view('customers.metadata.1.poll', compact('customers'));
    }

    public function save($id, Request $request){
    	//dd($request->suggestions);
        /*
    	$customer = Customer::find($id);
    	$customer->name = $request->name;
    	$customer->email = $request->email;
    	$customer->business = $request->business;
    	$customer->position = $request->position;
    	$customer->save();
        */

        $meta_data = new CustomerMetaData();
        $meta_data->customer_id = $id;
        $meta_data->number_employees =$request->number_employees;
        $meta_data->data_authorization =$request->data_authorization;
        $meta_data->position =$request->position;
        $meta_data->business =$request->business;
        $meta_data->email =$request->email;
        $meta_data->save();


    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = $request->empanadas;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 78;
    	$meta_data->value = $request->quality_78;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 79;
    	$meta_data->value = $request->confort_79;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 80;
    	$meta_data->value = $request->security_80;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 81;
    	$meta_data->value = $request->delivery_time_81;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 82;
    	$meta_data->value = $request->atention_82;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 83;
    	$meta_data->value = $request->responsive_time_personal_83;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 84;
    	$meta_data->value = $request->atention_technical_support_84;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 85;
    	$meta_data->value = $request->quality_technical_support_85;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 86;
    	$meta_data->value = $request->satisfaction_level_86;
    	$meta_data->save();

		$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 87;
    	$meta_data->value = $request->recommendation_87;
    	$meta_data->save();

    	$meta_data = new CustomerMetaData();
    	$meta_data->customer_id = $id;
    	$meta_data->customer_meta_data_type_id = 88;
    	$meta_data->value = $request->suggestions_88;
    	$meta_data->save(); 

        return redirect('https://maquiempanadas.com/es/gracias-web');

    }

    public function freeClass($cid){
        $customer = Customer::find($cid);
        return view('customers.metadata.freeclass', compact('customer'));
    }

    public function freeClassUpdate($cid, Request $request){
        $cHistory = new CustomerHistory;
        $cHistory->customer_id = $cid;
        $cHistory->name = $request->name;
        $cHistory->phone = $request->phone;
        $cHistory->email = $request->email;
        $cHistory->country = $request->country;
        $cHistory->business = $request->business;
        $cHistory->position = $request->position;
        
        
        $date = new DateTime();
        $cHistory->created_at = $date->getTimestamp();
        $cHistory->updated_at = $date->getTimestamp();
        $cHistory->count_empanadas = $request->count_empanadas;
        if ($cHistory->save()) {
            $action = new Action;
            $action->type_id = 24;
            $action->customer_id = $cid;
            $action->save();
            return redirect('https://maquiempanadas.com/es/gracias-web');
        }else{
            dd("error");
        }
    }

    public function schedule($id){
        $customer = Customer::find($id);
        $material = CustomerMetaDataType::where('parent_id',94)->get();
        $outlets = CustomerMetaDataType::where('parent_id',98)->get();
        $rol = CustomerMetaDataType::where('parent_id',106)->get();
        return view('customers.metadata.schedule', compact('customer','material','outlets','rol'));
    }

    public function scheduleStore(Request $request){
        //dd($request);
        //$meta_data = new CustomerMetaData();
        return redirect('https://maquiempanadas.com/es/gracias-web');
    }

    public function store(Request $request){


 
        

        foreach ($request->all() as $key => $value) {
            if (str_contains($key, 'meta_') ) { //determina si una cadena contiene una subcadena determinada
                $meta_data_id = (int)substr($key,5);//quitamos meta_  
                if(is_array($value)){
                    foreach ($value as $item  ) {
                        $this->saveCustomerMeta($request, $meta_data_id,$item,$request->customer_id);
                    }
                }else
                if( !is_null($value)){
                     $this->saveCustomerMeta($request, $meta_data_id,$value,$request->customer_id);
                }
             }
        }
   
        return redirect('customers/'.$request->customer_id.'/show')->with("La encuesta se guardó correctamente!");
   
    }

public function saveCustomerMeta(request $request, $meta_data_id, $value, $customer_id){

    $model = new CustomerMeta;
    $model->meta_data_id = $meta_data_id;
    $model->customer_id = $customer_id;
    $model->audience_id = $request->audience_id;
    

    if($meta_data_id == 55){
        
        if($request->hasFile('meta_55')){     
            $originName = $request->file('meta_55')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $destinationPath = 'public/files/'.$request->project_id."/";
            $extension = $request->file('meta_55')->getClientOriginalExtension();
            $fileName = $fileName.'_'.time().'.'.$extension;
            $path = $request->file('meta_55')->move($destinationPath,$fileName);
            
            $url = asset($destinationPath.$fileName);  
            $model->value = $url;
        }
    }else{

        $model->value = $value;
    }

    $model->save();
    
            

}

public function createSurvey($id){

    $model = Customer::find($id);
    $metaData = CustomerMetaData::all();

    
    return view('customers.survey', compact('model', 'metaData'));
}

public function createPoe($id, $cid){

    $model = Customer::find($id);
    $campaign = Campaign::find($cid);
   
    return view('POE.create', compact('model','campaign'));
}


public function storepoe(request $request, $id){
    
    $model = customer::find($id);
        //dd($id);
     $customer_id = $id;  
   foreach ($request->all() as $key => $value) {
       if (str_contains($key, 'meta_') ) { //determina si una cadena contiene una subcadena determinada
           $meta_data_id = (int)substr($key,5);//quitamos meta_  
           if(is_array($value)){
               foreach ($value as $item  ) {
                   $this->savecustomerMeta($request, $meta_data_id,$item, $customer_id);
               }
           }else
           if( !is_null($value)){
                $this->savecustomerMeta($request, $meta_data_id,$value, $customer_id);
           }
        }
   }

   foreach ($request->all() as $key => $value) {
       if (str_contains($key, 'radio_')) { //determina si una cadena contiene una subcadena determinada
           $meta_data_id = (int)substr($key,6);//quitamos radio_  
           if(is_array($value)){
               foreach ($value as $item  ) {
                   $this->savecustomerMeta($request, $meta_data_id,$item, $customer_id);
               }
           }else
           if( !is_null($value)){
                $this->savecustomerMeta($request, $meta_data_id,$value, $customer_id);
           }
        }
   }

   foreach ($request->all() as $key => $value) {
       if (str_contains($key, 'check_')) { //determina si una cadena contiene una subcadena determinada
           $meta_data_id = (int)substr($key,6);//quitamos check_  
           if(is_array($value)){
               foreach ($value as $item  ) {
                   $this->savecustomerMeta($request, $meta_data_id,$item,$model->id);
               }
           }else
           if( !is_null($value)){
                $this->savecustomerMeta($request, $meta_data_id,$value,$model->id);
           }
        }
   }

//dd($request->all());
    foreach ($request->all() as $key => $value) {
       if (str_contains($key, 'login_')) { //determina si una cadena contiene una subcadena determinada
            
           $meta_data_id = (int)substr($key,6);//quitamos login_  
           if(is_array($value)){
               $size = (sizeof($value))/4;
               $position = 0 ;
               for($i=0; $i<$size; $i++){
                   foreach ($value as $key => $item  ) {
                       if( !is_null($item)){
                          $modelProject = new ProjectLogin;
                           //$model->meta_data_id = $meta_data_id;
                           $modelProject->project_id = $model->id;
                           $modelProject->name       = $value[$position];
                           $modelProject->url        = $value[$position+1];
                           $modelProject->user       = $value[$position+2];
                           $modelProject->password   = $value[$position+3];
                           if($key == $position){
                               $modelProject->save();
                           }
                       }
                   }
                   $position = $position+4;
               }       
           }
       }
   }
     
   $array = array("Logos de la empresa","Manual de marca","Activos de mercadeo (Imágenes, videos, textos)");
      


   $files = $request->file();

   foreach ($files as $key => $value) {
       if($request->hasFile($key)){       
           $modelDocument = new ProjectDocument;
           $originName = $request->file($key)->getClientOriginalName();
           $fileName = pathinfo($originName, PATHINFO_FILENAME);
           $destinationPath = 'public/files/'.$request->project_id."/";
           $extension = $request->file($key)->getClientOriginalExtension();
           $fileName = $fileName.'_'.time().'.'.$extension;
           $path = $request->file($key)->move($destinationPath,$fileName);
           $url = asset($destinationPath.$fileName);  
           $modelDocument->url = $url;
           $modelDocument->project_id = $model->id;
           $modelDocument->type_id = 5; 
           //$modelDocument->description = "aaaa" ; 
           $modelDocument-> save();
       }
   }

     //$this->saveCustomer2($model->id,$request->nit,$request->position,$request->namecontact);
      
   return redirect('http://maquiempanadas.com/');

}



public function surveyReport($id){

    $audience = Audience::find($id);     
    
    $customer = AudienceCustomer::leftJoin('customer_metas', 'customer_metas.customer_id', 'audience_customer.customer_id')
    ->select(DB::raw('distinct(customer_metas.customer_id) as customer_id'))
    ->where('audience_customer.audience_id', '=', $id)
    ->get();

    $meta_data = CustomerMetaData::all();

    $metas = CustomerMetaData:: leftJoin('customer_metas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
        ->leftJoin('audience_customer', 'customer_metas.customer_id', 'audience_customer.customer_id')
        ->select('customer_meta_datas.value as name', 'customer_meta_datas.parent_id',
        DB::raw('COUNT(customer_metas.meta_data_id) as countask'),
        DB::raw( '(SUM(customer_metas.VALUE)/COUNT(customer_metas.meta_data_id)) as average'))
        ->groupBy('customer_metas.meta_data_id', 'customer_meta_datas.value', 'customer_meta_datas.parent_id')
        ->where('audience_customer.audience_id', '=', $id)
        ->get();
    
    return view('surveys.survey_report', compact('audience', 'customer', 'meta_data', 'metas'));


}
}
