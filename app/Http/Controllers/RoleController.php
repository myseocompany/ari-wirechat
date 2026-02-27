<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;

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
    public function show($id)
    {
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
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*.create' => ['nullable', 'in:1'],
            'permissions.*.read' => ['nullable', 'in:1'],
            'permissions.*.update' => ['nullable', 'in:1'],
            'permissions.*.delete' => ['nullable', 'in:1'],
            'can_view_all_customers' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('permissions', $validated)) {
            $data = $validated['permissions'] ?? [];
            $syncData = [];

            foreach ($data as $menuId => $perms) {
                $syncData[$menuId] = [
                    'create' => isset($perms['create']) ? 1 : 0,
                    'read' => isset($perms['read']) ? 1 : 0,
                    'update' => isset($perms['update']) ? 1 : 0,
                    'delete' => isset($perms['delete']) ? 1 : 0,
                ];
            }

            $role->menus()->sync($syncData);
        }
        $role->can_view_all_customers = $request->boolean('can_view_all_customers');
        $role->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Permiso guardado correctamente',
                'can_view_all_customers' => (bool) $role->can_view_all_customers,
            ]);
        }

        return redirect()->back()->with('success', 'Permisos actualizados');
    }
}
