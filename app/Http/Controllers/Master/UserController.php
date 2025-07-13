<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\Supports\ApiHC;
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
        $jabatans = Jabatan::with('levels')->get();

        return view('master.users.create', compact('roles', 'roless','unit', 'projects', 'jabatans'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|sometimes|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'unit_id' => 'required',
        ]);

        $dataUser = null;
        if ($request->search) {
            $remoteUser = $this->searchRemoteUser($request);
            if ($remoteUser->getStatusCode() == 404) {
                return back()->withErrors(['search' => 'User tidak ditemukan'])->withInput();
            }
            $dataUser = $remoteUser->getData(true)['data'];
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unitId = $request->unit_id;
        if (is_numeric($unitId)) {
            $unitId = $unitId;
        } elseif ($dataUser) {
            $unitId = null;
            if ($dataUser['nm_unit']) {
                $unit = Unit::where('name', $dataUser['nm_unit'])->first();
                if (!$unit) {
                    $unit = Unit::create([
                        'name' => $dataUser['nm_unit'],
                        'unit_type_id' => 2,
                        'parent_id' => 0,
                    ]);
                }
                $unitId = $unit->id;
            }
        }

        $unit = Unit::findOrFail($unitId);

        $jabatan = Jabatan::with('levels')->find($request->jabatan_id);

        $user = User::create([
            'name' => $dataUser['nm_peg'] ?? $request->name,
            'email' => ($dataUser['email'] ?? null) ? $dataUser['email'] : $request->email,
            'nip' => ($dataUser['nip'] ?? null) ? $dataUser['nip'] : $request->nip,
            'nik' => ($dataUser['nik'] ?? null) ? $dataUser['nik'] : $request->nik,
            'password' => Hash::make($request->password),
            'unit_id' => $unitId,
            'unit_type_id' => $unit->unitType->id,
            'parent_id' => $unit->parent_id,
            'jabatan_id' => $jabatan->id,
            'level_id' => $jabatan?->levels?->first()?->id,
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
        $jabatans = Jabatan::with('levels')->get();

        return view('master.users.edit', compact('user', 'roles', 'userRoles', 'roless', 'unit', 'projects', 'jabatans'));
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

        $jabatan = Jabatan::find($request->jabatan_id)->with('levels')->first();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'unit_id' => $request->unit_id,
            'unit_type_id' => Unit::findOrFail($request->unit_id)->unitType->id,
            'parent_id' => Unit::findOrFail($request->unit_id)->parent_id,
            'jabatan_id' => $request->jabatan_id,
            'level_id' => $jabatan?->levels?->first()?->id,
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

    public function searchRemoteUser(Request $request)
    {
        $api = new ApiHC();
        $value = $request->search;
        $column = 'nip';
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $column = 'email';
        }
        $response = $api->apiRequest('GET', '/', ['method' => 'get_pegawai', $column => $value]);

        $dataUser = $response['data'][0] ?? null;

        if (!$dataUser) {
            return response()->json([
                'message' => 'Tidak ditemukan',
            ], 404);
        }

        if ($request->not_registered_only) {
            $email = $dataUser['email'] ?? null;
            $nip = $dataUser['nip'] ?? null;

            if ($email && $nip) {
                $user = User::where('email', $email)->orWhere('nip', $nip)->first();
                if ($user) {
                    return response()->json([
                        'message' => 'User sudah terdaftar',
                    ], 400);
                }
            } elseif ($email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    return response()->json([
                        'message' => 'User sudah terdaftar',
                    ], 400);
                }
            } elseif ($nip) {
                $user = User::where('nip', $nip)->first();
                if ($user) {
                    return response()->json([
                        'message' => 'User sudah terdaftar',
                    ], 400);
                }
            }
        }

        $jabatan = null;
        if ($dataUser['kd_jabatan'] ?? false) {
            $jabatan = Jabatan::where('code', $dataUser['kd_jabatan'])->first();
        }

        return response()->json(['data' => $dataUser, 'jabatan' => $jabatan]);
    }
}
