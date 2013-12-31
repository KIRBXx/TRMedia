@extends('master/index')

@section('content')
<h2>Registration</h2>
<hr>
{{ Form::open() }}
<div class="form-group">
    <label for="email">Email<small>*</small></label>
    {{ Form::text('email','',array('class'=>'form-control','id'=>'email','placeholder'=>'Your Email','required'=>'required')) }}
</div>
<div class="form-group">
    <label for="recaptcha">Type these words<small>*</small></label>
    {{ Form::captcha() }}
</div>

{{ Form::submit('Reset Password',array('class'=>'btn btn-success'))}}
{{ Form::close() }}
@stop