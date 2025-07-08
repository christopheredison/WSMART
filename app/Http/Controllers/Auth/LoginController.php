<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Supports\WZone;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
                $unit = Unit::where('unit_api_id', $responseData['kd_unit_org'])->first();
                User::create([
                    'email' => $responseData['email'],
                    'name' => $responseData['full_name'],
                    'nip' => $responseData['nip'],
                    'unit_type_id' => $unit?->unit_type_id ?: 0,
                    'unit_id' => $unit?->id ?: 0,
                    'password' => Hash::make(Str::random(10)),
                ]);
            }
        }

        Auth::guard('web')->login($userExist);
        return redirect()->route('home');
    }
}
