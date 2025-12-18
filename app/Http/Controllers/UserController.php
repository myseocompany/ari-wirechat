<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['role', 'status'])
            ->orderBy('name')
            ->get();
        $user_statuses = UserStatus::all();

        return view('users.index', compact('users', 'user_statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $user_statuses = UserStatus::all();

        return view('users.create', compact('roles', 'user_statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'status_id' => ['nullable', 'integer', 'exists:user_statuses,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $model = new User();
        $model->name = $validated['name'];
        $model->email = $validated['email'];
        $model->status_id = $validated['status_id'] ?? null;
        $model->role_id = $validated['role_id'] ?? null;
        $model->password = bcrypt($validated['password']);
        $model->image_url = $this->handleProfilePhotoUpload($request);

        $model->save();

        return redirect('/users');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);
        $user_statuses = UserStatus::all();
        $roles = Role::all();

        return view('users.edit', compact('user', 'user_statuses', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $model = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'status_id' => ['nullable', 'integer', 'exists:user_statuses,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $model->name = $validated['name'];
        $model->email = $validated['email'];
        $model->status_id = $validated['status_id'] ?? null;
        if (! empty($validated['password'])) {
            $model->password = bcrypt($validated['password']);
        }
        $model->role_id = $validated['role_id'] ?? null;
        $model->image_url = $this->handleProfilePhotoUpload($request, $model->image_url);

        $model->save();

        return redirect('/users');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    protected function handleProfilePhotoUpload(Request $request, ?string $currentPath = null): ?string
    {
        if (! $request->hasFile('profile_photo')) {
            return $currentPath;
        }

        if ($currentPath) {
            $this->deleteStoredProfilePhoto($currentPath);
        }

        $path = $request->file('profile_photo')->store('public/users');

        return Storage::url($path);
    }

    protected function deleteStoredProfilePhoto(?string $storedPath): void
    {
        if (! $storedPath) {
            return;
        }

        $normalized = ltrim($storedPath, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $relative = 'public/' . Str::after($normalized, 'storage/');
            Storage::delete($relative);
        } elseif (Str::startsWith($normalized, 'public/')) {
            Storage::delete($normalized);
        }
    }
}
