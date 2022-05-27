<?php

namespace App\Http\Controllers\Auth;

use App\ActiveUser;
use App\Event;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

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
    protected $redirectAfterLogout = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', [ 'except' => 'logout' ]);
    }

    /**
     * Override the username method used to validate login
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Overriding laravel auth login function to update the user passwords
     **/
    public function login(Request $request)
    {
        $isLoggedIn = false;
        $this->validateLogin($request);
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        $credentials = $this->credentials($request);

        // check if user exists
        $user = User::where('username', $credentials['username'])->first();
        // user exists
        if ($user) {
            if ($user->password != "") {
                if ($this->guard()->attempt($credentials, $request->has('remember'))) {
                    $isLoggedIn = true;
                }
            } // laravel password is empty
            else {
                $pass = $credentials['password'];
                if ($user->hashed_password == sha1($pass)) {
                    $user->password = \Hash::make($pass);
                    $user->save();
                    if ($this->guard()->attempt($credentials, $request->has('remember'))) {
                        $isLoggedIn = true;
                    }
                }
            }

            if ($isLoggedIn) {
                $userLevel = substr(Auth::user()->user_group_member, -1);
                switch ($userLevel) {
                    case 1:
                        session([ 'userLevel' => 'Unit' ]);
                        break;
                    case 2:
                        session([ 'userLevel' => 'Ops' ]);
                        break;
                    case 3:
                        session([ 'userLevel' => 'HQ' ]);
                        break;
                    case 4:
                        session([ 'userLevel' => 'Admin' ]);
                        break;
                    case 5:
                        session([ 'userLevel' => 'SU' ]);
                        break;
                    case 6:
                        session([ 'userLevel' => 'Management' ]);
                        break;
                }

                session([ 'userId' => Auth::user()->user_id, 'userName' => Auth::user()->username ]);

                // Track action
                Event::trackAction('Log in');

                // Track active user
                ActiveUser::create(
                    [
                        'user_id'       => auth()->user()->user_id,
                        'session_token' => $request->session()->getToken(),
                        'ip_address'    => ip2long($request->ip()),
                        'expired_at'    => !$request->has('remember') ? Carbon::now()
                            ->addMinutes(config('session.lifetime')) : null,
                    ]
                );

                return $this->sendLoginResponse($request);
            }
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        if (auth()->user()) {
            // Track action
            Event::trackAction('Log out');

            // Remove active user
            ActiveUser::where('user_id', auth()->user()->user_id)
                ->where('session_token', $request->session()->getId())
                ->delete();
        }

        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect($this->redirectAfterLogout);
    }
}
