<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class Images extends Basemodel
{

    protected $table = 'images';
    protected $softDelete = TRUE;

    public static $rules = array(
        'title'    => 'required|max:200',
        'file'     => 'required|image',
        'category' => 'required',
        'tags'     => 'required'
    );

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function comments()
    {
        return $this->hasMany('Comment', 'image_id');
    }

    public function favorite()
    {
        return $this->hasMany('Favorite', 'image_id');
    }

}