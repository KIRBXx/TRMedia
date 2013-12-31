<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Abhimanyu Sharma
 * Date: 9/12/13
 * Time: 10:22 PM
 * To change this template use File | Settings | File Templates.
 */
class Favorite extends Eloquent
{
    protected $table = 'favorite';

    public function user()
    {
        return  $this->belongsTo('User');
    }

    public function image()
    {
        return $this->belongsTo('Images');
    }
}