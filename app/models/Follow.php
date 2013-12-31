<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class Follow extends Eloquent
{
    protected $table = 'follow';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function followingUser()
    {
        return $this->belongsTo('User', 'follow_id');
    }
}