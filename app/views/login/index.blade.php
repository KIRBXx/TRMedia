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

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>