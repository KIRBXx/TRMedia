<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class SettingsController extends BaseController
{

    public function getSettings()
    {
        $user = Auth::user()->find(Auth::user()->id);
        return View::make('settings/index')
            ->with('user', $user)
            ->with('title', 'Settings');
    }

    public function postUpdateAvatar()
    {

        $imageName = $this->dirName();
        $file = Image::open(Input::file('avatar'))->cropResize(400, 400)->save(public_path() . '/avatar/' . $imageName . '.jpg', 'jpg', 90);
        $update = Auth::user();
        $update->avatar = $imageName;
        $update->save();
        return Redirect::to('settings')->with('flashSuccess', 'Your avatar is now updated');
    }

    private function dirName()
    {
        $str = str_random(9);
        if (file_exists(public_path() . '/avatar/' . $str)) {
            $str = $this->dirName();
        }
        return $str;
    }

    public function postUpdateProfile()
    {
        $input = Input::all();
        $rules = array(
            //'fullname' => array('required', 'regex:/^([a-z0-9\x20])+$/i'),
            'gender' => array('required'),
            'country' => array('required', 'alpha_num'),
            'dob' => array('date_format:Y-m-d'),
            'blogurl' => array('url'),
            'fb_link' => array('url'),
            'tw_link' => array('url')
        );
        $validate = Validator::make($input, $rules);

        if ($validate->fails()) {
            return Redirect::to('settings')->withErrors($validate)->withInput($input);
        }
        $gender = Input::get('gender');

        if ($gender != 'male' && $gender != 'female') {
            return Redirect::to('settings')->with('flashError', 'Invalid Gender');
        }

        if (countryIsoCodeMatch(Input::get('country')) == false) {
            return Redirect::to('settings')->with('flashError', 'Invalid country')->withInput($input);
        }

        if (strlen(Input::get('blogurl')) > 2) {
            if (!preg_match('/^(http(?:s)?\:\/\/[a-zA-Z0-9\-]+(?:\.[a-zA-Z0-9\-]+)*\.[a-zA-Z]{2,6}(?:\/?|(?:\/[\w\-]+)*)(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]+)*)$/', Input::get('blogurl')))
                return Redirect::to('settings')->with('flashError', 'Invalid Blog Url');
        }


        $update = Auth::user();
        //$update->fullname = Input::get('fullname');
        $update->dob = Input::get('dob');
        $update->country = Input::get('country');
        $update->about_me = Input::get('aboutme');
        $update->blogurl = Input::get('blogurl');
        $update->fb_link = Input::get('fbLink');
        $update->tw_link = Input::get('twLink');
        $update->save();

        return Redirect::to('settings')->with('flashSuccess', 'Your profile is updated');

    }

    public function postChangePassword()
    {
        $input = Input::all();

        $rules = array(
            'password' => array('required', 'min:6', 'confirmed'),
            'currentpassword' => array('required')
        );
        $v = Validator::make($input, $rules);


        if ($v->fails()) {
            return Redirect::to('settings')->withErrors($v->errors());
        }

        if (Hash::check($input['currentpassword'], Auth::user()->password)) {
            $user = Auth::user();
            $user->password = Hash::make(Input::get('password'));
            $user->save();
            return Redirect::to('settings')->with('flashSuccess', 'Your password is updated');
        } else {
            return Redirect::to('settings')->with('flashError', 'Old password is not valid');
        }
    }
}