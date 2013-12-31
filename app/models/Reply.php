<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class Reply extends Eloquent
{
    protected $table = 'reply';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function image()
    {
        return $this->belongsTo('Images', 'image_id');
    }

    public function comment()
    {
        return $this->belongsTo('Comment');
    }
}