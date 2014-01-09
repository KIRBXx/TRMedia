@extends('master/index')

@section('content')
@if (Session::has('error'))
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ trans(Session::get('reason')) }}</strong>
</div>
@endif
<h3 class="content-heading">{{ t('Register') }} or</small> <a href="{{ Facebook::loginUrl() }}" class="btn btn-sm btn-facebook">Login with facebook</a></h3>
{{ Form::open() }}
<div class="form-group">
    <label for="username">{{ t('Select Username') }}<small>*</small></label>
    {{ Form::text('username','',array('class'=>'form-control','id'=>'username','placeholder'=>t('Select Username'),'required'=>'required'))}}
</div>
<div class="form-group">
    <label for="email">{{ t('Your Email') }}<small>*</small></label>
    {{ Form::text('email','',array('class'=>'form-control','type'=>'email','id'=>'email','placeholder'=>t('Your Email'),'required'=>'required'))}}
</div>
<div class="form-group">
    <label for="gender">{{ t('Gender') }}<small>*</small></label>
    {{ Form::select('gender', array('male' => 'Male', 'female' => 'Female'), 'male',array('id'=>'gender','class'=>'form-control','required'=>'required')) }}
</div>


<div class="form-group">
    <label for="password">{{ t('Password') }}<small>*</small></label>
    {{ Form::password('password',array('class'=>'form-control','id'=>'password','placeholder'=>t('Enter Password'),'autocomplete'=>'off','required'=>'required')) }}
</div>
<div class="form-group">
    <label for="password_confirmation">{{ t('Retype Password') }}<small>*</small></label>
    {{ Form::password('password_confirmation',array('class'=>'form-control','id'=>'password_confirmation','placeholder'=>'Confirm Password','autocomplete'=>'off','required'=>'required')) }}
</div>
{{ Form::submit(('Create Account'),array('class'=>'btn btn-success'))}}
{{ Form::close() }}
@stop