@extends('admin/master')

@section('container')
<h2>Editing "{{ $user->username }}" <small>{{ $user->fullname }}</small></h2>
<hr>
{{ Form::open() }}

    {{ Form::hidden('userid',$user->id) }}


<div class="form-group">
    <label for="username">Username</label>
    {{ Form::text('username',$user->username,array('class'=>'form-control','disabled'=>'disabled')) }}
</div>

<div class="form-group">
    <label for="fullname">Full Name</label>
    {{ Form::text('fullname',$user->fullname,array('class'=>'form-control')) }}
</div>

<div class="form-group">
    <label for="email">Email</label>
    {{ Form::text('email',$user->email,array('class'=>'form-control')) }}
</div>

<div class="form-group">
    <label for="email">About Me</label>
    {{ Form::textarea('aboutme',$user->about_me,array('class'=>'form-control')) }}
</div>


<div class="form-group">
    <label for="blogurl">Blog Url</label>
    {{ Form::text('blogurl',$user->blogurl,array('class'=>'form-control')) }}
</div>

<div class="form-group">
    <label for="country">Location</label>
    {{ Form::text('country',$user->country,array('class'=>'form-control')) }}
</div>

<div class="form-group">
    <label for="featured">Featured This User</label>
    @if($user->is_featured == true)
    {{ Form::checkbox('featured','TRUE', true) }}
    @else
    {{ Form::checkbox('featured','TRUE', false) }}
    @endif
</div>

<div class="form-group">
    <label for="featured">Ban This User</label>
    @if($user->permission == 'ban')
    {{ Form::checkbox('ban','TRUE', true) }}
    @else
    {{ Form::checkbox('ban','TRUE', false) }}
    @endif
</div>

@if($user->confirmed != '1')
<div class="form-group">
    <label for="featured">Confirm this user <small>( Validating email )</small> </label>
    {{ Form::checkbox('confirmed','1', false) }}
</div>
@endif

<div class="form-group">
 {{ Form::submit('Edit User',array('class'=>'btn btn-default')) }}
</div>


{{ Form::close() }}
@stop