<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Project;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Supports\ApiHC;
use App\Supports\WZone;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('welcome');
    }

    public function username()
    {
        $username = request()->input('email');
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : (strlen($username) && is_numeric($username) == 16 ? 'nik' : 'nip');
        request()->merge([$field => $username]);
        return $field;
    }

    public function callbackSSO()
    {
        $libWzone = new WZone();
        $statusLogin = $libWzone->cekValidToken(request()->input('token'));
        if (!($statusLogin['responseStatus'] ?? 0)) {
            return redirect()->route('login')->withErrors(['Token tidak valid']);
        }

        $responseData = $statusLogin['responseData'];

        $userExist = User::where('email', $responseData['email'])->first();
        if (!$userExist) {
            $userExist = User::where('nip', $responseData['nip'])->first();
            if (!$userExist) {
                $userExist = User::create([
                    'email' => $responseData['email'],
                    'name' => $responseData['full_name'],
                    'nip' => $responseData['nip'],
                    'unit_type_id' => 0,
                    'unit_id' => 0,
                    'password' => Hash::make(Str::random(10)),
                    'username' => $responseData['username'] ?? null,
                    // 'departemen' => $responseData['departemen'] ?? null,
                    'jabatan' => $responseData['jabatan'] ?? null,
                    'kd_jabatan' => $responseData['kd_jabatan'] ?? null,
                    'meta' => $responseData
                ]);
            }

            $kdJabatan = $userExist->kd_jabatan;
            $jabatan = Jabatan::with('levels')->where('code', $kdJabatan)->first();
    
            if ($jabatan) {
                $defaultLevel = env('DEF_LEVEL');
                $userExist->update([
                    'jabatan_id' => $jabatan?->id,
                    'level_id' => $jabatan?->levels?->first()?->id ?: $defaultLevel,
                ]);
            }

            $defaultRole = env('DEF_ROLE');
            if ($defaultRole) {
                $role = Role::find($defaultRole);
                if ($role) {
                    $userExist->roles()->attach($role->id);
                }
            }
        } else {
            $userExist->update([
                'name' => $responseData['full_name'],
                'email' => $responseData['email'],
                'nip' => $responseData['nip'],
                'username' => $responseData['username'] ?? null,
                'departemen' => $responseData['departemen'] ?? null,
                'jabatan' => $responseData['jabatan'] ?? null,
                'kd_jabatan' => $responseData['kd_jabatan'] ?? null,
                'meta' => $responseData
            ]);
        }

        $apiHC = new ApiHC();
        $response = $apiHC->apiRequest('GET', '/', [
            'client' => 'risk',
            'method' => 'get_pegawai',
            'key' => '38VeNwf5',
            'nip' => $userExist->nip,
        ]);

        $costCenterParent = $response['data'][0]['cost_center_parent'] ?? null;

        if ($costCenterParent) {
            $unit = Unit::where('cost_center', $costCenterParent)->first();
            if ($unit) {
                $userExist->update([
                    'unit_type_id' => $unit?->unit_type_id ?: 0,
                    'unit_id' => $unit?->id ?: 0,
                ]);
            }
        }

        $namaProyek = $responseData['nama_proyek'] ?? null;
        $project = Project::where('project_name', $namaProyek)->first();

        if ($project) {
            $userExist->projects()->attach($project->id);
        }

        Auth::guard('web')->login($userExist);
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        // $wzone = new WZone();
        // if ($user->nip && $wzone->getStatusLogin($user->nip)['responseData']['status_login'] ?? 0) {
        //     // do something here
        // }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return redirect(config('wzone.url'));

        return $request->wantsJson()
            ? response()->json([], 204)
            : redirect('/');
    }

}
