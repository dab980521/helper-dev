<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * TODO: remove this method
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request){
        $name = $request->name;
        $password = $request->password;
        $data = [
            'message' => '登陆失败'
        ];
        if (Auth::attempt(['name' => $name, 'password' => $password])){
            $user = Auth::user();
            $api_token = str_random(10);
            $data = [
                'message' => '登陆成功',
                'api_token' => $api_token,
            ];
            Cache::put($user->name, $api_token, 10); // TODO: 魔术数字警告，指的是登录失效时间
            return response()->json($data,200);
        }
        return response()->json($data,401);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = Auth::user();
            $api_token = str_random(10);
            Cache::tags('users')->put($user->name, $api_token, 10);
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        if (Auth::user()){
            $name = Auth::user()->name;
            Cache::tags('users')->forget($name);
        }
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/login');
    }
}
