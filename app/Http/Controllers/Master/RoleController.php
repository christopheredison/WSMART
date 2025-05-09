<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use App\Supports\Helper;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        Helper::syncBuiltInPermissions();
        $roles = Role::withTrashed()->get();
        return view('master.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('master.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255',
            'permissions' => 'required|array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions'));

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::pluck('name', 'id');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('master.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        // Update permissions if any permissions are sent
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role permissions updated successfully');
    }

    public function destroy(Role $role)
    {
        $role->users()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }

    public function restore($id)
    {
        $role = Role::withTrashed()->find($id);

        if ($role) {
            $role->restore();
            return redirect()->route('roles.index')->with('success', 'Role restored successfully!');
        }

        return redirect()->route('roles.index')->with('error', 'Role not found!');
    }
}
