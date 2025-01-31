<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerStatus;
use DB;

class CustomerStatusController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // $customer_statuses = CustomerStatus::
        //                         where('customer_statuses.weight')
        //                         ->orderBy('weight','asc')
        //                         ->get();
        $customer_statuses = CustomerStatus::orderBy('stage_id', 'DESC')
            ->orderBy('weight', 'ASC')->get();

        return view('customer_statuses.index', compact('customer_statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $customer_statuses = CustomerStatus::all();
        return view('customer_statuses.create', compact( 'customer_statuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $model = new CustomerStatus;

        $model->id = $request->id;
        $model->name = $request->name;
        $model->description = $request->description;
        $model->weight = $request->weight;
        $model->color = $request->color;
        $model->stage_id = $request->stage_id;
        // $model->created_at = $request->created_at;
        // $model->updated_at = $request->updated_at;

        
        $model->save();

        return redirect('/customer_statuses');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $customer_statuses = CustomerStatus::find($id);

        return view('customer_statuses.show', compact('customer_statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
      $customer_statuses = CustomerStatus::find($id);


        return view('customer_statuses.edit', compact('customer_statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $model = CustomerStatus::find($id);

        $model->name = $request->name;
        $model->description = $request->description;
        $model->weight = $request->weight;
        $model->color = $request->color;
        $model->stage_id = $request->stage_id;
        
        $model->save();

        return redirect('/customer_statuses');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         $model = CustomerStatus::find($id);
         $model->delete();
      
            return redirect('customer_statuses'); 
    
    }
}
