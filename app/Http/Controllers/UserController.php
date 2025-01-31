<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use DB;
use App\Models\UserStatus;

class UserController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::all();
        $user_statuses = UserStatus::all();
        

        return view('users.index', compact('users', 'user_statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        $user_statuses = UserStatus::all();
        return view('users.create', compact('roles', 'user_statuses'));
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
        $model = new User;

        $model->id = $request->id;
        $model->name = $request->name;
        $model->email = $request->email;
        $model->status_id = $request->status_id;
        $model->role_id = $request->role_id;
        $model->password = bcrypt($request->password);

        
        $model->save();

        return redirect('/users');
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
        $user = User::find($id);
        // $user_statuses = UserStatus::all();
        return view('users.show', compact('user'));
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
        $user = User::find($id);
        $user_statuses = UserStatus::all();
        $roles = Role::all();
        return view('users.edit', compact('user', 'user_statuses', 'roles'));
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
        $model = User::find($id);

        $model->name = $request->name;
        $model->email = $request->email;
        $model->status_id = $request->status_id;
        if($model->password != $request->password)
            $model->password = bcrypt($request->password);
        $model->role_id = $request->role_id;
        
        $model->save();

        return redirect('/users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
