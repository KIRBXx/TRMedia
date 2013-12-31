<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class PasswordresetController extends BaseController
{
    public function resetPassword()
    {
        $credentials = array('email' => Input::get('email'));

        return Password::reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);

            $user->save();

            return Redirect::to('/')->with('flashSuccess', 'Your password is now resetted');
        });
    }

    public function passwordReset($token)
    {
        return View::make('passwordreset/reset')->with('token', $token)->with('title', 'Resetting password');
    }

    public function getIndex()
    {
        return View::make('passwordreset/index')
            ->with('title', 'Password Rest');
    }

    public function postIndex()
    {
        $rules = array(
            'email' => array('required', 'email'),
            'recaptcha_response_field' => array('required', 'recaptcha'),
        );
        $v = Validator::make(Input::all(), $rules);
        if ($v->fails()) {
            return Redirect::to('password/remind')
                ->withErrors($v);
        }
        $credentials = array('email' => Input::get('email'));
        $r = Password::remind($credentials);
        return Redirect::to('password/remind')->with('flashSuccess', 'Your password configuration is send to your email');
    }
}