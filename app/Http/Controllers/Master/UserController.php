<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles','unit')->withTrashed()->get();
        $roless = Role::with('permissions')->get();
        $roles = Role::pluck('name', 'id'); // Get roles for select dropdown

        return view('master.users.index', compact('users', 'roles', 'roless'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id'); // Get roles for select dropdown
        $roless = Role::with('permissions')->get();
        $unit = Unit::pluck('name','id');

        $projects = Project::pluck('project_name','id');

        return view('master.users.create', compact('roles', 'roless','unit', 'projects'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'unit_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'unit_id' => $request->unit_id,
            'unit_type_id' => Unit::findOrFail($request->unit_id)->unitType->id,
            'parent_id' => Unit::findOrFail($request->unit_id)->parent_id,
        ]);

        if ($request->has('user_projects')) {
            $user->projects()->attach($request->user_projects);
        }

        $user->assignRole($request->roles); // Assign selected roles to the user

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'id'); // Get roles for select dropdown
        $userRoles = $user->roles->pluck('id')->toArray(); // Get user's current roles
        $roless = Role::with('permissions')->get();
        $unit = Unit::pluck('name','id');
        // dd($unit);
        $projects = Project::pluck('project_name','id');

        return view('master.users.edit', compact('user', 'roles', 'userRoles', 'roless', 'unit', 'projects'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id, // Update validation for unique email excluding current user
            'roles' => 'required|array',
            'unit_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'unit_id' => $request->unit_id,
            'unit_type_id' => Unit::findOrFail($request->unit_id)->unitType->id,
            'parent_id' => Unit::findOrFail($request->unit_id)->parent_id,
        ]);

        if ($request->has('user_projects')) {
            $user->projects()->sync($request->user_projects);
        }

        // Sync user roles (removes existing and adds new ones)
        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user) {
            $user->restore();
            return redirect()->route('users.index')->with('success', 'User restored successfully!');
        }

        return redirect()->route('users.index')->with('error', 'User not found!');
    }
}
