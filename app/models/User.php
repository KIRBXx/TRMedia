<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */

    protected $softDelete = TRUE;

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    public function is_validated(){
      return $this->is_validated;
    }


    public function images()
    {
        return $this->hasMany('Images');
    }

    public function latestImages()
    {
        return $this->hasMany('Images')->orderBy('created_at', 'desc')->where('approved','=','1');
    }

    public function comments()
    {
        return $this->hasMany('Comment');
    }

    public function favorites()
    {
        return $this->hasMany('Favorite', 'user_id');
    }

    public function followers()
    {
        return $this->hasMany('Follow', 'follow_id');
    }

    public function following()
    {
        return $this->hasMany('Follow', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany('Notification', 'to_id');
    }


    public static function validate($input)
    {

        $rules = array(
            'username' => 'Required|Min:3|Max:20|alpha_num|Unique:users',
            'fullname' => 'Required|Min:3|Max:80|regex:/^([a-z0-9\x20])+$/i',
            'gender' => 'Required',
            'email' => 'Required|Between:3,64|Email|Unique:users',
            'password' => 'Required|Between:4,25|Confirmed',
            'password_confirmation' => 'Required|Between:4,25'
            //'recaptcha_response_field' => 'required|recaptcha'
        );

        return Validator::make($input, $rules);
    }


}
