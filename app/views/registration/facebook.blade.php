@extends('master/index')

@section('content')
@if (Session::has('error'))
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ trans(Session::get('reason')) }}</strong>
</div>
@endif
<h3 class="content-heading">{{ t('Registration') }}</h3>
{{ Form::open() }}
<div class="form-group">
    <label for="username">Select Username<small>*</small></label>
    {{ Form::text('username','',array('class'=>'form-control','id'=>'username','placeholder'=>'Select Username','required'=>'required'))}}
</div>

<div class="form-group">
    <label for="password">Password<small>*</small></label>
    {{ Form::password('password',array('class'=>'form-control','id'=>'password','placeholder'=>'Enter Password','autocomplete'=>'off','required'=>'required')) }}
</div>
<div class="form-group">
    <label for="password_confirmation">Retype Password<small>*</small></label>
    {{ Form::password('password_confirmation',array('class'=>'form-control','id'=>'password_confirmation','placeholder'=>'Confirm Password','autocomplete'=>'off','required'=>'required')) }}
</div>
<p><small>By clicking on the "create account" you accept the following <a href="{{ url('tos') }}">terms and conditions</a> and our <a href="{{ url('privacy') }}">privacy policy</a></small></p>
{{ Form::submit('Create New Account',array('class'=>'btn btn-success'))}}
{{ Form::close() }}
@stop