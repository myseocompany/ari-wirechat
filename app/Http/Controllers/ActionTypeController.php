<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use DB;
use App\Action;
use App\Customer;
use App\CustomerStatus;
use App\Email;
use Mail;
use App\DateTime;
use App\ActionType;

class ActionTypeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id)
    {
        $model = ActionType::find($id);
        return view('action_types.show',compact('model'));
    }
	
	public function index(){
        $model = ActionType::all();
        return view('action_types.index',compact('model'));
	}

    public function edit($id)
    {
        $model = ActionType::find($id);
        return view('action_types.edit',compact('model'));

    }

    public function update($id,Request $request){      
        $model = ActionType::find($id);
        $model->name = $request->name;
        $model->description = $request->description;
        $model->save();

        return redirect('/action_type');
    } 

    public function destroy($id)
    {
        $model = ActionType::find($id);
        if ($model->delete()) {
            return redirect('/action_type')->with('statustwo', 'La acción <strong>'.$model->name.'</strong> fué eliminada con éxito!'); 
        }
    }

    public function store(Request $request){      
        $model = new ActionType;
        $model->name = $request->name;
        $model->description = $request->description;
        $model->save();

        return redirect('/action_type');
    }
}
