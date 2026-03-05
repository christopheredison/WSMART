<?php

namespace App\Http\Controllers\Master;

use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Level;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\Supports\ApiHC;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with(['roles', 'unit', 'level'])->withTrashed()->select('users.*');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row) {
                    return '<div class="form-check mb-0">
                              <input class="form-check-input" type="checkbox" data-bulk-select-row="data-bulk-select-row" value="'.$row->id.'" />
                            </div>';
                })
                ->addColumn('level', function($row) {
                    return $row?->level?->name ? $row?->level?->name : '-';
                })
                ->addColumn('role_names', function($row) {
                    // Map roles to a string
                    return $row->roles->map(function($role) {
                        return ucwords(str_replace('_', ' ', $role->name));
                    })->implode(', ');
                })
                ->addColumn('action', function($row) {
                    $btn = '';
                    if ($row->trashed()) {
                        $btn .= '<button type="button" class="btn-input-icon ps-0" onclick="restoreUser('.$row->id.')">
                                    <span class="bx bx-undo" title="Undo"></span>
                                </button>';
                    } else {
                        $editUrl = route('users.edit', $row->id);
                        $btn .= '<a href="'.$editUrl.'" class="btn-input-icon" title="Edit"><span class="bx bx-edit"></span></a>';
                        $btn .= '<button type="button" class="btn-input-icon" onclick="deleteUser('.$row->id.')">
                                    <span class="bx bx-trash text-danger" title="Delete"></span>
                                </button>';
                    }
                    return $btn;
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        $roless = Role::with('permissions')->get();
        $roles = Role::pluck('name', 'id');

        return view('master.users.index', compact('roles', 'roless'));
    }

    public function create()
    {
        $roles = Role::orderBy('name', 'asc')->pluck('name', 'id'); // Get roles for select dropdown
        $roless = Role::with('permissions')->orderBy('name', 'asc')->get();
        $unit = Unit::pluck('name','id');

        $projects = Project::pluck('project_name','id');
        $jabatans = Jabatan::get();
        $levels = Level::get();

        return view('master.users.create', compact('roles', 'roless','unit', 'projects', 'jabatans', 'levels'));
    }

    public function store(Request $request)
    {
        $isRemoteUser = !empty($request->search) && empty($request->password);

        $rules = [
            'search' => 'nullable|sometimes|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'roles' => 'required|array|min:1',
            'unit_id' => 'required',
        ];

        if (!$isRemoteUser) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

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
        if (!is_numeric($unitId)) {
            return back()->withErrors(['unit_id' => 'Silakan pilih unit yang valid']).withInput();
        }

        $unit = Unit::findOrFail($unitId);
        $jabatan = Jabatan::find($request->jabatan_id);
        $finalPassword = $isRemoteUser ? Str::random(32) : $request->password;

        $user = User::create([
            'name' => $dataUser['nm_peg'] ?? $request->name,
            'email' => ($dataUser['email'] ?? null) ? $dataUser['email'] : $request->email,
            'nip' => ($dataUser['nip'] ?? null) ? $dataUser['nip'] : $request->nip,
            'nik' => ($dataUser['nik'] ?? null) ? $dataUser['nik'] : $request->nik,
            'password' => Hash::make($finalPassword),
            'unit_id' => $unitId,
            'unit_type_id' => $unit->unitType->id,
            'parent_id' => $unit->parent_id,
            'jabatan_id' => $jabatan->id,
            'level_id' => $request->level_id,
        ]);

        if ($request->has('user_projects')) {
            $user->projects()->attach($request->user_projects);
        }

        $user->assignRole($request->roles); // Assign selected roles to the user

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name', 'asc')->pluck('name', 'id'); // Get roles for select dropdown
        $userRoles = $user->roles->pluck('id')->toArray(); // Get user's current roles
        $roless = Role::with('permissions')->orderBy('name', 'asc')->get();
        $unit = Unit::pluck('name','id');
        // dd($unit);
        $projects = Project::pluck('project_name','id');
        $jabatans = Jabatan::get();
        $levels = Level::get();

        return view('master.users.edit', compact('user', 'roles', 'userRoles', 'roless', 'unit', 'projects', 'jabatans', 'levels'));
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

        //$jabatan = Jabatan::find($request->jabatan_id)->first();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'unit_id' => $request->unit_id,
            'unit_type_id' => Unit::findOrFail($request->unit_id)->unitType->id,
            'parent_id' => Unit::findOrFail($request->unit_id)->parent_id,
            'jabatan_id' => $request->jabatan_id,
            'level_id' => $request->level_id,
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

        // Resolve unit by cost_center_parent from API user data
        $resolvedUnit = null;
        $ccParent = $dataUser['cost_center_parent'] ?? null;
        if ($ccParent) {
            // Try to find unit by cost_center matching cost_center_parent from API
            $unit = Unit::where('cost_center', $ccParent)->first();
            if ($unit) {
                $resolvedUnit = ['id' => $unit->id, 'name' => $unit->name];
            }
        }

        return response()->json([
            'data' => $dataUser,
            'jabatan' => $jabatan,
            'resolved_unit' => $resolvedUnit,
            'debug' => [
                'cost_center_parent' => $ccParent,
                'unit_found' => $resolvedUnit ? true : false,
                'nm_unit' => $dataUser['nm_unit'] ?? null
            ]
        ]);
    }
}
