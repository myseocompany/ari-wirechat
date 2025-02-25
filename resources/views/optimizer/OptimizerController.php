<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Customer;
use App\Action;
use App\CustomerStatus;
use App\CustomerSource;
use App\Product;
use App\User;
use App\CustomerFile;
use App\Order;


class OptimizerController extends Controller{

    
 public function mergeDuplicates_(Request $request){
        //dd($request);
        
        /*$this->updatedActionFromRequest($request);
        $this->updatedOrdersFromRequest($request);
         */
        $this->saveFromRequest($request);  
        /*
        $this->deleteFromRequest($request);  

        return redirect('https://elemental.crmquirky.com.co/optimize/customers/consolidateDuplicates?document='.$request->document);   
    */
    }


  function updatedActionFromRequest($request){

        if($request->action_all!=null){
             foreach ($request->action_all as $key => $value) {
                       $action = Action::find($value);
                       $action->customer_id = $request->customer_id;
                       $action->save();
            }

        }
    }


    function updatedOrdersFromRequest($request){

        if($request->order_all!=null){
             foreach ($request->order_all as $key => $value) {
                       $action = Order::find($value);
                       $action->customer_id = $request->customer_id;
                       $action->save();
            }

        }
    }
    function SaveFilesFromRequest($request){
        $modelFile = CustomerFile::where('customer_id',$request->customer_id)->get();
        if($request->file_all!=null){
             foreach ($request->file_all as $key => $value) {
                       $file = CustomerFile::find($value);
                       $file->customer_id = $request->customer_id;
                       $file->save();
            }

        }
    }

function saveFromRequest($request){

        dd($request);
        $model = Customer::find($request->customer_id);
        $model->recency_points = $request->recency_points;
        $model->frequency_points = $request->frequency_points;
        $model->session_id = $request->session_id;
        $model->valid_wp = $request->valid_wp;
        $model->rfm_group_id = $request->rfm_group_id;
        
         $model->status_id = $request->status_id_1;
         $model->name = $request->name;
         $model->document = $request->document;
         $model->identification = $request->identification;
         
         $model->position = $request->position;
         $model->business = $request->business;
         $model->phone = $request->phone;
         $model->phone2 = $request->phone2;
         $model->phone_wp = $request->phone_wp;
         
         $model->email = $request->email;
         $model->address = $request->address;
         $model->birthday = $request->birthday;
         
         $model->city = $request->city;
         $model->country = $request->country;
         $model->department = $request->department;
         $model->notes = $request->notes;
         //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;
        $model->user_id = $request->user_id;
        $model->source_id = $request->source_id;
        if(isset( $model->date_bought)){
             $model->date_bought = $request->date_bought;
        }
       
        $model->notes = $request->notes;
        $model->src = $request->src;
        $model->cid = $request->cid;
        $model->vas = $request->vas;
        $model->created_at = $request->created_at;
        //dd($request);

        $model->save();

        return $model;
    }

 function deleteFromRequest($request){
    
        $modelE = Customer::where('document', $request->document)->get();
         foreach($modelE as $item){
             if($item->id==$request->customer_id){

             }else{
                Customer::destroy($item->id);
                
             }
            
         }
    
         return;
    }

    /*
	public function findDuplicates(Request $request){

        
        $model = Customer::select('email', DB::raw('count(email) as count'))
            ->whereNotNull('email')->groupBy('email')
            ->havingRaw('count(email) > 1')
            ->orderby('email')
            ->get();
    
        return view('optimizer.index', compact('model'));


    }*/

    public function getModelText($key, $model){
        $str = "";
        switch ($key) {
            case 'status_id':
                if(isset($model->status))
                    $str = $model->status->name . " - ";
                break;
            case 'source_id':
                if(isset($model->source))
                    $str = $model->source->name . " - ";
                break;
            case 'user_id':
                if(isset($model->user))
                    $str = $model->user->name . " - ";
                break;
            case 'updator_user_id':
                if(isset($model->updated_user))
                    $str = $model->updated_user->name . " - ";
                break;
            
        }
        return $str;
    }



    public function consolidateDuplicates(Request $request){
        if(isset($request->document)){  
            
            $model = Customer::where('document', $request->document)->get();
            
            $statuses_options = CustomerStatus::all();
            $customers_source=CustomerSource::all();
            $user = User::all();
            
            $controller = $this;
            if( !empty($model) ){
                //$model->status_id = CustomerStatus::($model->status_id)
                return view('optimizer.show', compact('model','statuses_options', 'user' ,'controller','customers_source'));
            }
        }
    }
    /*
    public function showDuplicates($email, Request $request){
        
        dd($email);
        
        $status = CustomerStatus::all();
        if( !empty($data) ){
            //$data->status_id = CustomerStatus::($data->status_id)
            return view('optimizer.show', compact('data', 'status'));
        }
        

    }
    */

    public function duplicatePhone(Request $request){
        $data = Customer::where('phone', 'like', '%'.$request->phone.'%' )
            ->whereNotNull('phone')
            ->get();
        $status = CustomerStatus::all();    
        if( !empty($data) ){
            //$data->status_id = statusName($data->status_id);
            return view('customers.duplicate.show', compact('data', 'status'));
        }
    }
}

 ?>