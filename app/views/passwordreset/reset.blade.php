@extends('master/index')

@section('content')
<h2>Choose New Password</h2>
<hr>


@if (Session::has('error'))
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ trans(Session::get('reason')) }}</strong>
</div>
@endif

{{ Form::open() }}
<input type="hidden" name="token" value="{{ $token }}">
<div class="form-group">
    <label for="email">Email<small>*</small></label>
    {{ Form::text('email','',array('class'=>'form-control','id'=>'email','placeholder'=>'Your Email','required'=>'required')) }}
</div>
<div class="form-group">
    <label for="password">New Password<small>*</small></label>
    {{ Form::password('password',array('class'=>'form-control','id'=>'password','placeholder'=>'Enter Password','autocomplete'=>'off','required'=>'required')) }}
</div>
<div class="form-group">
    <label for="password_confirmation">Retype Confirmation<small>*</small></label>
    {{ Form::password('password_confirmation',array('class'=>'form-control','id'=>'password_confirmation','placeholder'=>'Confirm Password','autocomplete'=>'off','required'=>'required')) }}
</div>
{{ Form::submit('Reset Password',array('class'=>'btn btn-success'))}}
{{ Form::close() }}
@stop