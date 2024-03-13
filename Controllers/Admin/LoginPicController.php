<?php


namespace App\Http\Controllers\Admin;



use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Users\UsersRolesEntryModel;
use Anomaly\Streams\Platform\Model\Users\UsersUsersEntryModel;


use Anomaly\UsersModule\User\UserAuthenticator;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use Carbon\Carbon;

class LoginPicController extends PublicController
{

    public function picLogin(){

        return view('login');

    }

    public function logInSend(Guard $auth, UserAuthenticator $authenticator){

        $credentials = \request()->validate([
            'email' => 'required|email',
//            'password' => 'required|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|max:16',
            'password' => 'required',
        ]);

        if ($auth->attempt($credentials)) {
            if ($auth->user()->hasRole('pic_user')) {
                $this->request->session()->regenerate();

                return redirect()->to('check-in-detail');
            } else {
                $authenticator->logout();
                return back()->withErrors([
                    'error' => 'You are not authorized !.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);

    }

    public function picLogout(UserAuthenticator $authenticator, Guard $auth)
    {
        if (!$auth->guest()) {
            $authenticator->logout();

            return redirect()->to('login')->with([
                'success' => 'You are successfully loged out !.',
            ]);
        }
    }

}
