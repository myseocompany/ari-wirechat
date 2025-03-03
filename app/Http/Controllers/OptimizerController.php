<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Models\Customer;
use App\Models\Action;
use App\Models\CustomerStatus;
use App\Models\CustomerSource;
use App\Models\Product;
use App\Models\User;
use App\Models\CustomerFile;
use App\Models\CustomerHistory;


class OptimizerController extends Controller{

    
 public function mergeDuplicates_(Request $request){
        //dd($request);
        
        $this->updatedActionFromRequest($request); 
        $this->saveFromRequest($request);  
        $this->SaveFilesFromRequest($request);  
        
        $this->deleteFromRequest($request); 

        $this->saveHistoryFromRequest($request);

        return redirect('/optimize/customers/consolidateDuplicates?email=' . $request->email);   

    }


  function updatedActionFromRequest($request){

        $modelAction = Action::where('customer_id',$request->customer_id)->get();
        if($request->action_all!=null){
             foreach ($request->action_all as $key => $value) {
                       $action = Action::find($value);
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
        $model = Customer::find($request->customer_id);
        $model->status_id = $request->status_id_1;
        $model->name = $request->name;
        $model->document = $request->document;
        $model->position = $request->position;
        $model->business = $request->business;
        $model->phone = $request->phone;
        $model->phone2 = $request->phone2;
        $model->phone_wp = $request->phone_wp;
        $model->total_sold = $request->total_sold;
        
        $model->email = $request->email;
        $model->address = $request->address;
        $model->city = $request->city;
        $model->country = $request->country;
        $model->department = $request->department;
        $model->notes = $request->notes;
        $model->count_empanadas = $request->count_empanadas;
         //datos de contacto
        $model->contact_name = $request->contact_name;
        $model->contact_phone2 = $request->contact_phone2;
        $model->contact_email = $request->contact_email;
        $model->contact_position = $request->contact_position;
        $model->user_id = $request->user_id;
        dd($request->all());

        $model->source_id = $request->source_id;
        $model->count_empanadas = $request->count_empanadas;
        if(isset( $model->date_bought)){
             $model->date_bought = $request->date_bought;
        }
       
        $model->notes = $request->notes;
        $model->technical_visit = $request->technical_visit;
        $model->gender = $request->gender;
        $model->scoring_interest = $request->scoring_interest;
        $model->scoring_profile = $request->scoring_profile;
        $model->rd_public_url = $request->rd_public_url;
        $model->src = $request->src;
        $model->cid = $request->cid;
        $model->vas = $request->vas;
        $model->rd_source = $request->rd_source;
        $model->product_id = $request->product_id;
        $model->updated_user_id = $request->updated_user_id;
        $model->created_at = $request->created_at;
        $model->country2 = $request->country2;
        //dd($request);

        $model->save();

        return $model;
    }

// Funcion de maqui
function deleteFromRequest($request)
{
    $customer_id_all = $request->customer_id_all;
    
   
    //$model = Customer::where('email', 'like', '%' . $request->email . '%')->get();
    $model = Customer::whereIn('id', $customer_id_all)->get();

    
    //dd($request->customer_id);
    foreach ($model as $item) {
        if ($item->id != $request->customer_id) {
            Customer::destroy($item->id);
        }
    }
}


function saveHistoryFromRequest($request)
{
    // Obtenemos el array de customer IDs
    $customer_id_all = $request->customer_id_all;
    
    // Obtenemos todos los registros de historial que coincidan con los customer IDs en el array
    $histories = CustomerHistory::whereIn('customer_id', $customer_id_all)->get();

    // Iteramos sobre cada historial y actualizamos el customer_id al unificado
    foreach ($histories as $history) {
        $history->customer_id = $request->customer_id;
        $history->save();
    }
}


    public function findDuplicates(Request $request)
    {
        $model = Customer::select('email', 'phone', DB::raw('count(*) as count'))
            ->where(function($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone');
            })
            ->groupBy('email', 'phone')
            ->havingRaw('count(*) > 1')
            ->orderBy('email')
            ->orderBy('phone')
            ->get();

        if ($model->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron duplicados.');
        }

        return view('optimizer.index', compact('model'));
    }



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


    public function consolidateDuplicates(Request $request)
    {
        $query = $request->input('query'); // Recibimos un solo parámetro de búsqueda

        // Si el campo está vacío, redirigir con error
        if (empty($query)) {
            return redirect()->back()->with('error', 'Debe ingresar un correo o un teléfono para buscar duplicados.');
        }

        // Buscar por teléfono si el query es un número, o por email si no lo es
        $model = Customer::where('phone', $query)
                        ->orWhere('email', 'like', "%$query%")
                        ->get();

        // Si no hay resultados, redirigir con error
        if ($model->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron duplicados.');
        }

        $statuses_options = CustomerStatus::all();
        $products = Product::all();
        $customers_source = CustomerSource::all();
        $user = User::where("status_id", 1)->where("role_id", 2)->get();

        $controller = $this;

        return view('optimizer.show', compact('model', 'statuses_options', 'controller', 'products', 'customers_source', 'user'));
    }


    
    
    
    

    public function showDuplicates($email, Request $request){
        /*
        dd($email);
        
        $status = CustomerStatus::all();
        if( !empty($data) ){
            //$data->status_id = CustomerStatus::($data->status_id)
            return view('optimizer.show', compact('data', 'status'));
        }
        */

    }

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