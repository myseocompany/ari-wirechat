<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use DB;
use App\Models\UserStatus;
use App\Models\Menu;

class RoleController extends Controller
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
        $model = Role::all();
        return view('roles.index', compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('roles.create');
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
        $model = new Role;
        $model->id = $request->id;
        $model->name = $request->name;
        
        $model->save();

        return redirect('/roles');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $role = Role::with(['menus', 'users'])->findOrFail($id);
        $menus = Menu::orderBy('name')->get();

        return view('roles.show', compact('role', 'menus'));
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
        $model = Role::find($id);

        return view('roles.edit', compact('model'));
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
        $model = Role::find($id);

        $model->name = $request->name;
       
        
        $model->save();

        return redirect('/roles');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         $model = Role::find($id);
        if ($model->delete()) {
            return redirect('/roles')->with('statustwo', 'El Rol <strong>'.$model->name.'</strong> fué eliminado con éxito!'); 
        }
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $data = $request->input('permissions', []);
        $syncData = [];

        foreach ($data as $menuId => $perms) {
            $syncData[$menuId] = [
                'create' => isset($perms['create']) ? 1 : 0,
                'read'   => isset($perms['read']) ? 1 : 0,
                'update' => isset($perms['update']) ? 1 : 0,
                'delete' => isset($perms['delete']) ? 1 : 0,
            ];
        }

        $role->menus()->sync($syncData);

        return redirect()->back()->with('success', 'Permisos actualizados');
    }

}
