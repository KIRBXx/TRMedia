<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Abhimanyu Sharma
 * Date: 9/10/13
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */
class Comment extends Eloquent
{
    protected $table = 'comments';
    protected $softDelete = true;

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function reply()
    {
        return $this->hasMany('Reply');
    }

    public function image()
    {
        return $this->belongsTo('Images', 'image_id');
    }
}