<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class LoginController extends BaseController
{

    /**
     * POST Login by both email and username
     * save client IP in database if not confirmed then logout
     * @return mixed
     */
    public function postLogin()
    {
        // Check if input type is email or not
        if (filter_var(Input::get('username'), FILTER_VALIDATE_EMAIL)) {
            $input = array(
                'email'    => Input::get('username'),
                'password' => Input::get('password')
            );

            $v = array(
                'email'    => array('required'),
                'password' => array('required')
            );
        } else {
            $input = array(
                'username' => Input::get('username'),
                'password' => Input::get('password')
            );

            $v = array(
                'username' => array('required'),
                'password' => array('required')
            );
        }
        $v = Validator::make($input, $v);
        if ($v->fails()) {
            return Redirect::to('login')->withErrors($v);
        }
        $remember = Input::get('remember-me');
        if (!empty($remember)) {
            if (Auth::attempt($input, TRUE)) {
                if (Auth::user()->confirmed != 1) {
                    Auth::logout();
                    return Redirect::to('login')->with('flashError', t('Email activation is required'));
                }
                $user = Auth::user();
                $user->ip_address = Request::getClientIp();
                $user->save();
                return Redirect::to('gallery')->with('flashSuccess', t('You are now logged in'));
            }
        }
        if (Auth::attempt($input)) {
            $user = Auth::user();
            if (Auth::user()->confirmed != 1) {
                Auth::logout();
                return Redirect::to('login')->with('flashError', t('Email activation is required'));
            }
            $user->ip_address = Request::getClientIp();
            $user->save();
            return Redirect::to('gallery')->with('flashSuccess', t('You are now logged in'));
        }
        return Redirect::to('login')->with('flashError', t('Your username/password combination was incorrect'));
    }

    /**
     * Make view of login
     * @return mixed
     */
    public function getLogin()
    {
        return View::make('login/index')
            ->with('title', 'Login');
    }

    /**
     * Logout from site
     * @return mixed
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('/');
    }

    /**
     * Login by facebook, check if only email address in database
     * then save users facebook is to database for future user.
     * If both not exits starts session and redirect to registration page
     * @return mixed
     */
    public function getFacebook()
    {
        $social = Facebook::api('/me?fields=id,name,email,gender');
        if (!$social) {
            return Redirect::to('login')->with('flashError', t('Please try again'));
        }

        $user = User::where('fbid', '=', $social['id'])->first();

        if ($user) {
            if ($user->fbid == $social['id']) {
                Auth::loginUsingId($user->id);
                $user = Auth::user();
                $user->ip_address = Request::getClientIp();
                $user->save();
                return Redirect::to('gallery')->with('flashSuccess', t('You are now logged in'));
            }
        }
        $user = User::where('email', '=', $social['email'])->first();
        if ($user) {
            if ($user->email == $social['email']) {
                $user->fbid = $social['id'];
                $user->save();
                Auth::loginUsingId($user->id);
                return Redirect::to('gallery')->with('flashSuccess', t('You are now logged in'));
            }
        }
        Session::put('fbdetails', $social);
        return Redirect::to('registration/facebook');

    }


}