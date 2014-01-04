@extends('master/index')

@section('content')
<h3 class="content-heading">{{ t('Login') }}</h3>

{{ Form::open() }}
<div class="form-group">
    <label for="username">{{ t('Username or Email address') }}</label>
    {{ Form::text('username','',array('class'=>'form-control','id'=>'username','placeholder'=>t('Username or Email address')))}}
</div>
<div class="form-group">
    <label for="password">{{ t('Password') }}</label>
    {{ Form::password('password',array('class'=>'form-control','id'=>'password','placeholder'=>t('Password'),'autocomplete'=>'off')) }}
</div>
<div class="checkbox">
    <label>
       {{ t('Remember Me') }} {{ Form::checkbox('remember-me', 'value') }}
    </label>
    &nbsp;&middot;&nbsp; <a href="{{ url('password/remind') }}">Forgot your password? </a>
</div>
{{ Form::submit(t('Login'),array('class'=>'btn btn-success')) }} or <a href="{{ Facebook::loginUrl() }}" class="btn btn-facebook">Login with facebook</a>
{{ Form::close() }}

@stop

