<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class RegistrationController extends BaseController
{

    public function validateUser($username, $code)
    {
        $user = User::where('username', '=', $username)->first();
        if ($user->confirmed === $code) {
            $user->confirmed = 1;
            $user->save();
			mail('james@theracersmedia.com', "TRM new user", "User $username has registered and validated");
			}

        return Redirect::to('gallery')->with('flashError', t('You are not registered with us'));
    }

    public function getIndex()
    {
        return View::make('registration/index')
            ->with('title', 'Registration');
    }

    public function postIndex()
    {
        if (Auth::check() == TRUE) {
            return Redirect::to('gallery')->with('flashError', t('You are already logged in'));
        }

        $v = User::validate(Input::all());
        if ($v->fails()) {
            return Redirect::to('registration')->withErrors($v);
        }
        if (Input::get('gender') != 'male' && Input::get('gender') != 'female') {
            return Redirect::to('registration')->with('flashError', t('Invalid Gender'));
        }

        $activationCode = sha1(str_random(11) . (time() * rand(2, 2000)));
        $user = new User();
        $user->username = Input::get('username');
        $user->fullname = Input::get('fullname');
        $user->gender = Input::get('gender');
        $user->email = Input::get('email');
        $user->password = Hash::make(Input::get('password'));
        $user->confirmed = $activationCode;
        $user->save();

        $data = array(
            'fullname'       => Input::get('fullname'),
            'username'       => Input::get('username'),
            'activationcode' => $activationCode
        );
        Mail::send('emails.registration.welcome', $data, function ($message) {
            $message->to(Input::get('email'), Input::get('fullname'))->subject('Welcome to ' . siteSettings('siteName'));
        });

        return Redirect::to('login')->with('flashSuccess', t('A confirmation email is sent to your mail'));
    }

    public function getFacebook()
    {
        if (!Session::get('fbdetails')) {
            return Redirect::to('login');
        }
        return View::make('registration/facebook')
            ->with('title', 'Facebook Login');
    }

    public function postFacebook()
    {
        $session = Session::get('fbdetails');
        if (!$session) {
            return Redirect::to('login');
        }

        $input = array(
            'username'              => Input::get('username'),
            'password'              => Input::get('password'),
            'password_confirmation' => Input::get('password_confirmation')
        );

        $rules = array(
            'username'              => array('Required', 'Min:3', 'Max:20', 'alpha_num','Unique:users'),
            'password'              => array('Required', 'Between:4,25', 'Confirmed'),
            'password_confirmation' => array('Required', 'Between:4,25'),
        );
        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            return Redirect::to('registration/facebook')->withErrors($v);
        }
        $user = new User();
        $user->username = Input::get('username');
        $user->password = Hash::make(Input::get('password'));
        $user->fbid = $session['id'];
        $user->email = $session['email'];
        $user->gender = $session['gender'];
        $user->fullname = $session['name'];
        $user->confirmed = 1;
        $user->save();

        Auth::loginUsingId($user->id);
        return Redirect::to('gallery')->with('flashSuccess',t('Congratulations your account is created and activated'));
    }
}