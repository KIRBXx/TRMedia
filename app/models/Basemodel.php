<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Abhimanyu Sharma
 * Date: 9/16/13
 * Time: 1:44 AM
 * To change this template use File | Settings | File Templates.
 */
class Basemodel extends Eloquent
{

    public static function validate($input)
    {
        return Validator::make($input, static::$rules);
    }
}