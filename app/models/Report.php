<?php

class Report extends Basemodel
{
    protected $table = 'report';
    protected $softDelete = true;
    public static $rules = array(
        'report' => 'required|min:10|max:200'
    );

    public function user()
    {
        return $this->belongsTo('User');
    }
}